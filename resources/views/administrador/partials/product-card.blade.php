@php($image = \App\Support\MarketplaceImage::url($producto->imagenPrincipal?->imagen_url ?: $producto->imagen_url))
<article class="catalog-card">
    <a href="{{ route('admin.productos.show', $producto) }}" class="product-image-link" aria-label="Revisar {{ $producto->nombre_producto }}"><div class="catalog-image">@if($image)<img src="{{ $image }}" alt="{{ $producto->nombre_producto }}" loading="lazy">@else<span class="product-placeholder" aria-hidden="true">📦</span>@endif</div></a>
    <div class="catalog-body">
        <h3><a href="{{ route('admin.productos.show', $producto) }}">{{ $producto->nombre_producto }}</a></h3>
        @if($producto->tienda)<p class="catalog-meta"><a href="{{ route('admin.tiendas.show', $producto->tienda) }}">{{ $producto->tienda->nombre_tienda }}</a></p>@endif
        <p class="catalog-meta">{{ $producto->codigo_producto }} · {{ $producto->categoria?->nombre_categoria ?? 'Sin categoría' }}</p>
        <p class="catalog-price">${{ number_format((float) $producto->precio, 2) }}</p>
        <a href="{{ route('admin.productos.show', $producto) }}" class="buyer-button">Revisar producto</a>
    </div>
</article>
