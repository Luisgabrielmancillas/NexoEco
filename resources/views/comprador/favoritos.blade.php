@extends('layouts.marketplace')
@section('title', ($tipo === 'tiendas' ? 'Tiendas favoritas' : 'Productos favoritos').' · NexoEco')
@section('content')
<main class="nexo-container commerce-page growth-workspace favorites-page" data-favorites-page>
    <x-seller-growth-hero eyebrow="Lo que te gusta, siempre cerca" :title="$tipo === 'tiendas' ? 'Tiendas favoritas' : 'Productos favoritos'" :description="$tipo === 'tiendas' ? 'Un espacio para los negocios que te inspiran. Vuelve a sus tiendas y descubre lo que tienen para ti.' : 'Tu selección personal, lista para volver a descubrirla. Guarda lo que te gusta y encuentra aquí tu próxima compra.'" icon="heart" badge="Tu selección en NexoEco">
        <x-slot:actions><a class="seller-button" href="{{ route('marketplace.index') }}{{ $tipo === 'tiendas' ? '#tiendas' : '#productos' }}">{{ $tipo === 'tiendas' ? 'Descubrir más tiendas' : 'Descubrir más productos' }}<x-seller-icon name="arrow"/></a><a class="seller-button secondary" href="{{ route('comprador.notificaciones') }}">Ver notificaciones</a></x-slot:actions>
        <x-slot:meta><span><x-seller-icon :name="$tipo === 'tiendas' ? 'store' : 'box'"/>{{ $items->total() }} {{ $tipo === 'tiendas' ? ($items->total() === 1 ? 'tienda guardada' : 'tiendas guardadas') : ($items->total() === 1 ? 'producto guardado' : 'productos guardados') }}</span><span><x-seller-icon name="heart"/>Tú eliges qué conservar</span></x-slot:meta>
    </x-seller-growth-hero>
    @if(session('success'))<p class="commerce-alert" role="status">{{ session('success') }}</p>@endif
    <div class="commerce-section-heading"><div><span class="buyer-eyebrow">Tu colección</span><h2>{{ $tipo === 'tiendas' ? 'Negocios para volver' : 'Productos que te encantan' }}</h2></div><p>Guardados más recientes primero</p></div>
    @if($items->count())
        <div class="favorites-grid {{ $tipo === 'tiendas' ? 'favorites-stores' : 'favorites-products' }}">
            @foreach($items as $item)
                @if($tipo === 'productos')
                    @include('marketplace.partials.product-card', ['producto' => $item])
                @else
                    @include('comprador.partials.favorite-store-card', ['tienda' => $item])
                @endif
            @endforeach
        </div>
        <div class="commerce-pagination">{{ $items->links() }}</div>
    @else
        <x-seller-growth-empty icon="heart" title="Aquí empieza tu selección" :description="$tipo === 'tiendas' ? 'Presiona el corazón de una tienda para guardarla aquí. Tu comunidad empieza con un negocio que te guste.' : 'Presiona el corazón de un producto para guardarlo aquí. Tu próxima compra puede estar a un descubrimiento de distancia.'" />
    @endif
</main>
@endsection
