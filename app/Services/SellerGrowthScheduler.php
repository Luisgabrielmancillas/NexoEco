<?php

namespace App\Services;

use App\Models\Apartado;
use App\Models\CommercialOffer;
use App\Models\StoreUpdate;
use App\Notifications\BuyerActivityNotification;
use Illuminate\Support\Facades\DB;

class SellerGrowthScheduler
{
    public function run(): void
    {
        CommercialOffer::visible()->whereNull('notified_at')->whereHas('producto')->eachById(function ($offer) {
            app(StoreAudience::class)->offerPublished($offer);
        });
        StoreUpdate::where('status', 'scheduled')->where('publish_at', '<=', now())->eachById(function ($update) {
            app(StoreAudience::class)->publish($update);
        });
        Apartado::where('fulfillment_status', 'pendiente')->whereNotNull('follow_up_at')->where('follow_up_at', '<=', now())->whereNull('reminded_at')
            ->whereHas('seller', fn ($q) => $q->where('activo', true)->whereHas('sellerSubscriptions', fn ($q) => $q->entitled()->where('plan', 'pro')))
            ->eachById(function ($apartado) {
                DB::transaction(function () use ($apartado) {
                    $apartado = Apartado::whereKey($apartado->id)->lockForUpdate()->firstOrFail();
                    if ($apartado->reminded_at || ! $apartado->follow_up_at || $apartado->follow_up_at->isFuture() || $apartado->fulfillment_status !== 'pendiente'
                        || app(SellerBenefits::class)->subscription($apartado->seller)?->plan !== 'pro') {
                        return;
                    }
                    $apartado->seller->notify(new BuyerActivityNotification('Recordatorio de apartado', 'Revisa el seguimiento de '.$apartado->producto_nombre.'. Este recordatorio no cambia el estado del anticipo.', route('vendedor.apartados.index', ['apartado' => $apartado->id]), ['event' => 'apartado.reminder', 'apartado_id' => $apartado->id]));
                    $apartado->update(['reminded_at' => now()]);
                });
            });
    }
}
