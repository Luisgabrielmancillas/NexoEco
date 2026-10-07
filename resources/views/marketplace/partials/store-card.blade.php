@php($logo = \App\Support\MarketplaceImage::url($tienda->logo_tienda))
<article class="market-store-card catalog-store">
    <div class="market-store-image-wrap"><a href="{{ route('tiendas.show', $tienda) }}" class="catalog-store-logo" aria-label="Ver tienda {{ $tienda->nombre_tienda }}">@if($logo)<img src="{{ $logo }}" alt="{{ $tienda->nombre_tienda }}" loading="lazy">@else<x-market-icon name="store"/>@endif</a><div class="favorite-placement">@include('marketplace.partials.favorite-button', ['tipo' => 'tiendas', 'item' => $tienda])</div></div>
    <h3><a href="{{ route('tiendas.show', $tienda) }}">{{ $tienda->nombre_tienda }}</a></h3>
</article>