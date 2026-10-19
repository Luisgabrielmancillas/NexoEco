@extends('layouts.seller-panel')
@section('title', $tienda ? 'Editar mi tienda | NexoEco' : 'Crea tu tienda | NexoEco')
@section('seller-content')
@php
    $editing = (bool) $tienda;
    $fields = [
        'nombre_tienda' => ['Nombre de la tienda', 'text', true, 120, null],
        'giro' => ['Giro o categoría del negocio', 'text', true, 100, 'Ej. alimentos, artesanías, ropa'],
        'razon_social' => ['Razón social (opcional)', 'text', false, 150, null],
        'telefono' => ['Teléfono de contacto', 'tel', true, 30, 'Ej. 314 123 4567'],
        'email_contacto' => ['Correo de contacto', 'email', true, 255, null],
        'sitio_web' => ['Sitio web o red social (opcional)', 'url', false, 500, 'https://'],
    ];
@endphp
<div class="seller-heading"><div><span class="buyer-eyebrow">Tu negocio, más cerca</span><h1>{{ $editing ? 'Dale vida a tu tienda' : 'Crea tu tienda en NexoEco' }}</h1><p>{{ $editing ? 'Actualiza la información que ven tus compradores.' : 'Ya eres vendedor. Ahora prepara el espacio donde tu comunidad conocerá tu negocio.' }}</p></div>@if($editing)<a class="seller-button secondary" href="{{ route('vendedor.dashboard') }}">Volver al resumen</a>@endif</div>
<div class="seller-setup-layout">
<aside class="seller-setup-guide seller-card"><span class="seller-icon"><x-seller-icon/></span><h2>Todo listo para empezar</h2><p>Estos datos forman la presentación pública de tu negocio.</p><a href="#identidad">01 · Identidad del negocio</a><a href="#imagenes">02 · Portada y logo</a><a href="#ubicacion">03 · Ubicación</a><a href="#horarios">04 · Horarios y atención</a><div class="seller-guide-note"><x-seller-icon name="shield"/><span>Tus documentos de vendedor siguen siendo privados.</span></div></aside>
<form class="seller-form" method="POST" enctype="multipart/form-data" action="{{ route($editing ? 'vendedor.tienda.update' : 'vendedor.tienda.store') }}" data-shop-form>
@csrf @if($editing) @method('PUT') @endif
<section class="seller-card" id="identidad"><div class="seller-section-heading"><span class="seller-step">01</span><div><h2>Identidad del negocio</h2><p>Cuéntales quién eres y cómo pueden contactarte.</p></div></div>
<div class="seller-fields">
@foreach($fields as $name => [$label, $type, $required, $max, $placeholder])
<div class="seller-field"><label for="{{ $name }}">{{ $label }}@if($required)<span aria-hidden="true"> *</span>@endif</label><input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" maxlength="{{ $max }}" value="{{ old($name, $tienda?->$name ?? ($name === 'email_contacto' ? auth()->user()->email : '')) }}" placeholder="{{ $placeholder }}" @required($required)>@error($name)<p class="buyer-field-error">{{ $message }}</p>@enderror</div>
@endforeach
<div class="seller-field"><label for="tipo_negocio">Tipo de negocio *</label><select name="tipo_negocio" id="tipo_negocio" required><option value="">Selecciona una opción</option>@foreach(['local' => 'Local físico', 'servicios' => 'Servicios', 'en_linea' => 'Tienda en línea', 'mixto' => 'Local físico y venta en línea'] as $value => $label)<option value="{{ $value }}" @selected(old('tipo_negocio', $tienda?->tipo_negocio) === $value)>{{ $label }}</option>@endforeach</select></div>
<div class="seller-field full"><label for="descripcion_tienda">Descripción de la tienda *</label><textarea name="descripcion_tienda" id="descripcion_tienda" required minlength="20" maxlength="3000" rows="4" placeholder="¿Qué ofreces y qué hace especial a tu negocio?">{{ old('descripcion_tienda', $tienda?->descripcion_tienda) }}</textarea><p class="seller-help">Entre 20 y 3,000 caracteres. Usa una descripción que ayude a conocerte.</p></div>
</div></section>
<section class="seller-card" id="imagenes"><div class="seller-section-heading"><span class="seller-step">02</span><div><h2>La imagen de tu tienda</h2><p>Una portada que te represente y un logo fácil de reconocer.</p></div></div>
<div class="seller-upload-grid">
@foreach(['logo' => ['Logo del negocio', 'logo_tienda', 'JPG, PNG o WebP. Máximo 3 MB.', 'Tu logo'], 'portada' => ['Imagen de portada', 'portada_tienda', 'JPG, PNG o WebP. Máximo 6 MB. Recomendado: 1600 × 600 px.', 'Tu portada']] as $name => [$label, $column, $hint, $empty])
<div class="seller-upload"><div class="seller-upload-preview {{ $name }}" data-image-container><img data-shop-image-preview="{{ $name }}" @if($tienda?->$column) src="{{ \App\Support\MarketplaceImage::url($tienda->$column) }}" @else hidden @endif alt="Vista previa de {{ strtolower($label) }}"><span data-image-placeholder @if($tienda?->$column) hidden @endif><x-seller-icon name="{{ $name === 'logo' ? 'store' : 'chart' }}"/>{{ $empty }}</span></div><label for="{{ $name }}">{{ $label }}{{ !$editing ? ' *' : '' }}</label><input type="file" name="{{ $name }}" id="{{ $name }}" accept="image/jpeg,image/png,image/webp" data-shop-image-input="{{ $name }}" @required(!$editing)><p class="seller-help">{{ $hint }}@if($editing) Deja vacío para conservar la imagen actual.@endif</p></div>
@endforeach
</div></section>
<section class="seller-card" id="ubicacion"><div class="seller-section-heading"><span class="seller-step">03</span><div><h2>¿Dónde está tu negocio?</h2><p>NexoEco está dedicado a los microemprendimientos del municipio de Manzanillo, Colima. Solo puedes crear una tienda dentro de esta zona.</p></div></div>
<div class="seller-fields">
@foreach(['direccion' => ['Calle y número', 250, null], 'colonia' => ['Colonia', 100, null], 'ciudad' => ['Ciudad o municipio', 100, 'Manzanillo'], 'estado' => ['Estado', 100, 'Colima'], 'codigo_postal' => ['Código postal', 5, null]] as $name => [$label, $max, $default])
<div class="seller-field {{ $name === 'direccion' ? 'full' : '' }}"><label for="{{ $name }}">{{ $label }} *</label><input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, $tienda?->$name ?? $default) }}" maxlength="{{ $max }}" @if($name === 'codigo_postal') inputmode="numeric" pattern="[0-9]{5}" @endif required></div>
@endforeach
<div class="seller-field full"><label for="referencias">Referencias para llegar (opcional)</label><input id="referencias" name="referencias" value="{{ old('referencias', $tienda?->referencias) }}" maxlength="500" placeholder="Ej. frente al parque, local 2"></div>
</div>
<div class="seller-map-toolbar"><strong><x-seller-icon name="pin"/>Ubicación exacta</strong><button type="button" class="seller-button secondary small" data-shop-geolocate>Usar mi ubicación</button></div>
<div class="seller-map" data-shop-map role="region" aria-label="Mapa para seleccionar la ubicación del negocio"></div>
<p class="seller-help" data-map-status aria-live="polite">Toca el mapa o mueve el marcador hasta tu negocio. También puedes escribir las coordenadas.</p>
@error('latitud')<p class="buyer-field-error" role="alert">{{ $message }}</p>@enderror
@error('longitud')<p class="buyer-field-error" role="alert">{{ $message }}</p>@enderror
<div class="seller-fields"><div class="seller-field"><label for="latitud">Latitud *</label><input type="number" step="any" min="-90" max="90" id="latitud" name="latitud" value="{{ old('latitud', $tienda?->latitud) }}" required></div><div class="seller-field"><label for="longitud">Longitud *</label><input type="number" step="any" min="-180" max="180" id="longitud" name="longitud" value="{{ old('longitud', $tienda?->longitud) }}" required></div></div>
<p class="seller-help">Si vendes en línea, indica tu punto de atención o recogida. La ubicación y los datos de contacto serán visibles en tu tienda.</p>
</section>
<section class="seller-card" id="horarios"><div class="seller-section-heading"><span class="seller-step">04</span><div><h2>Horarios y atención</h2><p>Elige los días de atención y cómo entregas tus productos.</p></div></div>
<div class="seller-hours">
@foreach(\App\Http\Requests\StoreBusinessRequest::DAYS as $day => $label)
@php($open = (bool) old("horarios.$day.abierto", $tienda?->horarios[$day]['abierto'] ?? !in_array($day, ['sabado', 'domingo'])))
<div class="seller-hours-row" data-hours-row>
<label class="seller-check"><input type="hidden" name="horarios[{{ $day }}][abierto]" value="0"><input type="checkbox" name="horarios[{{ $day }}][abierto]" value="1" @checked($open) data-hours-open><span>{{ $label }}</span></label>
<div class="seller-hours-times"><input type="time" aria-label="Apertura del {{ strtolower($label) }}" name="horarios[{{ $day }}][inicio]" value="{{ old("horarios.$day.inicio", $tienda?->horarios[$day]['inicio'] ?? '09:00') }}" data-hours-time><span>a</span><input type="time" aria-label="Cierre del {{ strtolower($label) }}" name="horarios[{{ $day }}][fin]" value="{{ old("horarios.$day.fin", $tienda?->horarios[$day]['fin'] ?? '18:00') }}" data-hours-time></div>
<span class="seller-closed" data-hours-closed @if($open) hidden @endif>Cerrado</span>
</div>
@endforeach
</div><p class="seller-help">Los horarios se muestran en la hora local de Manzanillo. Si el cierre es anterior a la apertura, corresponde al día siguiente.</p>
<h3 class="seller-subtitle">Modalidades de atención</h3><div class="seller-service-options">@foreach(['local' => 'Atención en local', 'recoger' => 'Recoger en tienda', 'domicilio' => 'Entrega a domicilio', 'envios' => 'Envíos'] as $value => $label)<label class="seller-check"><input type="checkbox" name="servicios[]" value="{{ $value }}" @checked(in_array($value, old('servicios', $tienda?->servicios ?? [])))>{{ $label }}</label>@endforeach</div>
</section>
<div class="seller-form-actions"><p>Los campos con * son obligatorios.</p><button type="submit" class="seller-button"><x-seller-icon name="{{ $editing ? 'shield' : 'plus' }}"/>{{ $editing ? 'Guardar cambios' : 'Crear mi tienda' }}</button></div>
</form></div>
@endsection
