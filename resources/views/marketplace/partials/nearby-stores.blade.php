<section id="tiendas-cercanas" class="nearby-section" aria-labelledby="nearby-title">
    <div class="buyer-section-heading"><div><span class="buyer-eyebrow">A unos pasos de tu comunidad</span><h2 id="nearby-title">Tiendas cerca de ti</h2><p>Desde {{ $ubicacionComprador ? implode(', ', array_filter([$ubicacionComprador['colonia'] ?? null, $ubicacionComprador['ciudad'] ?? null])) : 'la ubicación que elijas' }} · Hasta {{ config('discovery.nearby_radius_km') }} km en línea recta.</p></div><button class="buyer-text-link" type="button" data-dialog-open="buyer-location-dialog">Cambiar ubicación</button></div>
    @if(!$ubicacionMapa)
        <div class="nearby-empty"><span class="directions-icon"><x-seller-icon name="pin"/></span><div><h3>Encuentra los negocios que tienes más cerca</h3><p>{{ $ubicacionComprador ? 'Tu dirección está guardada. Añade el punto exacto en el mapa para calcular distancias y rutas.' : 'Guarda tu ubicación en el mapa y descubre tiendas de tu zona.' }}</p><button type="button" class="buyer-button" data-dialog-open="buyer-location-dialog">Elegir ubicación en el mapa</button></div></div>
    @elseif($tiendasCercanas->isEmpty())
        <div class="nearby-empty"><span class="directions-icon"><x-market-icon name="store"/></span><div><h3>Aún no encontramos tiendas a {{ config('discovery.nearby_radius_km') }} km de este punto</h3><p>Puedes cambiar tu ubicación o explorar todos los negocios de Manzanillo más abajo.</p><button type="button" class="buyer-button secondary" data-dialog-open="buyer-location-dialog">Revisar mi ubicación</button></div></div>
    @else
        <div class="nearby-stores-grid">
            @foreach($tiendasCercanas as $nearbyStore)
                @php($nearbyLogo = \App\Support\MarketplaceImage::url($nearbyStore->logo_tienda))
                <article class="nearby-store-card"><div class="nearby-store-heading"><a href="{{ route('tiendas.show', $nearbyStore) }}" class="nearby-store-logo">@if($nearbyLogo)<img src="{{ $nearbyLogo }}" alt="Logo de {{ $nearbyStore->nombre_tienda }}" loading="lazy">@else<x-market-icon name="store"/>@endif</a><div><span class="nearby-distance">A {{ number_format($nearbyStore->distance_km, 1) }} km</span><h3><a href="{{ route('tiendas.show', $nearbyStore) }}">{{ $nearbyStore->nombre_tienda }}</a></h3><p>{{ implode(', ', array_filter([$nearbyStore->colonia, $nearbyStore->ciudad])) }}</p></div></div>
                    <div class="nearby-store-actions"><a class="buyer-text-link" href="{{ route('tiendas.show', $nearbyStore) }}">Ver tienda</a>@auth<button type="button" class="buyer-button secondary" data-store-route data-route-url="{{ route('tiendas.ruta', $nearbyStore) }}" data-store-name="{{ $nearbyStore->nombre_tienda }}" data-maps-url="{{ app(\App\Services\LocalDiscovery::class)->directionsUrl($nearbyStore, $ubicacionComprador) }}" data-dialog-open="store-route-dialog">Cómo llegar</button>@else<a class="buyer-button secondary" href="{{ route('login') }}">Cómo llegar</a>@endauth</div>
                </article>
            @endforeach
        </div>
    @endif
</section>
