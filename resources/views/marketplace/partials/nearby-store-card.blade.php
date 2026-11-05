@php($nearbyLogo = \App\Support\MarketplaceImage::url($nearbyStore->logo_tienda))
<article class="nearby-store-card">
    <div class="nearby-store-heading">
        <a href="{{ route('tiendas.show', $nearbyStore) }}" class="nearby-store-logo" aria-label="Ver tienda {{ $nearbyStore->nombre_tienda }}">@if($nearbyLogo)<img src="{{ $nearbyLogo }}" alt="" loading="lazy">@else<x-market-icon name="store"/>@endif</a>
        <div>@if($nearbyStore->distance_km !== null)<span class="nearby-distance">A {{ number_format($nearbyStore->distance_km, 1) }} km</span>@endif<h3><a href="{{ route('tiendas.show', $nearbyStore) }}">{{ $nearbyStore->nombre_tienda }}</a></h3><p>{{ implode(', ', array_filter([$nearbyStore->colonia, $nearbyStore->ciudad])) ?: 'Ubicación no indicada' }}</p></div>
    </div>
    <div class="nearby-store-actions"><a class="buyer-text-link" href="{{ route('tiendas.show', $nearbyStore) }}">Ver tienda</a>@auth<button type="button" class="buyer-button secondary" data-store-route data-route-url="{{ route('tiendas.ruta', $nearbyStore) }}" data-store-name="{{ $nearbyStore->nombre_tienda }}" data-maps-url="{{ app(\App\Services\LocalDiscovery::class)->directionsUrl($nearbyStore, $ubicacionComprador) }}" data-dialog-open="store-route-dialog">Cómo llegar</button>@else<a class="buyer-button secondary" href="{{ route('login') }}">Cómo llegar</a>@endauth</div>
</article>
