@php($savedPoint = app(\App\Services\LocalDiscovery::class)->position($ubicacionComprador ?? null))
@if($savedPoint)
<div class="nexo-container location-awareness" data-location-awareness data-lat="{{ $savedPoint[0] }}" data-lng="{{ $savedPoint[1] }}" data-drift-km="{{ config('discovery.location_drift_km') }}">
    <div><span class="location-awareness-dot" aria-hidden="true"></span><p data-location-check-status>Las tiendas cercanas se muestran desde tu ubicación guardada.</p></div>
    <button type="button" class="buyer-text-link" data-location-check>Comprobar ubicación actual</button>
    <button type="button" class="buyer-button secondary" data-location-update data-dialog-open="buyer-location-dialog" hidden>Actualizar mi ubicación</button>
    <button type="button" class="buyer-text-link" data-location-keep hidden>Mantener la guardada</button>
</div>
@endif
