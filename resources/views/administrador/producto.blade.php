@extends('layouts.admin-panel')
@section('title', 'Revisión de producto · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración · Catálogo</span><h1>{{ $producto->nombre_producto }}</h1><a href="{{ route('admin.catalogo', 'productos') }}" class="buyer-text-link">← Volver a productos</a></div>
<div class="admin-columns">
    <section class="buyer-panel"><h2>Datos del producto</h2><dl class="admin-data"><dt>Código</dt><dd>{{ $producto->codigo_producto }}</dd><dt>Precio</dt><dd>${{ number_format($producto->precio, 2) }}</dd><dt>Categoría</dt><dd>{{ $producto->categoria?->nombre_categoria ?? 'Sin categoría' }}</dd><dt>Tienda</dt><dd>@if($producto->tienda)<a class="buyer-text-link" href="{{ route('admin.tiendas.show', $producto->tienda) }}">{{ $producto->tienda->nombre_tienda }}</a>@else Sin tienda @endif</dd><dt>Publicación</dt><dd>{{ $producto->fecha_publicacion?->format('d/m/Y') ?? 'Sin fecha' }}</dd><dt>Opiniones</dt><dd>{{ $producto->opiniones_count }} · {{ number_format($producto->opiniones_avg_calificacion ?? 0, 1) }}/5</dd></dl><p class="buyer-review-text">{{ $producto->descripcion ?: 'Sin descripción.' }}</p></section>
    <section class="buyer-panel"><h2>Imágenes publicadas</h2>@forelse($producto->imagenes as $imagen)@php($url = \App\Support\MarketplaceImage::url($imagen->imagen_url))@if($url)<img class="admin-detail-image" src="{{ $url }}" alt="{{ $producto->nombre_producto }}" loading="lazy">@endif @empty @php($url = \App\Support\MarketplaceImage::url($producto->imagen_url))@if($url)<img class="admin-detail-image" src="{{ $url }}" alt="{{ $producto->nombre_producto }}">@else<p class="buyer-muted">Sin imágenes publicadas.</p>@endif @endforelse</section>
</div>
@include('administrador.partials.reviews')
@endsection
