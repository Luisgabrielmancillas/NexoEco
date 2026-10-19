<?php

namespace App\Services;

use App\Models\Apartado;
use App\Models\MercadoPagoAccount;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MercadoPagoReservations
{
    public function __construct(private MercadoPagoConnection $connection) {}

    private function api(MercadoPagoAccount $account): PendingRequest
    {
        return Http::baseUrl('https://api.mercadopago.com')->withToken($this->connection->token($account))->acceptJson()->asJson()->timeout(15);
    }

    private function account(Apartado $apartado): MercadoPagoAccount
    {
        abort_unless($apartado->provider === 'mercadopago', 409);
        $account = MercadoPagoAccount::where('user_id', $apartado->seller_id)->where('mp_user_id', $apartado->merchant_id)->firstOrFail();
        abort_unless($this->connection->available($account) && $account->live_mode === $apartado->live_mode, 503, 'El vendedor debe volver a conectar su cuenta de Mercado Pago.');

        return $account;
    }

    public function prepare(Apartado $apartado): void
    {
        // Persist rotated credentials before a payment transaction can roll back.
        $this->connection->token($this->account($apartado));
    }

    public function create(Apartado $apartado): void
    {
        $account = $this->account($apartado);
        $preference = $this->api($account)->withHeaders(['X-Idempotency-Key' => $apartado->id])->post('/checkout/preferences', [
            'items' => [[
                'id' => $apartado->id, 'title' => 'Apartado: '.mb_substr($apartado->producto_nombre, 0, 100),
                'quantity' => 1, 'currency_id' => $apartado->moneda, 'unit_price' => (float) $apartado->monto,
            ]],
            'external_reference' => $apartado->id,
            'back_urls' => ['success' => route('apartados.return', $apartado), 'failure' => route('apartados.return', $apartado), 'pending' => route('apartados.return', $apartado)],
            'auto_return' => 'approved', 'notification_url' => route('mercadopago.webhook', ['account' => $account->id]), 'marketplace_fee' => 0,
        ])->throw()->json();
        $url = $apartado->live_mode ? ($preference['init_point'] ?? null) : ($preference['sandbox_init_point'] ?? null);
        if (empty($preference['id']) || (string) ($preference['collector_id'] ?? '') !== $apartado->merchant_id || ! $this->safeCheckoutUrl($url)) {
            throw ValidationException::withMessages(['mercadopago' => 'Mercado Pago no devolvió un checkout válido para la cuenta del vendedor.']);
        }
        $apartado->update(['mp_preference_id' => $preference['id'], 'approval_url' => $url]);
    }

    public function safeCheckoutUrl(?string $url): bool
    {
        return $url && parse_url($url, PHP_URL_SCHEME) === 'https'
            && in_array(parse_url($url, PHP_URL_HOST), ['www.mercadopago.com.mx', 'sandbox.mercadopago.com.mx', 'sandbox.mercadopago.com']);
    }

    public function reconcile(Apartado $apartado, ?string $paymentId = null): void
    {
        $api = $this->api($this->account($apartado));
        $paymentId ??= $apartado->mp_payment_id;
        if ($paymentId) {
            abort_unless(ctype_digit($paymentId), 422);
            $this->applyPayment($apartado, $api->get('/v1/payments/'.$paymentId)->throw()->json());

            return;
        }
        $payments = $api->get('/v1/payments/search', ['external_reference' => $apartado->id, 'sort' => 'date_created', 'criteria' => 'desc', 'limit' => 30])->throw()->json('results') ?? [];
        $payment = collect($payments)->first(fn ($p) => in_array($p['status'] ?? '', ['approved', 'refunded', 'charged_back']))
            ?? collect($payments)->first(fn ($p) => in_array($p['status'] ?? '', ['pending', 'in_process', 'in_mediation', 'authorized'])) ?? ($payments[0] ?? null);
        if ($payment) {
            $this->applyPayment($apartado, $payment);
        }
    }

    public function applyPayment(Apartado $apartado, array $payment): void
    {
        if ((string) ($payment['external_reference'] ?? '') !== $apartado->id
            || (string) ($payment['collector_id'] ?? '') !== $apartado->merchant_id
            || ($payment['currency_id'] ?? '') !== $apartado->moneda
            || $this->money($payment['transaction_amount'] ?? null) !== $apartado->monto
            || ! isset($payment['live_mode']) || (bool) $payment['live_mode'] !== $apartado->live_mode
            || ! ctype_digit((string) ($payment['id'] ?? ''))) {
            throw ValidationException::withMessages(['mercadopago' => 'El pago no coincide con el monto, moneda o vendedor de este apartado.']);
        }
        $estado = match ($payment['status'] ?? '') {
            'approved' => (float) ($payment['transaction_amount_refunded'] ?? 0) > 0 ? 'reembolso_parcial' : 'pagado',
            'pending', 'in_process', 'in_mediation', 'authorized' => 'procesando',
            'refunded' => 'reembolsado', 'charged_back' => 'revertido', 'rejected', 'cancelled' => 'rechazado', default => null,
        };
        if (! $estado) {
            return;
        }
        $financial = ['pagado', 'reembolso_parcial', 'reembolsado', 'revertido'];
        if ($apartado->mp_payment_id && $apartado->mp_payment_id !== (string) $payment['id']) {
            if (in_array($estado, $financial)) {
                Log::warning('Additional payment for a reservation requires seller review', ['apartado' => $apartado->id, 'payment_id' => $payment['id']]);
            }

            return;
        }
        if (in_array($apartado->estado, ['reembolsado', 'revertido', 'reembolso_parcial']) && in_array($estado, ['pagado', 'procesando', 'rechazado'])) {
            return;
        }
        if ($apartado->estado === 'pagado' && in_array($estado, ['procesando', 'rechazado'])) {
            return;
        }
        $apartado->update([
            'estado' => $estado, 'mp_payment_id' => in_array($estado, $financial) ? (string) $payment['id'] : $apartado->mp_payment_id,
            'pagado_at' => in_array($estado, $financial) ? ($apartado->pagado_at ?? now()) : $apartado->pagado_at,
        ]);
    }

    private function money(mixed $value): ?string
    {
        $value = (string) $value;
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            return null;
        }
        $parts = explode('.', $value);

        return ((string) (int) $parts[0]).'.'.str_pad($parts[1] ?? '', 2, '0');
    }

    public function verifiedPaymentId(Request $request): ?string
    {
        $secret = config('mercadopago.webhook_secret');
        $id = $request->query('data_id') ?? $request->query('data.id') ?? data_get($request->query('data'), 'id');
        foreach (explode('&', $request->server('QUERY_STRING', '')) as $part) {
            $pair = explode('=', $part, 2);
            if (urldecode($pair[0]) === 'data.id') {
                $id = urldecode($pair[1] ?? '');
            }
        }
        $requestId = $request->header('x-request-id');
        if (! $secret || ! is_string($id) || ! ctype_digit($id) || ! $requestId) {
            return null;
        }
        $parts = [];
        foreach (explode(',', (string) $request->header('x-signature')) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }
        if (empty($parts['ts']) || ! ctype_digit($parts['ts']) || empty($parts['v1'])) {
            return null;
        }
        $expected = hash_hmac('sha256', 'id:'.$id.';request-id:'.$requestId.';ts:'.$parts['ts'].';', $secret);
        if (! hash_equals($expected, $parts['v1'])) {
            return null;
        }
        if ($request->input('data.id') !== null && (string) $request->input('data.id') !== $id) {
            return null;
        }

        return $id;
    }

    public function webhookPayment(MercadoPagoAccount $account, string $paymentId): array
    {
        return $this->api($account)->get('/v1/payments/'.$paymentId)->throw()->json();
    }
}
