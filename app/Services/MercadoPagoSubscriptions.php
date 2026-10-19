<?php

namespace App\Services;

use App\Models\SellerSubscription;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MercadoPagoSubscriptions
{
    public function available(): bool
    {
        return filled(config('subscriptions.access_token')) && filled(config('subscriptions.collector_id')) && filled(config('subscriptions.webhook_secret'));
    }

    private function api(): PendingRequest
    {
        abort_unless($this->available(), 503, 'Las suscripciones todavía no están disponibles para contratación.');

        return Http::baseUrl('https://api.mercadopago.com')->withToken(config('subscriptions.access_token'))->acceptJson()->asJson()->timeout(15);
    }

    public function checkout(SellerSubscription $subscription): string
    {
        if (! $subscription->provider_id) {
            // Recover an accepted request whose response was lost before creating another mandate.
            $previous = $this->api()->get('/preapproval/search', ['external_reference' => $subscription->id])->throw()->json('results', []);
            $remote = collect($previous)->first(fn ($item) => ($item['external_reference'] ?? '') === $subscription->id);
            $remote ??= $this->api()->withHeaders(['X-Idempotency-Key' => $subscription->id])->post('/preapproval', [
                'reason' => 'NexoEco '.$subscription->planDetails()['name'].' mensual',
                'external_reference' => $subscription->id,
                'payer_email' => $subscription->user->email,
                'auto_recurring' => ['frequency' => 1, 'frequency_type' => 'months', 'transaction_amount' => (float) $subscription->amount, 'currency_id' => $subscription->currency],
                'back_url' => route('vendedor.suscripcion.return', $subscription),
                'status' => 'pending',
            ])->throw()->json();
            $this->validateMandate($subscription, $remote);
            $url = $remote['init_point'] ?? null;
            if (! $this->safeUrl($url)) {
                throw ValidationException::withMessages(['suscripcion' => 'Mercado Pago no devolvió un enlace de suscripción válido.']);
            }
            $subscription->update(['provider_id' => $remote['id'], 'checkout_url' => $url]);
        }
        $this->sync($subscription);
        if ($subscription->status !== 'pending' || ! $this->safeUrl($subscription->checkout_url)) {
            throw ValidationException::withMessages(['suscripcion' => 'Esta suscripción ya fue procesada. Actualiza su estado antes de continuar.']);
        }

        return $subscription->checkout_url;
    }

    public function sync(SellerSubscription $subscription): void
    {
        if (! $subscription->provider_id) {
            return;
        }
        $remote = $this->api()->get('/preapproval/'.$subscription->provider_id)->throw()->json();
        $this->validateMandate($subscription, $remote);
        $status = $remote['status'] ?? 'pending';
        if (! in_array($status, ['pending', 'authorized', 'paused', 'cancelled'])) {
            throw ValidationException::withMessages(['suscripcion' => 'Estado de suscripción no reconocido.']);
        }
        // An authorization is not a payment. Verify the actual monthly invoice and payment.
        $invoices = [];
        $offset = 0;
        do {
            $page = $this->api()->get('/authorized_payments/search', ['preapproval_id' => $subscription->provider_id, 'limit' => 100, 'offset' => $offset])->throw()->json();
            $batch = $page['results'] ?? [];
            $invoices = array_merge($invoices, $batch);
            $offset += count($batch);
        } while (count($batch) > 0 && $offset < (int) data_get($page, 'paging.total', 0));
        $paidUntil = null;
        foreach ($invoices as $invoice) {
            if (($invoice['preapproval_id'] ?? '') !== $subscription->provider_id || ! ctype_digit((string) data_get($invoice, 'payment.id', ''))) {
                continue;
            }
            $debit = Carbon::parse($invoice['debit_date'] ?? $invoice['date_created']);
            // Older invoices cannot cover the current period; do not refetch the whole history.
            if ($debit->copy()->addMonthNoOverflow()->isPast()) {
                continue;
            }
            $payment = $this->api()->get('/v1/payments/'.data_get($invoice, 'payment.id'))->throw()->json();
            if ((string) ($payment['collector_id'] ?? '') !== (string) config('subscriptions.collector_id')
                || ($payment['currency_id'] ?? '') !== $subscription->currency
                || number_format((float) ($payment['transaction_amount'] ?? 0), 2, '.', '') !== $subscription->amount
                || (string) ($payment['id'] ?? '') !== (string) data_get($invoice, 'payment.id')) {
                throw ValidationException::withMessages(['suscripcion' => 'El pago no coincide con el importe, moneda o cuenta de NexoEco.']);
            }
            if (($payment['status'] ?? '') !== 'approved' || (float) ($payment['transaction_amount_refunded'] ?? 0) > 0) {
                continue;
            }
            $until = $debit->addMonthNoOverflow();
            if (! $paidUntil || $until->greaterThan($paidUntil)) {
                $paidUntil = $until;
            }
        }
        $subscription->update([
            'status' => $subscription->cancelled_at ? 'cancelled' : $status,
            'paid_until' => $paidUntil,
            'cancelled_at' => $status === 'cancelled' ? ($subscription->cancelled_at ?? now()) : $subscription->cancelled_at,
        ]);
    }

    public function cancel(SellerSubscription $subscription): void
    {
        if ($subscription->status === 'cancelled') {
            return;
        }
        if (! $subscription->provider_id) {
            // A timed-out checkout may have created a mandate remotely. Cancel that too.
            $previous = $this->api()->get('/preapproval/search', ['external_reference' => $subscription->id])->throw()->json('results', []);
            $remote = collect($previous)->first(fn ($item) => ($item['external_reference'] ?? '') === $subscription->id);
            if ($remote) {
                $this->validateMandate($subscription, $remote);
                $subscription->update(['provider_id' => $remote['id']]);
            }
        }
        if ($subscription->provider_id) {
            // Capture any paid invoice that arrived just before the cancellation request.
            $this->sync($subscription);
            $remote = $this->api()->put('/preapproval/'.$subscription->provider_id, ['status' => 'cancelled'])->throw()->json();
            $this->validateMandate($subscription, $remote);
            if (($remote['status'] ?? '') !== 'cancelled') {
                throw ValidationException::withMessages(['suscripcion' => 'Mercado Pago todavía no confirmó la cancelación. Intenta nuevamente.']);
            }
        }
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
    }

    private function validateMandate(SellerSubscription $subscription, array $remote): void
    {
        if (empty($remote['id']) || ! preg_match('/^[a-zA-Z0-9_-]+$/', (string) $remote['id'])
            || ($subscription->provider_id && $subscription->provider_id !== (string) $remote['id'])
            || ($remote['external_reference'] ?? '') !== $subscription->id
            || (string) ($remote['collector_id'] ?? '') !== (string) config('subscriptions.collector_id')
            || data_get($remote, 'auto_recurring.currency_id') !== $subscription->currency
            || (int) data_get($remote, 'auto_recurring.frequency') !== 1
            || data_get($remote, 'auto_recurring.frequency_type') !== 'months'
            || number_format((float) data_get($remote, 'auto_recurring.transaction_amount', 0), 2, '.', '') !== $subscription->amount) {
            throw ValidationException::withMessages(['suscripcion' => 'La suscripción de Mercado Pago no coincide con el plan contratado.']);
        }
    }

    private function safeUrl(?string $url): bool
    {
        return $url && parse_url($url, PHP_URL_SCHEME) === 'https' && in_array(parse_url($url, PHP_URL_HOST), ['www.mercadopago.com.mx', 'sandbox.mercadopago.com.mx', 'sandbox.mercadopago.com']) && ! parse_url($url, PHP_URL_USER);
    }

    public function webhookId(Request $request): ?string
    {
        $id = $request->query('data_id') ?? data_get($request->query('data'), 'id');
        foreach (explode('&', $request->server('QUERY_STRING', '')) as $part) {
            $pair = explode('=', $part, 2);
            if (urldecode($pair[0]) === 'data.id') {
                $id = urldecode($pair[1] ?? '');
            }
        }
        $requestId = $request->header('x-request-id');
        $secret = config('subscriptions.webhook_secret');
        if (! $secret || ! $requestId || ! is_string($id) || ! preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            return null;
        }
        $parts = [];
        foreach (explode(',', (string) $request->header('x-signature')) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }
        if (! ctype_digit($parts['ts'] ?? '') || empty($parts['v1'])) {
            return null;
        }
        $expected = hash_hmac('sha256', 'id:'.strtolower($id).';request-id:'.$requestId.';ts:'.$parts['ts'].';', $secret);
        if (! hash_equals($expected, $parts['v1']) || (string) $request->input('data.id') !== $id) {
            return null;
        }

        return $id;
    }

    public function subscriptionForEvent(string $type, string $id): ?SellerSubscription
    {
        if ($type === 'subscription_preapproval') {
            return SellerSubscription::where('provider_id', $id)->first();
        }
        if ($type === 'subscription_authorized_payment') {
            $invoice = $this->api()->get('/authorized_payments/'.$id)->throw()->json();

            return SellerSubscription::where('provider_id', $invoice['preapproval_id'] ?? '')->first();
        }
        if ($type === 'payment') {
            $payment = $this->api()->get('/v1/payments/'.$id)->throw()->json();

            return SellerSubscription::whereKey($payment['external_reference'] ?? '')->first();
        }

        return null;
    }
}
