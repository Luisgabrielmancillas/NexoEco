@extends('layouts.marketplace')
@section('title', $producto->nombre_producto . ' | NexoEco')
@section('content')
<div class="product-detail-page nexo-product">
    <div class="nexo-container">
        <nav class="product-breadcrumb" aria-label="Navegación del producto"><a href="{{ route('marketplace.index') }}">Inicio</a><span>/</span>@if($producto->categoria)<a href="{{ route('marketplace.index', ['categoria' => $producto->id_categoria]) }}">{{ $producto->categoria->nombre_categoria }}</a><span>/</span>@endif<span>{{ $producto->nombre_producto }}</span></nav>
        <div class="product-detail-grid">
            <section class="product-visual" aria-label="Fotos del producto">
                <div class="product-main-image">
                    @if($producto->tienda)<a href="{{ route('tiendas.show', $producto->tienda) }}" class="product-store-back" data-back-store><x-market-icon name="arrow"/>Volver a la tienda</a>@endif
                    <span class="product-image-favorite">@include('marketplace.partials.favorite-button', ['tipo' => 'productos', 'item' => $producto])</span>
                    @if($imagenes->isNotEmpty())
                        <img id="producto-imagen-principal" src="{{ $imagenes->first()['url'] }}" alt="{{ $imagenes->first()['alt'] }}">
                    @else
                        <div class="product-main-placeholder"><x-market-icon name="bags"/><strong>Conoce este producto</strong><span>El vendedor todavía no ha añadido fotos.</span></div>
                    @endif
                    <span class="product-gallery-label">Hecho para descubrir cerca de ti</span>
                </div>
                @if($imagenes->count() > 1)
                    <div class="product-thumbnails" aria-label="Galería del producto">@foreach($imagenes as $index => $imagen)<button type="button" class="product-thumbnail {{ $index === 0 ? 'active' : '' }}" data-product-image="{{ $imagen['url'] }}" data-product-alt="{{ $imagen['alt'] }}" aria-label="Ver imagen {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"><img src="{{ $imagen['url'] }}" alt="" loading="lazy"></button>@endforeach</div>
                @endif
                <div class="product-local-note"><span class="product-icon-circle"><x-market-icon name="store"/></span><div><strong>Tu próxima compra puede ser local</strong><p>Descubre negocios, conversa con quien vende y acuerda una entrega que te convenga.</p></div></div>
            </section>
            <section class="product-info">
                <div class="product-title-meta">@if($producto->categoria)<a class="product-category-badge" href="{{ route('marketplace.index', ['categoria' => $producto->id_categoria]) }}">{{ $producto->categoria->nombre_categoria }}</a>@endif<span class="product-community-label">Comercio de tu comunidad</span></div>
                <h1 class="product-detail-title">{{ $producto->nombre_producto }}</h1>
                @if($producto->opiniones_count)<a href="#opiniones" class="product-review-link"><x-market-icon name="star"/>{{ number_format($producto->opiniones_avg_calificacion, 1) }} <span>· {{ $producto->opiniones_count }} {{ $producto->opiniones_count === 1 ? 'opinión' : 'opiniones' }}</span></a>@else<p class="product-review-empty">¿Ya lo conoces? Comparte tu experiencia con la comunidad.</p>@endif
                <div class="product-price-card"><div><span class="product-small-label">Precio del producto</span><p class="product-detail-price">${{ number_format((float) $producto->precio, 2) }} <small>MXN</small></p></div><span class="product-price-caption">La compra y la entrega<br>se acuerdan con el vendedor.</span></div>
                @if($producto->tienda)
                    @php($logoTienda = \App\Support\MarketplaceImage::url($producto->tienda->logo_tienda))
                    <a class="product-seller-card" href="{{ route('tiendas.show', $producto->tienda) }}" aria-label="Visitar tienda {{ $producto->tienda->nombre_tienda }}"><span class="product-seller-logo">@if($logoTienda)<img src="{{ $logoTienda }}" alt="Logo de {{ $producto->tienda->nombre_tienda }}">@else<x-market-icon name="store"/>@endif</span><div><span class="product-small-label">Conoce al vendedor</span><strong>{{ $producto->tienda->nombre_tienda }}</strong><span>Ver su tienda y más productos</span></div><x-market-icon name="chevron"/></a>
                @endif
                @include('marketplace.partials.product-contact')
                @if($producto->tienda)@include('marketplace.partials.store-directions', ['tienda' => $producto->tienda])@endif
            </section>
        </div>
        <div class="product-lower-grid">
            <section class="product-description"><div class="product-section-heading"><span class="product-icon-circle"><x-market-icon name="bags"/></span><div><span class="product-small-label">Los detalles importan</span><h2>Acerca de este producto</h2></div></div><p class="product-description-text">{{ $producto->descripcion ?: 'Pregunta al vendedor por las características, medidas y disponibilidad de este producto.' }}</p>@if($producto->codigo_producto)<div class="product-reference">Referencia del producto <span>{{ $producto->codigo_producto }}</span></div>@endif</section>
            <section class="product-how-it-works"><span class="product-small-label">Así de cerca, así de fácil</span><h2>De la consulta a la entrega</h2><ol><li><span>01</span><div><strong>Pregunta lo que necesites</strong><p>Confirma detalles y disponibilidad en el chat.</p></div></li><li><span>02</span><div><strong>Acuerda con el vendedor</strong><p>{{ $producto->apartados_activos ? 'Revisa las condiciones y aparta con un anticipo si lo deseas.' : 'Acuerden el precio, la forma de pago y los detalles de la compra.' }}</p></div></li><li><span>03</span><div><strong>Coordinen la entrega</strong><p>Elijan el lugar y horario y acuerden cómo completar la compra.</p></div></li></ol></section>
        </div>
        <div class="product-community-strip"><x-market-icon name="heart"/><p><strong>Comprar cerca también conecta.</strong> NexoEco facilita el encuentro entre compradores y negocios locales.</p><a href="{{ route('marketplace.index') }}">Seguir explorando <span aria-hidden="true">→</span></a></div>
        <div class="product-reviews">@include('marketplace.partials.opiniones', ['tipo' => 'productos', 'item' => $producto])</div>
        <div class="product-report">@include('marketplace.partials.content-report', ['reportType' => 'productos', 'reportId' => $producto->getKey()])</div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const main = document.getElementById('producto-imagen-principal');
    if (!main) return;
    const thumbnails = document.querySelectorAll('[data-product-image]');
    thumbnails.forEach((button) => button.addEventListener('click', () => {
        main.src = button.dataset.productImage;
        main.alt = button.dataset.productAlt;
        thumbnails.forEach((other) => { other.classList.toggle('active', other === button); other.setAttribute('aria-pressed', String(other === button)); });
    }));
});
</script>
@endpush
