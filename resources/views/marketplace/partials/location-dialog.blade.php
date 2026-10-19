<dialog id="buyer-location-dialog" class="store-info-dialog buyer-location-dialog" aria-labelledby="buyer-location-title" data-store-dialog>
    <div class="store-dialog-heading"><div><span class="buyer-eyebrow">Compra cerca de ti</span><h2 id="buyer-location-title">¿Dónde te encuentras?</h2></div><button type="button" data-dialog-close aria-label="Cerrar ubicación"><x-market-icon name="close"/></button></div>
    <div class="store-dialog-body"><p class="buyer-muted">Encuentra tu ubicación en el mapa y completa los detalles para identificar tu domicilio.</p>
        <form method="POST" action="{{ route('marketplace.ubicacion.update') }}" class="buyer-form buyer-location-form" data-buyer-location-form>@csrf
            <p class="buyer-field-error" role="alert" data-location-error @if(!$errors->buyerLocation->any()) hidden @endif>{{ $errors->buyerLocation->first() }}</p>
            <button type="button" class="buyer-button location-geolocate" data-buyer-geolocate><x-seller-icon name="pin"/>Usar mi ubicación actual</button>
            <div class="buyer-location-map" data-buyer-map role="region" aria-label="Mapa para seleccionar tu ubicación"></div>
            <p class="buyer-muted location-map-status" data-location-map-status role="status">Toca el mapa o usa tu ubicación actual. Mueve el marcador para afinar el punto.</p>
            <input type="hidden" name="latitud" value="{{ old('latitud', $ubicacionComprador['latitud'] ?? '') }}">
            <input type="hidden" name="longitud" value="{{ old('longitud', $ubicacionComprador['longitud'] ?? '') }}">
            <div class="account-fields">
                @foreach(['ciudad' => ['Ciudad o municipio', true, 100], 'estado' => ['Estado', true, 100], 'colonia' => ['Colonia (opcional)', false, 100], 'codigo_postal' => ['Código postal (opcional)', false, 5]] as $name => [$label, $required, $max])
                <div class="buyer-field"><label for="buyer-location-{{ $name }}">{{ $label }}{{ $required ? ' *' : '' }}</label><input name="{{ $name }}" id="buyer-location-{{ $name }}" value="{{ old($name, $ubicacionComprador[$name] ?? '') }}" maxlength="{{ $max }}" @required($required) @if($name === 'codigo_postal') inputmode="numeric" pattern="[0-9]{5}" @endif></div>
                @endforeach
            </div>
            <div class="buyer-field"><label for="buyer-location-direccion">Calle y número (opcional)</label><input id="buyer-location-direccion" name="direccion" maxlength="250" value="{{ old('direccion', $ubicacionComprador['direccion'] ?? '') }}" autocomplete="street-address"></div>
            <p class="buyer-muted">{{ auth()->check() ? 'Se guardará en tu cuenta. Puedes cambiarla cuando quieras.' : 'Se guardará para esta visita. Inicia sesión para guardarla en tu cuenta.' }}</p>
            <button type="submit" class="buyer-button" data-location-submit>Guardar ubicación</button>
        </form>
    </div>
</dialog>
