<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\SellerSubscription;
use App\Models\User;
use App\Services\MercadoPagoSubscriptions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SellerSubscriptionController extends Controller
{
    public function index(Request $request, MercadoPagoSubscriptions $payments)
    {
        $subscriptions = SellerSubscription::where('user_id', $request->user()->id)->paid();
        $subscription = (clone $subscriptions)->entitled()->latest()->first()
            ?? (clone $subscriptions)->where('status', '!=', 'cancelled')->latest()->first()
            ?? $subscriptions->latest()->first();
        $tienda = $request->user()->tiendas()->orderBy('id_tienda')->first();
        $products = $tienda?->productos()->with('imagenPrincipal')->orderByDesc('id_producto')->paginate(12)->withQueryString();
        $selected = $subscription?->products()->pluck('productos.id_producto')->all() ?? [];
        $metrics = $subscription && $subscription->entitled()
            ? DB::table('promotion_metrics')->where('subscription_id', $subscription->id)->where('day', '>=', now()->subDays(29)->toDateString())
                ->selectRaw('kind, SUM(impressions) as impressions, SUM(visits) as visits')->groupBy('kind')->get()->keyBy('kind')
            : collect();

        return view('vendedor.suscripcion', ['plans' => config('subscriptions.plans'), 'available' => $payments->available(), 'subscription' => $subscription, 'tienda' => $tienda, 'products' => $products, 'selected' => $selected, 'metrics' => $metrics]);
    }

    public function checkout(Request $request, MercadoPagoSubscriptions $payments)
    {
        $data = $request->validate(['plan' => ['required', Rule::in(SellerSubscription::PAID_PLANS)], 'acepto_renovacion' => ['accepted']]);
        if (! $request->user()->tiendas()->exists()) {
            return redirect()->route('vendedor.tienda.create')->withErrors(['suscripcion' => 'Primero crea tu tienda para contratar una promoción.']);
        }
        if (! $payments->available()) {
            return back()->withErrors(['suscripcion' => 'La contratación de planes estará disponible próximamente.']);
        }
        $subscription = DB::transaction(function () use ($request, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $open = SellerSubscription::where('user_id', $request->user()->id)->paid()->where(fn ($q) => $q->where('status', '!=', 'cancelled')->orWhere('paid_until', '>', now()))->latest()->first();
            if ($open && ($open->status !== 'pending' || $open->plan !== $data['plan'])) {
                throw ValidationException::withMessages(['suscripcion' => 'Ya tienes una suscripción. Cancela su renovación y espera a que termine el periodo pagado antes de contratar otro plan. Si está pendiente, cancélala para elegir otro.']);
            }

            return $open ?? SellerSubscription::create(['user_id' => $request->user()->id, 'plan' => $data['plan'], 'amount' => config('subscriptions.plans.'.$data['plan'].'.price'), 'currency' => 'MXN']);
        });
        try {
            $url = DB::transaction(function () use ($subscription, $payments) {
                return $payments->checkout(SellerSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail());
            });
        } catch (ConnectionException|RequestException $e) {
            return back()->withErrors(['suscripcion' => 'No pudimos conectar con Mercado Pago. Puedes reintentar con el mismo plan; conservamos tu solicitud.']);
        }

        return redirect()->away($url);
    }

    public function returned(Request $request, SellerSubscription $subscription, MercadoPagoSubscriptions $payments)
    {
        return $this->sync($request, $subscription, $payments);
    }

    public function sync(Request $request, SellerSubscription $subscription, MercadoPagoSubscriptions $payments)
    {
        abort_unless($subscription->user_id === $request->user()->id, 404);
        try {
            DB::transaction(fn () => $payments->sync(SellerSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail()));
        } catch (ConnectionException|RequestException $e) {
            return redirect()->route('vendedor.suscripcion.index')->withErrors(['suscripcion' => 'No pudimos consultar el pago. Actualiza el estado en unos momentos.']);
        }

        return redirect()->route('vendedor.suscripcion.index')->with('success', $subscription->fresh()->entitled() ? 'Tu plan está activo. Ya puedes elegir los productos que quieres promocionar.' : 'Estado actualizado. Los beneficios se activan al confirmar el pago; autorizar la suscripción puede tomar unos minutos.');
    }

    public function cancel(Request $request, SellerSubscription $subscription, MercadoPagoSubscriptions $payments)
    {
        abort_unless($subscription->user_id === $request->user()->id, 404);
        $request->validate(['confirmar' => ['accepted']]);
        try {
            DB::transaction(function () use ($subscription, $payments) {
                User::whereKey($subscription->user_id)->lockForUpdate()->firstOrFail();
                $payments->cancel(SellerSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail());
            });
        } catch (ConnectionException|RequestException $e) {
            return back()->withErrors(['suscripcion' => 'Mercado Pago no confirmó la cancelación. Tu renovación sigue vigente; vuelve a intentarlo.']);
        }

        return redirect()->route('vendedor.suscripcion.index')->with('success', 'Renovación cancelada. Conservas los beneficios hasta que termine tu periodo pagado.');
    }

    public function promote(Request $request, Producto $producto)
    {
        abort_unless($producto->tienda?->id_vendedor === $request->user()->id, 404);
        $request->validate(['promoted' => ['required', 'boolean']]);
        DB::transaction(function () use ($request, $producto) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $subscription = SellerSubscription::where('user_id', $request->user()->id)->entitled()->latest()->lockForUpdate()->first();
            if (! $subscription) {
                throw ValidationException::withMessages(['promocion' => 'Necesitas un plan activo para promocionar productos.']);
            }
            if (! $request->boolean('promoted')) {
                $subscription->products()->detach($producto->getKey());

                return;
            }
            if ($subscription->products()->whereKey($producto->getKey())->exists()) {
                return;
            }
            if ($subscription->products()->count() >= $subscription->planDetails()['product_limit']) {
                throw ValidationException::withMessages(['promocion' => 'Llegaste al límite de productos de tu plan. Quita una promoción para agregar otra.']);
            }
            $subscription->products()->attach($producto->getKey());
        });

        return back()->with('success', $request->boolean('promoted') ? 'Producto promocionado. Tendrá prioridad en las búsquedas que coincidan con él.' : 'Se quitó la promoción del producto.');
    }

    public function webhook(Request $request, MercadoPagoSubscriptions $payments)
    {
        $id = $payments->webhookId($request);
        abort_unless($id, 401);
        $subscription = $payments->subscriptionForEvent((string) $request->input('type'), $id);
        if ($subscription) {
            DB::transaction(fn () => $payments->sync(SellerSubscription::whereKey($subscription->id)->lockForUpdate()->firstOrFail()));
        }

        return response()->json(['received' => true]);
    }
    public function activarPlanGratuito(Request $request)
    {
        if (! $request->user()->tiendas()->exists()) {
            return redirect()->route('vendedor.tienda.create')->withErrors(['suscripcion' => 'Primero crea tu tienda para activar un plan.']);
        }

        DB::transaction(function () use ($request) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $open = SellerSubscription::where('user_id', $request->user()->id)->paid()
                ->where(fn ($q) => $q->where('status', '!=', 'cancelled')->orWhere('paid_until', '>', now()))
                ->exists();
            if ($open) {
                throw ValidationException::withMessages(['suscripcion' => 'Ya tienes una suscripción de pago. Cancela su renovación y espera a que termine el periodo para cambiar al plan gratuito.']);
            }
            // Básico is the default account level, not a paid or renewable subscription.
        });

        return redirect()->route('vendedor.suscripcion.index')->with('success', 'Tu plan Básico está disponible sin pagos ni renovación. Puedes elegir Impulso o Pro cuando lo necesites.');
    }
}
