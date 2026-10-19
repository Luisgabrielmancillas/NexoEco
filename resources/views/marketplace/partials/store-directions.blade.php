@php
    $discovery = app(\App\Services\LocalDiscovery::class);
    $originPoint = $discovery->position($ubicacionComprador ?? null);
    $storePoint = $discovery->position(['latitud' => $tienda->latitud, 'longitud' => $tienda->longitud]);
    $mapsUrl = $discovery->directionsUrl($tienda, $ubicacionComprador ?? null);
    $storeDistance = $originPoint && $storePoint ? $discovery->distance($originPoint, $storePoint) : null;
@endphp
<section class="store-directions-card" aria-label="Cómo llegar a {{ $tienda->nombre_tienda }}">
    <span class="directions-icon"><x-seller-icon name="pin"/></span>
    <div class="directions-content"><strong>{{ $storeDistance !== null ? 'A '.number_format($storeDistance, 1).' km de tu ubicación guardada' : 'Visita este negocio' }}</strong>
        <p>@if($storeDistance !== null)@if($storeDistance > config('discovery.nearby_radius_km'))Esta tienda está lejos del punto guardado. @endif Distancia en línea recta. Consulta la ruta por calles antes de salir.@elseif(!$storePoint){{ $tienda->direccion ? 'Consulta la dirección en Google Maps.' : 'La tienda todavía no ha registrado su ubicación.' }}@else Guarda tu punto en el mapa para calcular la ruta.@endif</p>
        <div class="directions-actions">
            @if($originPoint && $storePoint)<button type="button" class="buyer-button" data-store-route data-route-url="{{ route('tiendas.ruta', $tienda) }}" data-store-name="{{ $tienda->nombre_tienda }}" data-maps-url="{{ $mapsUrl }}" data-dialog-open="store-route-dialog">Ver ruta y cómo llegar</button>
            @elseif($storePoint)<button type="button" class="buyer-button secondary" data-dialog-open="buyer-location-dialog">Elegir mi ubicación</button>@endif
            @if($mapsUrl)<a href="{{ $mapsUrl }}" class="buyer-text-link" target="_blank" rel="noopener noreferrer">Abrir en Google Maps ↗</a>@endif
            @if($storeDistance !== null && $storeDistance > config('discovery.nearby_radius_km'))<button type="button" class="buyer-text-link" data-dialog-open="buyer-location-dialog">Cambiar ubicación guardada</button>@endif
        </div>
    </div>
</section>
