@extends('layouts.admin-panel')
@section('title', 'Revisión de tienda · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración · Catálogo</span><h1>{{ $tienda->nombre_tienda }}</h1><a href="{{ route('admin.catalogo', 'tiendas') }}" class="buyer-text-link">← Volver a tiendas</a></div>
<section class="buyer-panel"><h2>Datos de la tienda</h2><dl class="admin-data"><dt>Vendedor</dt><dd>@if($tienda->user)<a class="buyer-text-link" href="{{ route('admin.users.edit', $tienda->user) }}">{{ $tienda->user->nombre_completo ?: $tienda->user->name }}</a>@else Sin usuario @endif</dd><dt>Creación</dt><dd>{{ $tienda->fecha_creacion?->format('d/m/Y') ?? 'Sin fecha' }}</dd><dt>Productos</dt><dd>{{ $tienda->productos_count }}</dd><dt>Opiniones</dt><dd>{{ $tienda->opiniones_count }} · {{ number_format($tienda->opiniones_avg_calificacion ?? 0, 1) }}/5</dd></dl><p class="buyer-review-text">{{ $tienda->descripcion_tienda ?: 'Sin descripción.' }}</p></section>
<section style="margin:24px 0;"><h2>Productos publicados</h2><div class="buyer-grid">@forelse($productos as $producto)@include('administrador.partials.product-card')@empty<p class="buyer-muted">Sin productos publicados.</p>@endforelse</div>{{ $productos->withQueryString()->links() }}</section>
@include('administrador.partials.reviews')
@endsection
