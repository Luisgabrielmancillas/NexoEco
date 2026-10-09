<?php

namespace App\Http\Controllers;

use App\Models\Apartado;
use App\Models\MercadoPagoAccount;
use App\Models\Producto;
use App\Services\MercadoPagoConnection;
use App\Services\MercadoPagoReservations;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApartadoController extends Controller
{
    public function index(Request $request)
    {
        $sellerArea = $request->routeIs('vendedor.*');
        $apartados = Apartado::where($sellerArea ? 'seller_id' : 'buyer_id', $request->user()->id)->with(['buyer', 'seller', 'producto'])->latest()->paginate(20);
        $account = $sellerArea ? $request->user()->mercadoPagoAccount : null;

        return view('apartados.index', compact('apartados', 'sellerArea', 'account'));
    }

    public function store(Request $request, Producto $producto, MercadoPagoReservations $payments, MercadoPagoConnection $connection)
    {
        $request->validate(['acepto_condiciones' => ['accepted']]);
        $apartado = DB::transaction(function () use ($request, $producto, $connection) {
            $producto = Producto::whereKey($producto->getKey())->lockForUpdate()->firstOrFail();
            $seller = $producto->tienda->user;
            abort_unless($producto->apartados_activos && $producto->apartado_monto > 0 && filled($producto->apartado_condiciones), 404);
            abort_unless($seller && $seller->activo && $seller->tieneTipo('vendedor'), 404);
            abort_if($seller->id === $request->user()->id, 403);
            $account = $seller->mercadoPagoAccount;
            abort_unless($connection->available($account), 503, 'El vendedor debe conectar Mercado Pago. Puedes hablar con él por chat.');
            $existing = Apartado::where('id_producto', $producto->getKey())->where('buyer_id', $request->user()->id)->where('provider', 'mercadopago')->whereNotIn('estado', ['reembolsado', 'revertido'])->latest()->first();
            if ($existing) {
                return $existing;
            }

            return Apartado::create([
                'id_producto' => $producto->getKey(), 'buyer_id' => $request->user()->id, 'seller_id' => $seller->id,
                'producto_nombre' => $producto->nombre_producto, 'monto' => $producto->apartado_monto, 'moneda' => config('mercadopago.currency'),
                'merchant_id' => $account->mp_user_id, 'live_mode' => $account->live_mode, 'condiciones' => $producto->apartado_condiciones,
            ]);
        });
        if ($apartado->monto !== $producto->apartado_monto || $apartado->condiciones !== $producto->apartado_condiciones) {
            return redirect()->route('apartados.index')->with('success', 'Ya tienes un apartado para este producto. Revisa sus condiciones en tu historial.');
        }

        return $this->checkout($apartado, $payments);
    }

    public function resume(Request $request, Apartado $apartado, MercadoPagoReservations $payments)
    {
        abort_unless($apartado->buyer_id === $request->user()->id, 404);

        return $this->checkout($apartado, $payments);
    }

    private function checkout(Apartado $apartado, MercadoPagoReservations $payments)
    {
        try {
            $payments->prepare($apartado);
            DB::transaction(function () use ($apartado, $payments) {
                $locked = Apartado::whereKey($apartado->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->provider === 'mercadopago', 409);
                if (! $locked->mp_preference_id) {
                    $payments->create($locked);
                } else {
                    $payments->reconcile($locked);
                }
            });
        } catch (RequestException|ConnectionException $e) {
            Log::warning('Mercado Pago checkout unavailable', ['apartado' => $apartado->id]);

            return back()->withErrors(['mercadopago' => 'No pudimos conectar con Mercado Pago. Intenta nuevamente o contacta al vendedor por chat.']);
        }
        $apartado->refresh();
        if (! in_array($apartado->estado, ['pendiente', 'rechazado'])) {
            return redirect()->route('apartados.index')->with('success', 'Este apartado ya está registrado o en proceso. Consulta su estado antes de volver a pagar.');
        }
        abort_unless($payments->safeCheckoutUrl($apartado->approval_url), 422);

        return redirect()->away($apartado->approval_url);
    }

    public function returned(Request $request, Apartado $apartado, MercadoPagoReservations $payments)
    {
        abort_unless($apartado->buyer_id === $request->user()->id, 404);
        $request->validate(['payment_id' => ['nullable', 'regex:/^\d+$/'], 'collection_id' => ['nullable', 'regex:/^\d+$/']]);
        try {
            $payments->prepare($apartado);
            DB::transaction(function () use ($apartado, $payments, $request) {
                $payments->reconcile(Apartado::whereKey($apartado->id)->lockForUpdate()->firstOrFail(), $request->query('payment_id') ?: $request->query('collection_id'));
            });
        } catch (RequestException|ConnectionException $e) {
            return redirect()->route('apartados.index')->withErrors(['mercadopago' => 'Estamos esperando la confirmación de Mercado Pago. Actualiza el estado de tu apartado en unos momentos.']);
        }

        return redirect()->route('apartados.index')->with('success', $apartado->fresh()->estado === 'pagado' ? 'Tu apartado está pagado. Habla con el vendedor para acordar la entrega.' : 'Regresaste de Mercado Pago. Revisa el estado de tu apartado antes de continuar.');
    }

    public function sync(Request $request, Apartado $apartado, MercadoPagoReservations $payments)
    {
        abort_unless(Apartado::forUser($request->user())->whereKey($apartado->id)->exists(), 404);
        try {
            $payments->prepare($apartado);
            DB::transaction(fn () => $payments->reconcile(Apartado::whereKey($apartado->id)->lockForUpdate()->firstOrFail()));
        } catch (RequestException|ConnectionException $e) {
            return back()->withErrors(['mercadopago' => 'No pudimos actualizar el pago. Intenta más tarde.']);
        }

        return redirect()->route($apartado->buyer_id === $request->user()->id ? 'apartados.index' : 'vendedor.apartados.index')->with('success', 'Estado actualizado desde Mercado Pago.');
    }

    public function webhook(Request $request, MercadoPagoReservations $payments)
    {
        $paymentId = $payments->verifiedPaymentId($request);
        abort_unless($paymentId, 401);
        if ($request->input('type') !== 'payment') {
            return response()->json(['received' => true]);
        }
        $account = $request->query('account') ? MercadoPagoAccount::find($request->query('account')) : MercadoPagoAccount::where('mp_user_id', (string) $request->input('user_id'))->first();
        if (! $account) {
            return response()->json(['received' => true]);
        }
        $payment = $payments->webhookPayment($account, $paymentId);
        abort_unless((string) ($payment['id'] ?? '') === $paymentId, 422);
        $apartado = Apartado::whereKey($payment['external_reference'] ?? '')->where('provider', 'mercadopago')->where('seller_id', $account->user_id)->first();
        if ($apartado) {
            DB::transaction(fn () => $payments->applyPayment(Apartado::whereKey($apartado->id)->lockForUpdate()->firstOrFail(), $payment));
        }

        return response()->json(['received' => true]);
    }
}
