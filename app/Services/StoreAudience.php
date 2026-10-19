<?php

namespace App\Services;

use App\Models\CommercialOffer;
use App\Models\Producto;
use App\Models\StoreUpdate;
use App\Models\Tienda;
use App\Notifications\BuyerActivityNotification;
use Illuminate\Support\Facades\DB;

class StoreAudience
{
    public function productPublished(Producto $product): void
    {
        $store = $product->tienda;
        $this->notify($store, 'Nuevo producto en '.$store->nombre_tienda, $product->nombre_producto, route('productos.show', $product), ['event' => 'store.product', 'store_id' => $store->getKey(), 'product_id' => $product->getKey()]);
    }

    public function offerPublished(CommercialOffer $offer): void
    {
        DB::transaction(function () use ($offer) {
            $offer = CommercialOffer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
            if ($offer->notified_at || ! CommercialOffer::visible()->whereKey($offer->id)->exists() || ! $offer->producto) {
                return;
            }
            $offer->update(['notified_at' => now()]);
            $product = $offer->producto;
            $this->notify($product->tienda, 'Oferta en '.$product->tienda->nombre_tienda, $offer->title.' · '.$product->nombre_producto, route('productos.show', $product), ['event' => 'store.offer', 'store_id' => $product->id_tienda, 'offer_id' => $offer->id, 'product_id' => $product->getKey()]);
        });
    }

    public function publish(StoreUpdate $update): void
    {
        DB::transaction(function () use ($update) {
            $update = StoreUpdate::whereKey($update->id)->lockForUpdate()->firstOrFail();
            if ($update->status !== 'scheduled' || $update->publish_at->isFuture()) {
                return;
            }
            $subscription = $update->subscription;
            if (! $subscription->entitled() || ! app(SellerBenefits::class)->subscription($subscription->user)) {
                $update->update(['status' => 'blocked']);

                return;
            }
            $update->update(['status' => 'published', 'published_at' => now()]);
            $this->notify($update->tienda, $update->title, $update->body, route('tiendas.show', $update->tienda).'#novedades', ['event' => 'store.update', 'store_id' => $update->id_tienda, 'update_id' => $update->id]);
        });
    }

    private function notify(Tienda $store, string $title, string $body, string $url, array $context): void
    {
        $store->followers()->where('activo', true)->where('users.id', '!=', $store->id_vendedor)->chunkById(100, function ($followers) use ($title, $body, $url, $context) {
            foreach ($followers as $follower) {
                $follower->notify(new BuyerActivityNotification($title, mb_substr($body, 0, 240).' · Sigues esta tienda; puedes dejar de seguirla desde su perfil.', $url, $context));
            }
        }, 'users.id', 'id');
    }
}
