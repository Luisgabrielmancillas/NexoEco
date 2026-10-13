@php($image = \App\Support\MarketplaceImage::url($producto->imagenPrincipal?->imagen_url ?: $producto->imagen_url))
<article class="product-card catalog-card">
    <div class="favorite-placement">@include('marketplace.partials.favorite-button', ['tipo' => 'productos', 'item' => $producto])</div>
    <a href="{{ route('productos.show', $producto) }}" class="product-image-link" aria-label="Ver {{ $producto->nombre_producto }}">
        <div class="product-image catalog-image">
            @if(($producto->promotion_priority ?? 0) > 0)<span class="promotion-badge">Patrocinado</span>@endif
            @if($image)<img src="{{ $image }}" alt="{{ $producto->nombre_producto }}" loading="lazy">@else<span class="product-placeholder" aria-hidden="true">📦</span>@endif
            @if($producto->codigo_producto)<span class="product-code">{{ $producto->codigo_producto }}</span>@endif
        </div>
    </a>
    <div class="product-body catalog-body">
        @if(($producto->is_pinned ?? 0) > 0)<span class="growth-pin-badge">Fijado por la tienda</span>@endif
        <h3 class="product-name"><a href="{{ route('productos.show', $producto) }}">{{ $producto->nombre_producto }}</a></h3>
        @if($producto->tienda)<div class="product-shop catalog-meta"><a href="{{ route('tiendas.show', $producto->tienda) }}">{{ $producto->tienda->nombre_tienda }}</a></div>@endif
        <div class="product-category catalog-meta">{{ $producto->categoria?->nombre_categoria ?? 'Sin categoría' }}</div>
        <div class="product-price catalog-price">${{ number_format((float) $producto->precio, 2) }}</div>
        @include('marketplace.partials.commercial-offer', ['compact' => true])
        @if($producto->fecha_publicacion)<div class="product-date catalog-meta">Publicado {{ $producto->fecha_publicacion->format('d/m/Y') }}</div>@endif
        <a href="{{ route('productos.show', $producto) }}" class="product-button buyer-button">Ver producto</a>
    </div>
</article>
