<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\SellerSubscription;
use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerPromotion
{
    private array $subscriptions = [];

    public function productPriority()
    {
        return SellerSubscription::query()->entitled()->selectRaw($this->prioritySql())
            ->join('product_promotions', 'product_promotions.subscription_id', '=', 'seller_subscriptions.id')
            ->join('tiendas as promoted_store', 'promoted_store.id_vendedor', '=', 'seller_subscriptions.user_id')
            ->whereColumn('product_promotions.id_producto', 'productos.id_producto')
            ->whereColumn('promoted_store.id_tienda', 'productos.id_tienda')
            ->whereHas('user', fn ($q) => $q->where('activo', true)->whereHas('tipos_usuario', fn ($q) => $q->whereRaw('LOWER(nombre_tipo) = ?', ['vendedor'])));
    }

    public function storePriority()
    {
        return SellerSubscription::query()->entitled()->selectRaw($this->prioritySql())
            ->whereColumn('seller_subscriptions.user_id', 'tiendas.id_vendedor')
            ->whereHas('user', fn ($q) => $q->where('activo', true)->whereHas('tipos_usuario', fn ($q) => $q->whereRaw('LOWER(nombre_tipo) = ?', ['vendedor'])));
    }

    private function prioritySql(): string
    {
        // Plan keys are fixed application identifiers; ranks are configurable integers.
        return 'COALESCE(MAX(CASE plan WHEN \'pro\' THEN '.(int) config('subscriptions.plans.pro.priority').' WHEN \'impulso\' THEN '.(int) config('subscriptions.plans.impulso.priority').' ELSE 0 END), 0)';
    }

    public function record(Request $request, string $kind, Producto|Tienda $target, string $event): void
    {
        if (! in_array($event, ['impressions', 'visits']) || ! in_array($kind, ['product', 'store'])) {
            return;
        }
        $owner = $target instanceof Producto ? $target->tienda?->id_vendedor : $target->id_vendedor;
        if (! $owner || $owner === $request->user()?->id) {
            return;
        }
        // Reuse the subscription and selected products for cards belonging to the same seller.
        if (! array_key_exists($owner, $this->subscriptions)) {
            $this->subscriptions[$owner] = SellerSubscription::query()->entitled()->where('user_id', $owner)
                ->whereHas('user', fn ($q) => $q->where('activo', true)->whereHas('tipos_usuario', fn ($q) => $q->whereRaw('LOWER(nombre_tipo) = ?', ['vendedor'])))
                ->with('products:productos.id_producto')->latest()->first();
        }
        $subscription = $this->subscriptions[$owner];
        if (! $subscription || ($kind === 'product' && ! $subscription->products->contains('id_producto', $target->getKey()))) {
            return;
        }
        // One count per session, target and hour reduces refresh inflation. No IPs or personal data stored.
        $key = 'promotion.'.$subscription->id.'.'.$kind.'.'.$target->getKey().'.'.$event;
        if ($request->session()->get($key) === now()->format('Y-m-d-H')) {
            return;
        }
        $row = ['subscription_id' => $subscription->id, 'kind' => $kind, 'target_id' => $target->getKey(), 'day' => now()->toDateString()];
        DB::table('promotion_metrics')->insertOrIgnore($row);
        DB::table('promotion_metrics')->where($row)->increment($event);
        $request->session()->put($key, now()->format('Y-m-d-H'));
    }
}
