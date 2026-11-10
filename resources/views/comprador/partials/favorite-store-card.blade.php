@php($logo = \App\Support\MarketplaceImage::url($tienda->logo_tienda))
<article class="favorite-store-card">
    <div class="favorite-store-banner"><span class="favorite-store-tag"><x-seller-icon name="store"/>Negocio de tu comunidad</span><div class="favorite-placement">@include('marketplace.partials.favorite-button', ['tipo' => 'tiendas', 'item' => $tienda])</div></div>
    <div class="favorite-store-body">
        <a href="{{ route('tiendas.show', $tienda) }}" class="favorite-store-logo" aria-label="Ver tienda {{ $tienda->nombre_tienda }}">@if($logo)<img src="{{ $logo }}" alt="" loading="lazy">@else<x-seller-icon name="store"/>@endif</a>
        <h3><a href="{{ route('tiendas.show', $tienda) }}">{{ $tienda->nombre_tienda }}</a></h3>
        <p class="favorite-store-description">{{ \Illuminate\Support\Str::limit($tienda->descripcion_tienda ?: 'Conoce sus productos y encuentra algo especial cerca de ti.', 140) }}</p>
        <span class="favorite-store-count"><x-seller-icon name="box"/>{{ $tienda->productos_count }} {{ (int) $tienda->productos_count === 1 ? 'producto publicado' : 'productos publicados' }}</span>
        <a class="seller-button secondary" href="{{ route('tiendas.show', $tienda) }}">Visitar tienda<x-seller-icon name="arrow"/></a>
    </div>
</article>
