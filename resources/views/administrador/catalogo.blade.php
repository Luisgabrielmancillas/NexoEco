@extends('layouts.admin-panel')
@section('title', 'Catálogo · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración · Marketplace</span><h1>{{ $tipo === 'productos' ? 'Productos publicados' : 'Tiendas publicadas' }}</h1><p class="buyer-description">{{ $items->total() }} registros encontrados en el catálogo real.</p></div>
<nav class="buyer-tabs"><a class="{{ $tipo === 'tiendas' ? 'active' : '' }}" href="{{ route('admin.catalogo', 'tiendas') }}">Tiendas</a><a class="{{ $tipo === 'productos' ? 'active' : '' }}" href="{{ route('admin.catalogo', 'productos') }}">Productos</a></nav>
<form method="GET" class="buyer-form admin-filters"><div class="buyer-field"><label for="q">Buscar en el catálogo</label><input id="q" name="q" value="{{ request('q') }}" maxlength="100" placeholder="{{ $tipo === 'productos' ? 'Nombre, código o tienda' : 'Nombre de tienda' }}"></div><button class="buyer-button" type="submit">Buscar</button></form>
@if($tipo === 'productos')<div class="buyer-grid">@foreach($items as $producto)@include('administrador.partials.product-card')@endforeach</div>@else<div class="buyer-grid">@foreach($items as $tienda)@include('administrador.partials.store-card')@endforeach</div>@endif
@if(!$items->count())<div class="buyer-empty"><h2>No hay resultados</h2><p>Prueba con otra búsqueda.</p></div>@endif<div style="margin-top:24px;">{{ $items->links() }}</div>
@endsection
