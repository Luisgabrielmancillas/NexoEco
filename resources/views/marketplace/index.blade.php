@extends('layouts.marketplace')
@section('title', 'NexoEco - Marketplace local')
@section('content')
<main class="nexo-container market-discovery">
    @if($solicitudVendedor?->estado === \App\Models\SolicitudVendedor::ESTADO_EN_REVISION && ! auth()->user()->tieneTipo('vendedor'))
        <div class="buyer-notice" role="status"><h2>Tu cuenta de vendedor está en revisión</h2>Recibimos tus datos y documentos. Mientras revisamos tu solicitud, puedes usar tu cuenta como comprador. <a class="buyer-text-link" href="{{ route('vendedor.register') }}">Consultar mi solicitud</a></div>
    @endif
    @if($errors->any())<div class="buyer-notice" role="alert">{{ $errors->first() }}</div>@endif
    <section class="market-hero">
        <div class="hero-content"><span class="hero-tag">Marketplace local</span><h1 class="hero-title">Productos de emprendedores cerca de ti</h1><p class="hero-description">Descubre productos locales, compara opciones y conecta directamente con emprendedores de tu comunidad.</p><a href="#productos" class="hero-button">Ver productos <x-market-icon name="chevron"/></a></div>
        <div class="hero-decoration" aria-hidden="true"><x-market-icon name="store"/></div>
    </section>
    @if($busqueda || $categoriaSeleccionada || $seccionSeleccionada)
        <div class="market-filter-summary"><span>Mostrando {{ $seccionSeleccionada ? \App\Support\MarketplaceDepartments::ALL[$seccionSeleccionada]['label'] : ($categoriaSeleccionada ? $categorias->firstWhere('id_categoria', $categoriaSeleccionada)?->nombre_categoria : 'todo el catálogo') }}@if($busqueda) · “{{ $busqueda }}”@endif</span><a href="{{ route($catalogRoute) }}">Limpiar filtros <x-market-icon name="close"/></a></div>
    @endif
    <section id="tiendas" aria-label="Tiendas del marketplace" class="market-discovery-section">
        <div class="buyer-section-heading"><div><span class="buyer-eyebrow">Conoce a tu comunidad</span><h2>Tiendas locales</h2><p>Encuentra tu próximo negocio favorito.</p></div><span class="market-result-count">{{ $tiendas->total() }} {{ $tiendas->total() === 1 ? 'tienda' : 'tiendas' }}</span></div>
        @if($tiendas->count())
            <div class="market-store-row">@foreach($tiendas as $tienda)@include('marketplace.partials.store-card')@endforeach</div>
            @if($tiendas->hasPages())<div class="market-pagination">{{ $tiendas->links() }}</div>@endif
        @else
            <div class="market-small-empty"><x-market-icon name="store"/><p>{{ $busqueda || $categoriaSeleccionada || $seccionSeleccionada ? 'No encontramos tiendas con esos filtros.' : 'Todavía no hay tiendas publicadas.' }}</p></div>
        @endif
    </section>
    <section id="productos" aria-label="Productos del marketplace" class="market-discovery-section">
        <div class="buyer-section-heading"><div><span class="buyer-eyebrow">Descubre el catálogo</span><h2>Productos para ti</h2><p>Explora lo que ofrecen los negocios de tu comunidad.</p></div><span class="market-result-count">{{ $productos->total() }} {{ $productos->total() === 1 ? 'producto' : 'productos' }}</span></div>
        @if($productos->count())
            <div class="buyer-grid marketplace-products-grid">@foreach($productos as $producto)@include('marketplace.partials.product-card')@endforeach</div>
            @if($productos->hasPages())<div class="market-pagination">{{ $productos->links() }}</div>@endif
        @else
            <div class="buyer-empty"><h2>No encontramos productos</h2><p>Prueba otra categoría o busca algo diferente.</p><a href="{{ route($catalogRoute) }}" class="buyer-button">Ver todos los productos</a></div>
        @endif
    </section>
</main>
@endsection