<?php

namespace App\Services;

use App\Models\Apartado;
use App\Models\ProductChat;
use App\Models\Producto;
use App\Models\Tienda;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerAnalytics
{
    public function visit(Request $request, Producto|Tienda $target): void
    {
        $kind = $target instanceof Producto ? 'product' : 'store';
        $owner = $target instanceof Producto ? $target->tienda?->user : $target->user;
        if (! $owner?->activo || $owner->id === $request->user()?->id || ! $owner->tieneTipo('vendedor')) {
            return;
        }
        $key = 'interest.'.$kind.'.'.$target->getKey();
        $hour = now()->format('Y-m-d-H');
        if ($request->session()->get($key) === $hour) {
            return;
        }
        $row = ['seller_id' => $owner->id, 'kind' => $kind, 'target_id' => $target->getKey(), 'day' => now('America/Mexico_City')->toDateString()];
        DB::table('seller_daily_metrics')->insertOrIgnore($row);
        DB::table('seller_daily_metrics')->where($row)->increment('visits');
        $request->session()->put($key, $hour);
    }

    public function summary(User $seller, Carbon $from, Carbon $to): array
    {
        $visits = DB::table('seller_daily_metrics')->where('seller_id', $seller->id)->whereBetween('day', [$from->toDateString(), $to->toDateString()])->selectRaw('kind, SUM(visits) as visits')->groupBy('kind')->pluck('visits', 'kind');
        $range = [$from->copy()->startOfDay()->utc(), $to->copy()->endOfDay()->utc()];
        $chats = ProductChat::where('seller_id', $seller->id)->whereBetween('created_at', $range)->count();
        $productVisits = (int) ($visits['product'] ?? 0);
        $reservations = Apartado::where('seller_id', $seller->id)->whereBetween('created_at', $range);
        $sales = ProductChat::where('seller_id', $seller->id)->where('negotiation_status', 'concretada')->whereBetween('sale_reported_at', $range);

        return [
            'product_visits' => $productVisits, 'store_visits' => (int) ($visits['store'] ?? 0), 'contacts' => $chats,
            'contact_rate' => $productVisits > 0 ? round($chats / $productVisits * 100, 1) : null,
            'favorites' => DB::table('productos_favoritos')->join('productos', 'productos.id_producto', '=', 'productos_favoritos.id_producto')->join('tiendas', 'tiendas.id_tienda', '=', 'productos.id_tienda')->where('tiendas.id_vendedor', $seller->id)->whereBetween('productos_favoritos.created_at', $range)->count(),
            'reservations' => (clone $reservations)->count(),
            'financial' => (clone $reservations)->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'fulfillment' => (clone $reservations)->selectRaw('fulfillment_status, COUNT(*) as total')->groupBy('fulfillment_status')->pluck('total', 'fulfillment_status'),
            'verified_deposits' => (float) (clone $reservations)->where('estado', 'pagado')->sum('monto'),
            'reported_sales' => (clone $sales)->count(), 'reported_amount' => (float) $sales->sum('reported_sale_amount'),
        ];
    }

    public function products(User $seller, Carbon $from, Carbon $to)
    {
        $range = [$from->copy()->startOfDay()->utc(), $to->copy()->endOfDay()->utc()];

        return Producto::withTrashed()->whereHas('tienda', fn ($q) => $q->where('id_vendedor', $seller->id))->select('productos.*')
            ->selectSub(DB::table('seller_daily_metrics')->selectRaw('COALESCE(SUM(visits), 0)')->where('seller_id', $seller->id)->where('kind', 'product')->whereColumn('target_id', 'productos.id_producto')->whereBetween('day', [$from->toDateString(), $to->toDateString()]), 'interest_visits')
            ->selectSub(ProductChat::selectRaw('COUNT(*)')->where('seller_id', $seller->id)->whereColumn('id_producto', 'productos.id_producto')->whereBetween('created_at', $range), 'interest_contacts')
            ->selectSub(DB::table('productos_favoritos')->selectRaw('COUNT(*)')->whereColumn('id_producto', 'productos.id_producto')->whereBetween('created_at', $range), 'interest_favorites')
            ->selectSub(Apartado::selectRaw('COUNT(*)')->where('seller_id', $seller->id)->whereColumn('id_producto', 'productos.id_producto')->whereBetween('created_at', $range), 'interest_reservations')
            ->selectSub(Apartado::selectRaw('COALESCE(SUM(monto), 0)')->where('seller_id', $seller->id)->where('estado', 'pagado')->whereColumn('id_producto', 'productos.id_producto')->whereBetween('created_at', $range), 'verified_deposits')
            ->selectSub(ProductChat::selectRaw('COUNT(*)')->where('seller_id', $seller->id)->where('negotiation_status', 'concretada')->whereColumn('id_producto', 'productos.id_producto')->whereBetween('sale_reported_at', $range), 'reported_sales')
            ->selectSub(ProductChat::selectRaw('COALESCE(SUM(reported_sale_amount), 0)')->where('seller_id', $seller->id)->where('negotiation_status', 'concretada')->whereColumn('id_producto', 'productos.id_producto')->whereBetween('sale_reported_at', $range), 'reported_amount');
    }
}
