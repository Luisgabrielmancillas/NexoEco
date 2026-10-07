@extends('layouts.marketplace')
@section('title', 'Mis favoritos · NexoEco')
@section('content')
<div class="nexo-container"><main class="buyer-page" data-favorites-page>
    <span class="buyer-eyebrow">Tu selección</span>
    <h1>Mis favoritos</h1>
    <p class="buyer-description">Guarda lo que te gusta y vuelve a encontrarlo aquí.</p>
    <nav class="buyer-tabs" aria-label="Tipo de favoritos">
        <a class="{{ $tipo === 'tiendas' ? 'active' : '' }}" href="{{ route('comprador.favoritos', 'tiendas') }}">Tiendas favoritas</a>
        <a class="{{ $tipo === 'productos' ? 'active' : '' }}" href="{{ route('comprador.favoritos', 'productos') }}">Productos favoritos</a>
    </nav>
    @if($items->count())
        <p class="buyer-muted" style="margin-bottom:16px;">{{ $items->total() }} {{ $tipo === 'productos' ? ($items->total() === 1 ? 'producto guardado' : 'productos guardados') : ($items->total() === 1 ? 'tienda guardada' : 'tiendas guardadas') }}</p>
        <div class="{{ $tipo === 'tiendas' ? 'market-store-row' : 'buyer-grid' }}">
            @foreach($items as $item)
                @if($tipo === 'productos')
                    @include('marketplace.partials.product-card', ['producto' => $item])
                @else
                    @include('marketplace.partials.store-card', ['tienda' => $item])
                @endif
            @endforeach
        </div>
        <div style="margin-top:24px;">{{ $items->links() }}</div>
    @else
        <div class="buyer-empty"><h2>Aquí empieza tu selección</h2><p>Presiona la estrella en {{ $tipo === 'productos' ? 'un producto' : 'una tienda' }} para guardarlo en tus favoritos.</p><a class="buyer-button" href="{{ route('comprador.dashboard') }}{{ $tipo === 'tiendas' ? '#tiendas' : '#productos' }}">Explorar el marketplace</a></div>
    @endif
</main></div>
@endsection
