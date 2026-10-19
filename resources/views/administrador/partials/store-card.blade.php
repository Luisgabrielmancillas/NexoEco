@php($logo = \App\Support\MarketplaceImage::url($tienda->logo_tienda))
<article class="catalog-card catalog-store">
    <a href="{{ route('admin.tiendas.show', $tienda) }}" class="catalog-store-logo" aria-label="Revisar {{ $tienda->nombre_tienda }}">@if($logo)<img src="{{ $logo }}" alt="{{ $tienda->nombre_tienda }}" loading="lazy">@else<span aria-hidden="true">🏪</span>@endif</a>
    <h3><a href="{{ route('admin.tiendas.show', $tienda) }}">{{ $tienda->nombre_tienda }}</a></h3>
    <p>{{ \Illuminate\Support\Str::limit($tienda->descripcion_tienda, 100) }}</p>
    <p>{{ $tienda->productos_count }} productos</p>
    <a href="{{ route('admin.tiendas.show', $tienda) }}" class="buyer-text-link">Revisar tienda →</a>
</article>
