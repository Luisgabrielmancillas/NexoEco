@extends('layouts.marketplace')

@section('title', 'NexoEco - Marketplace local')

@push('styles')

<style>
    /* =========================================================
       MARKETPLACE
    ========================================================== */

    .market-page {
        padding: 18px 0 0;
    }

    /* =========================================================
       CATEGORÍAS MÓVILES
    ========================================================== */

    .mobile-categories {
        display: flex;
        gap: 8px;

        margin-bottom: 15px;
        padding-bottom: 3px;

        overflow-x: auto;

        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .mobile-categories::-webkit-scrollbar {
        display: none;
    }

    .mobile-category {
        flex-shrink: 0;

        padding: 8px 13px;

        border: 1px solid var(--nexo-border);
        border-radius: 999px;

        background: #ffffff;
        color: var(--nexo-text);

        font-size: 12px;
        font-weight: 800;

        white-space: nowrap;

        transition:
            background .2s ease,
            border-color .2s ease,
            color .2s ease;
    }

    .mobile-category:hover {
        border-color: var(--nexo-primary);
        color: var(--nexo-primary);
    }

    .mobile-category.active {
        border-color: var(--nexo-primary);
        background: var(--nexo-primary);
        color: #ffffff;
    }

    /* =========================================================
       LAYOUT
    ========================================================== */

    .market-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .market-content {
        min-width: 0;
    }

    /* =========================================================
       SIDEBAR
    ========================================================== */

    .market-sidebar {
        display: none;
    }

    .sidebar-card {
        position: sticky;
        top: 82px;

        overflow: hidden;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 20px;

        box-shadow: var(--nexo-shadow);
    }

    .sidebar-heading {
        padding: 16px;

        border-bottom: 1px solid #F1EBE4;

        font-size: 16px;
        font-weight: 900;
    }

    .sidebar-nav {
        padding: 8px;
    }

    .sidebar-link {
        display: flex;
        align-items: center;

        gap: 8px;

        padding: 10px 11px;

        border-radius: 11px;

        color: var(--nexo-text);

        font-size: 13px;
        font-weight: 700;

        transition:
            background .2s ease,
            color .2s ease;
    }

    .sidebar-link:hover {
        background: var(--nexo-bg);
    }

    .sidebar-link.active {
        background: var(--nexo-primary-soft);
        color: var(--nexo-primary);

        font-weight: 900;
    }

    /* =========================================================
       HERO
    ========================================================== */

    .market-hero {
        position: relative;

        min-height: 215px;

        display: flex;
        align-items: center;

        padding: 25px 21px;

        overflow: hidden;

        border-radius: 23px;

        background:
            linear-gradient(
                125deg,
                #E85D2F 0%,
                #EF683D 55%,
                #FF8556 100%
            );

        color: #ffffff;

        box-shadow:
            0 12px 30px rgba(232, 93, 47, .16);
    }

    .market-hero::before {
        content: "";

        position: absolute;

        width: 210px;
        height: 210px;

        top: -90px;
        right: -55px;

        border-radius: 50%;

        background: rgba(255, 255, 255, .09);
    }

    .market-hero::after {
        content: "";

        position: absolute;

        width: 180px;
        height: 180px;

        left: -50px;
        bottom: -105px;

        border-radius: 50%;

        background: rgba(255, 255, 255, .07);
    }

    .hero-content {
        position: relative;
        z-index: 2;

        max-width: 650px;
    }

    .hero-tag {
        display: inline-flex;

        margin-bottom: 11px;
        padding: 6px 11px;

        border: 1px solid rgba(255, 255, 255, .2);
        border-radius: 999px;

        background: rgba(255, 255, 255, .13);

        font-size: 11px;
        font-weight: 900;
    }

    .hero-title {
        max-width: 620px;

        margin: 0;

        font-size: clamp(25px, 5vw, 41px);
        line-height: 1.06;

        font-weight: 950;
        letter-spacing: -.8px;
    }

    .hero-description {
        max-width: 550px;

        margin: 12px 0 0;

        color: rgba(255, 255, 255, .9);

        font-size: 14px;
        line-height: 1.55;
    }

    .hero-button {
        min-height: 40px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        margin-top: 18px;
        padding: 0 17px;

        border-radius: 999px;

        background: #ffffff;
        color: var(--nexo-primary);

        font-size: 13px;
        font-weight: 900;

        transition:
            transform .2s ease,
            box-shadow .2s ease;
    }

    .hero-button:hover {
        transform: translateY(-1px);

        box-shadow:
            0 6px 16px rgba(0, 0, 0, .08);
    }

    /* =========================================================
       BENEFICIOS
    ========================================================== */

    .market-benefits {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 8px;

        margin-top: 12px;
    }

    .benefit-card {
        min-width: 0;

        display: flex;
        align-items: center;

        gap: 9px;

        padding: 11px;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 14px;
    }

    .benefit-icon {
        width: 35px;
        height: 35px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 10px;

        background: var(--nexo-primary-soft);

        font-size: 17px;
    }

    .benefit-text {
        min-width: 0;
    }

    .benefit-title {
        font-size: 12px;
        font-weight: 900;
    }

    .benefit-subtitle {
        margin-top: 1px;

        overflow: hidden;

        color: var(--nexo-muted);

        font-size: 10px;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* =========================================================
       PRODUCTOS
    ========================================================== */

    .products-section {
        margin-top: 27px;
    }

    .section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 14px;
    }

    .section-title {
        margin: 0;

        font-size: 20px;
        font-weight: 950;
        letter-spacing: -.3px;
    }

    .section-description {
        margin: 3px 0 0;

        color: var(--nexo-muted);

        font-size: 12px;
    }

    .section-link {
        flex-shrink: 0;

        color: var(--nexo-primary);

        font-size: 12px;
        font-weight: 900;
    }

    .section-link:hover {
        text-decoration: underline;
    }

    .products-grid {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 10px;
    }

    /* =========================================================
       PRODUCT CARD
    ========================================================== */

    .product-card {
        min-width: 0;

        display: flex;
        flex-direction: column;

        overflow: hidden;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 16px;

        box-shadow:
            0 4px 14px rgba(28, 25, 23, .045);

        transition:
            transform .2s ease,
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .product-image-link {
        display: block;

        color: inherit;
    }

    .product-image {
        position: relative;

        aspect-ratio: 1 / .88;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        background:
            linear-gradient(
                145deg,
                #FFF6F1,
                #FFF0EA
            );
    }

    .product-image img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;

        transition: transform .25s ease;
    }

    .product-card:hover .product-image img {
        transform: scale(1.025);
    }

    .product-placeholder {
        font-size: 43px;
    }

    .product-code {
        position: absolute;

        left: 7px;
        bottom: 7px;

        max-width: calc(100% - 14px);

        padding: 4px 7px;

        overflow: hidden;

        border-radius: 999px;

        background: rgba(255, 255, 255, .94);
        color: #746C66;

        font-size: 8px;
        font-weight: 800;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-body {
        flex: 1;

        display: flex;
        flex-direction: column;

        padding: 11px;
    }

    .product-name {
        min-height: 34px;

        margin: 0;

        display: -webkit-box;
        overflow: hidden;

        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;

        color: var(--nexo-text);

        font-size: 13px;
        line-height: 1.3;
        font-weight: 900;
    }

    .product-name a {
        color: inherit;
    }

    .product-name a:hover {
        color: var(--nexo-primary);
    }

    .product-shop {
        margin-top: 5px;

        overflow: hidden;

        color: var(--nexo-muted);

        font-size: 10px;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-shop a {
        color: inherit;
        font-weight: 700;
    }

    .product-shop a:hover {
        color: var(--nexo-primary);
        text-decoration: underline;
    }

    .product-category {
        margin-top: 4px;

        overflow: hidden;

        color: #918780;

        font-size: 9px;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-price {
        margin-top: 8px;

        color: var(--nexo-primary);

        font-size: 18px;
        font-weight: 950;
    }

    .product-date {
        margin-top: 5px;

        color: #9D948E;

        font-size: 9px;
    }

    .product-button {
        width: 100%;
        min-height: 36px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-top: 11px;
        padding: 0 10px;

        border: 0;
        border-radius: 999px;

        background: var(--nexo-primary);
        color: #ffffff;

        cursor: pointer;

        font-size: 11px;
        font-weight: 900;

        transition:
            background .2s ease,
            transform .2s ease;
    }

    .product-button:hover {
        background: var(--nexo-primary-dark);

        transform: translateY(-1px);
    }

    /* =========================================================
       SIN RESULTADOS
    ========================================================== */

    .empty-products {
        padding: 44px 20px;

        text-align: center;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 18px;
    }

    .empty-products-icon {
        font-size: 40px;
    }

    .empty-products h3 {
        margin: 10px 0 4px;

        font-size: 17px;
    }

    .empty-products p {
        margin: 0;

        color: var(--nexo-muted);

        font-size: 13px;
    }

    .empty-products a {
        display: inline-flex;

        margin-top: 15px;
        padding: 9px 16px;

        border-radius: 999px;

        background: var(--nexo-primary);
        color: #ffffff;

        font-size: 12px;
        font-weight: 900;
    }

    /* =========================================================
       PAGINACIÓN
    ========================================================== */

    .market-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;

        gap: 8px;

        margin-top: 24px;
    }

    .pagination-button {
        min-height: 38px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0 14px;

        border: 1px solid var(--nexo-border);
        border-radius: 999px;

        background: #ffffff;
        color: var(--nexo-text);

        font-size: 12px;
        font-weight: 800;
    }

    .pagination-button:hover {
        border-color: var(--nexo-primary);
        color: var(--nexo-primary);
    }

    .pagination-button.disabled {
        opacity: .45;
        pointer-events: none;
    }

    .pagination-page {
        color: var(--nexo-muted);

        font-size: 11px;
        font-weight: 700;
    }

    /* =========================================================
       TABLET
    ========================================================== */

    @media (min-width: 700px) {
        .market-page {
            padding-top: 24px;
        }

        .market-benefits {
            grid-template-columns:
                repeat(4, 1fr);
        }

        .products-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 15px;
        }

        .product-card {
            border-radius: 18px;
        }

        .product-body {
            padding: 14px;
        }

        .product-name {
            min-height: 40px;

            font-size: 15px;
        }

        .product-shop {
            font-size: 12px;
        }

        .product-category,
        .product-date {
            font-size: 10px;
        }

        .product-price {
            font-size: 21px;
        }

        .product-button {
            min-height: 40px;

            font-size: 12px;
        }
    }

    /* =========================================================
       DESKTOP
    ========================================================== */

    @media (min-width: 1000px) {
        .mobile-categories {
            display: none;
        }

        .market-layout {
            grid-template-columns:
                220px minmax(0, 1fr);

            gap: 22px;
        }

        .market-sidebar {
            display: block;
        }

        .market-hero {
            min-height: 255px;

            padding: 34px 38px;
        }

        .hero-description {
            font-size: 15px;
        }

        .market-benefits {
            gap: 10px;
        }

        .benefit-card {
            padding: 12px 13px;
        }

        .benefit-title {
            font-size: 13px;
        }

        .benefit-subtitle {
            font-size: 11px;
        }

        .section-title {
            font-size: 24px;
        }

        .section-description {
            font-size: 13px;
        }

        .products-grid {
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
        }

        .product-card:hover {
            transform: translateY(-3px);

            border-color:
                rgba(232, 93, 47, .55);

            box-shadow:
                var(--nexo-shadow-hover);
        }
    }
</style>

@endpush


@section('content')

<div class="market-page">

    <div class="nexo-container">

        {{-- CATEGORÍAS MÓVILES --}}

        <nav
            class="mobile-categories"
            aria-label="Categorías"
        >

            <a
                href="{{ route('marketplace.index', [
                    'q' => $busqueda
                ]) }}"
                class="
                    mobile-category
                    {{ $categoriaSeleccionada === null ? 'active' : '' }}
                "
            >
                🛍️ Todos
            </a>

            @foreach ($categorias as $categoria)

                <a
                    href="{{ route('marketplace.index', [
                        'categoria' => $categoria->id_categoria,
                        'q' => $busqueda
                    ]) }}"
                    class="
                        mobile-category
                        {{
                            $categoriaSeleccionada === (int) $categoria->id_categoria
                                ? 'active'
                                : ''
                        }}
                    "
                >
                    {{ $categoria->nombre_categoria }}
                </a>

            @endforeach

        </nav>


        <div class="market-layout">

            {{-- SIDEBAR --}}

            <aside class="market-sidebar">

                <div class="sidebar-card">

                    <div class="sidebar-heading">
                        Categorías
                    </div>

                    <nav
                        class="sidebar-nav"
                        aria-label="Categorías"
                    >

                        <a
                            href="{{ route('marketplace.index', [
                                'q' => $busqueda
                            ]) }}"
                            class="
                                sidebar-link
                                {{
                                    $categoriaSeleccionada === null
                                        ? 'active'
                                        : ''
                                }}
                            "
                        >
                            🛍️ Todos los productos
                        </a>

                        @foreach ($categorias as $categoria)

                            <a
                                href="{{ route('marketplace.index', [
                                    'categoria' => $categoria->id_categoria,
                                    'q' => $busqueda
                                ]) }}"
                                class="
                                    sidebar-link
                                    {{
                                        $categoriaSeleccionada ===
                                        (int) $categoria->id_categoria
                                            ? 'active'
                                            : ''
                                    }}
                                "
                            >
                                📦 {{ $categoria->nombre_categoria }}
                            </a>

                        @endforeach

                    </nav>

                </div>

            </aside>


            <main class="market-content">

                {{-- HERO --}}

                <section class="market-hero">

                    <div class="hero-content">

                        <span class="hero-tag">
                            📍 Marketplace local
                        </span>

                        <h1 class="hero-title">
                            Productos de emprendedores cerca de ti
                        </h1>

                        <p class="hero-description">
                            Descubre productos locales, compara opciones
                            y conecta directamente con emprendedores de
                            tu comunidad.
                        </p>

                        <a
                            href="#productos"
                            class="hero-button"
                        >
                            Ver productos
                        </a>

                    </div>

                </section>


                {{-- BENEFICIOS --}}

                <section
                    class="market-benefits"
                    aria-label="Beneficios de NexoEco"
                >

                    <div class="benefit-card">
                        <div class="benefit-icon">📍</div>

                        <div class="benefit-text">
                            <div class="benefit-title">
                                Comercio local
                            </div>

                            <div class="benefit-subtitle">
                                Compra cerca de ti
                            </div>
                        </div>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🏪</div>

                        <div class="benefit-text">
                            <div class="benefit-title">
                                Emprendedores
                            </div>

                            <div class="benefit-subtitle">
                                Negocios de la comunidad
                            </div>
                        </div>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">🤝</div>

                        <div class="benefit-text">
                            <div class="benefit-title">
                                Compra directa
                            </div>

                            <div class="benefit-subtitle">
                                Acuerda tu entrega
                            </div>
                        </div>
                    </div>

                    <div class="benefit-card">
                        <div class="benefit-icon">⭐</div>

                        <div class="benefit-text">
                            <div class="benefit-title">
                                Comunidad
                            </div>

                            <div class="benefit-subtitle">
                                Apoya negocios locales
                            </div>
                        </div>
                    </div>

                </section>


                {{-- PRODUCTOS --}}

                <section
                    id="productos"
                    class="products-section"
                >

                    <div class="section-header">

                        <div>

                            <h2 class="section-title">

                                @if ($busqueda)

                                    Resultados de búsqueda

                                @elseif ($categoriaSeleccionada)

                                    Productos de la categoría

                                @else

                                    Productos recientes

                                @endif

                            </h2>

                            <p class="section-description">

                                @if ($busqueda)

                                    Resultados para
                                    “{{ $busqueda }}”

                                @else

                                    {{ $productos->total() }}
                                    productos encontrados

                                @endif

                            </p>

                        </div>

                        @if ($busqueda || $categoriaSeleccionada)

                            <a
                                href="{{ route('marketplace.index') }}"
                                class="section-link"
                            >
                                Limpiar filtros
                            </a>

                        @endif

                    </div>


                    @if ($productos->count() > 0)

                        <div class="products-grid">

                            @foreach ($productos as $producto)

                                @php
                                    $rutaImagen =
                                        $producto->imagenPrincipal?->imagen_url
                                        ?: $producto->imagen_url;

                                    $imagenSrc = null;

                                    if ($rutaImagen) {
                                        if (
                                            \Illuminate\Support\Str::startsWith(
                                                $rutaImagen,
                                                ['http://', 'https://']
                                            )
                                        ) {
                                            $imagenSrc = $rutaImagen;
                                        } elseif (
                                            \Illuminate\Support\Str::startsWith(
                                                $rutaImagen,
                                                '/'
                                            )
                                        ) {
                                            $imagenSrc = asset(
                                                ltrim($rutaImagen, '/')
                                            );
                                        } elseif (
                                            \Illuminate\Support\Str::startsWith(
                                                $rutaImagen,
                                                'storage/'
                                            )
                                        ) {
                                            $imagenSrc = asset($rutaImagen);
                                        } else {
                                            $imagenSrc = asset(
                                                'storage/' .
                                                ltrim($rutaImagen, '/')
                                            );
                                        }
                                    }
                                @endphp


                                <article class="product-card">

                                    {{-- IMAGEN --}}

                                    <a
                                        href="{{ route(
                                            'productos.show',
                                            $producto
                                        ) }}"
                                        class="product-image-link"
                                        aria-label="Ver {{ $producto->nombre_producto }}"
                                    >

                                        <div class="product-image">

                                            @if ($imagenSrc)

                                                <img
                                                    src="{{ $imagenSrc }}"
                                                    alt="{{ $producto->nombre_producto }}"
                                                    loading="lazy"
                                                >

                                            @else

                                                <span
                                                    class="product-placeholder"
                                                    aria-hidden="true"
                                                >
                                                    📦
                                                </span>

                                            @endif

                                            @if ($producto->codigo_producto)

                                                <span class="product-code">
                                                    {{ $producto->codigo_producto }}
                                                </span>

                                            @endif

                                        </div>

                                    </a>


                                    {{-- INFORMACIÓN --}}

                                    <div class="product-body">

                                        <h3 class="product-name">

                                            <a
                                                href="{{ route(
                                                    'productos.show',
                                                    $producto
                                                ) }}"
                                            >
                                                {{ $producto->nombre_producto }}
                                            </a>

                                        </h3>


                                        <div class="product-shop">

                                            @if ($producto->tienda)

                                                🏪

                                                <a
                                                    href="{{ route(
                                                        'tiendas.show',
                                                        $producto->tienda
                                                    ) }}"
                                                >
                                                    {{ $producto->tienda->nombre_tienda }}
                                                </a>

                                            @else

                                                🏪 Tienda NexoEco

                                            @endif

                                        </div>


                                        <div class="product-category">

                                            📦

                                            {{
                                                $producto
                                                    ->categoria
                                                    ?->nombre_categoria
                                                ?? 'Sin categoría'
                                            }}

                                        </div>


                                        <div class="product-price">

                                            ${{ number_format(
                                                (float) $producto->precio,
                                                2
                                            ) }}

                                        </div>


                                        @if ($producto->fecha_publicacion)

                                            <div class="product-date">

                                                Publicado

                                                {{
                                                    $producto
                                                        ->fecha_publicacion
                                                        ->format('d/m/Y')
                                                }}

                                            </div>

                                        @endif


                                        <a
                                            href="{{ route(
                                                'productos.show',
                                                $producto
                                            ) }}"
                                            class="product-button"
                                        >
                                            Ver producto
                                        </a>

                                    </div>

                                </article>

                            @endforeach

                        </div>


                        {{-- PAGINACIÓN --}}

                        @if ($productos->hasPages())

                            <nav
                                class="market-pagination"
                                aria-label="Paginación de productos"
                            >

                                @if ($productos->onFirstPage())

                                    <span
                                        class="
                                            pagination-button
                                            disabled
                                        "
                                    >
                                        ← Anterior
                                    </span>

                                @else

                                    <a
                                        href="{{ $productos->previousPageUrl() }}"
                                        class="pagination-button"
                                    >
                                        ← Anterior
                                    </a>

                                @endif


                                <span class="pagination-page">

                                    Página
                                    {{ $productos->currentPage() }}
                                    de
                                    {{ $productos->lastPage() }}

                                </span>


                                @if ($productos->hasMorePages())

                                    <a
                                        href="{{ $productos->nextPageUrl() }}"
                                        class="pagination-button"
                                    >
                                        Siguiente →
                                    </a>

                                @else

                                    <span
                                        class="
                                            pagination-button
                                            disabled
                                        "
                                    >
                                        Siguiente →
                                    </span>

                                @endif

                            </nav>

                        @endif


                    @else

                        <div class="empty-products">

                            <div class="empty-products-icon">
                                🔎
                            </div>

                            <h3>
                                No encontramos productos
                            </h3>

                            <p>
                                Prueba con otra búsqueda o selecciona
                                una categoría diferente.
                            </p>

                            <a href="{{ route('marketplace.index') }}">
                                Ver todos los productos
                            </a>

                        </div>

                    @endif

                </section>

            </main>

        </div>

    </div>

</div>

@endsection