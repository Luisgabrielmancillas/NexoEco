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
    @include('marketplace.partials.nearby-stores')
    @if($tiendasRecomendadas->isNotEmpty())
    <section class="market-discovery-section" aria-labelledby="recommended-stores-title"><div class="buyer-section-heading"><div><span class="buyer-eyebrow">Negocios para descubrir</span><h2 id="recommended-stores-title">Tiendas recomendadas</h2><p>Espacios patrocinados por negocios de tu comunidad.</p></div></div><div class="market-store-row">@foreach($tiendasRecomendadas as $tienda)@include('marketplace.partials.store-card', ['sponsored' => true])@endforeach</div></section>
    @endif
    @if($busqueda || $categoriaSeleccionada || $seccionSeleccionada)
        <div class="market-filter-summary"><span>Mostrando {{ $seccionSeleccionada ? \App\Support\MarketplaceDepartments::ALL[$seccionSeleccionada]['label'] : ($categoriaSeleccionada ? $categorias->firstWhere('id_categoria', $categoriaSeleccionada)?->nombre_categoria : 'todo el catálogo') }}@if($busqueda) · “{{ $busqueda }}”@endif</span><a href="{{ route($catalogRoute) }}">Limpiar filtros <x-market-icon name="close"/></a></div>
    @endif
    <section id="tiendas" aria-label="Tiendas del marketplace" class="market-discovery-section">
        <div class="buyer-section-heading"><div><span class="buyer-eyebrow">Conoce a tu comunidad</span><h2>Tiendas locales</h2><p>Encuentra tu próximo negocio favorito.</p></div></div>
        @if($tiendas->count())
            <x-store-carousel id="local-store-track" label="Tiendas locales" :view-all-url="route('comprador.tiendas', request()->only('q', 'categoria', 'seccion'))">@foreach($tiendas as $tienda)@include('marketplace.partials.store-card')@endforeach</x-store-carousel>
        @else
            <div class="market-small-empty"><x-market-icon name="store"/><p>{{ $busqueda || $categoriaSeleccionada || $seccionSeleccionada ? 'No encontramos tiendas con esos filtros.' : 'Todavía no hay tiendas publicadas.' }}</p></div>
        @endif
    </section>
    <section id="productos" aria-label="Productos del marketplace" class="market-discovery-section">
        <div class="buyer-section-heading"><div><span class="buyer-eyebrow">Descubre el catálogo</span><h2>Productos para ti</h2><p>Explora lo que ofrecen los negocios de tu comunidad.</p></div></div>
        @if($productos->count())
            <div class="buyer-grid marketplace-products-grid">@foreach($productos as $producto)@include('marketplace.partials.product-card')@endforeach</div>
            @if($productos->hasPages())<div class="market-pagination">{{ $productos->links('marketplace.partials.store-pagination', ['entity' => 'productos']) }}</div>@endif
        @else
            <div class="buyer-empty"><h2>No encontramos productos</h2><p>Prueba otra categoría o busca algo diferente.</p><a href="{{ route($catalogRoute) }}" class="buyer-button">Ver todos los productos</a></div>
        @endif
    </section>
</main>

{-- Modal Vende con Nosotros para Invitados --}}
@guest
<div id="vende-con-nosotros-modal" class="market-modal-overlay" style="display: flex; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div class="market-modal-card" style="background: #fff; padding: 2rem; border-radius: 12px; max-width: 480px; width: 90%; text-align: center; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <button type="button" onclick="document.getElementById('vende-con-nosotros-modal').style.display='none'" style="position: absolute; top: 12px; right: 16px; background: none; border: none; font-size: 1.5rem; cursor: pointer;" aria-label="Cerrar">&times;</button>
        <x-market-icon name="store" style="width: 48px; height: 48px; color: #2563eb; margin-bottom: 1rem;"/>
        <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem;">¡Haz crecer tu negocio local!</h2>
        <p style="color: #4b5563; margin-bottom: 1.5rem;">Únete a NexoEco, publica tus productos y llega a cientos de clientes cerca de ti.</p>
        <a href="{{ route('vendedor.register') }}" class="buyer-button" style="display: inline-block; width: 100%; padding: 0.75rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600;">Comenzar a vender</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('vende-con-nosotros-modal');
        if (modal) {
            modal.style.display = 'flex';
        }
    });
</script>
@endguest

@endsection
