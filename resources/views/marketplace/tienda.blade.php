@extends('layouts.marketplace')

@section(
    'title',
    $tienda->nombre_tienda . ' - NexoEco'
)

@push('styles')

<style>
    /* =========================================================
       PÁGINA TIENDA
    ========================================================== */

    .store-page {
        padding: 17px 0 0;
    }

    /* =========================================================
       BREADCRUMB
    ========================================================== */

    .store-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: 6px;

        margin-bottom: 13px;

        color: var(--nexo-muted);

        font-size: 12px;
    }

    .store-breadcrumb a {
        font-weight: 800;
    }

    .store-breadcrumb a:hover {
        color: var(--nexo-primary);
    }

    .store-breadcrumb-separator {
        opacity: .45;
    }

    /* =========================================================
       CABECERA TIENDA
    ========================================================== */

    .store-header {
        position: relative;

        overflow: hidden;

        padding: 18px;

        background:
            linear-gradient(
                135deg,
                #FFFFFF 0%,
                #FFF9F5 100%
            );

        border: 1px solid var(--nexo-border);
        border-radius: 22px;

        box-shadow:
            0 7px 24px rgba(28, 25, 23, .05);
    }

    .store-header::after {
        content: "";

        position: absolute;

        width: 180px;
        height: 180px;

        top: -110px;
        right: -70px;

        border-radius: 50%;

        background:
            rgba(232, 93, 47, .07);

        pointer-events: none;
    }

    .store-header-main {
        position: relative;
        z-index: 2;

        display: flex;
        align-items: flex-start;

        gap: 13px;
    }

    .store-logo {
        width: 67px;
        height: 67px;

        flex: 0 0 67px;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border: 1px solid var(--nexo-border);
        border-radius: 17px;

        background: var(--nexo-primary-soft);

        font-size: 30px;

        box-shadow:
            0 4px 13px rgba(28,25,23,.05);
    }

    .store-logo img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .store-information {
        min-width: 0;
        flex: 1;
    }

    .store-label {
        color: var(--nexo-primary);

        font-size: 10px;
        font-weight: 900;

        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .store-title {
        margin: 3px 0 0;

        color: var(--nexo-text);

        font-size: clamp(22px, 6vw, 32px);
        line-height: 1.1;

        font-weight: 950;
        letter-spacing: -.5px;
    }

    .store-description {
        max-width: 720px;

        margin: 7px 0 0;

        color: #69615C;

        font-size: 12px;
        line-height: 1.55;

        white-space: pre-line;
    }

    /* =========================================================
       METADATOS
    ========================================================== */

    .store-meta {
        display: flex;
        flex-wrap: wrap;

        gap: 8px;

        margin-top: 15px;
    }

    .store-meta-item {
        display: inline-flex;
        align-items: center;

        gap: 5px;

        min-height: 31px;

        padding: 0 10px;

        border: 1px solid var(--nexo-border);
        border-radius: 999px;

        background: white;

        color: #625B56;

        font-size: 10px;
        font-weight: 800;
    }

    /* =========================================================
       PRODUCTOS
    ========================================================== */

    .store-products {
        margin-top: 25px;
    }

    .store-section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;

        gap: 12px;

        margin-bottom: 13px;
    }

    .store-section-title {
        margin: 0;

        color: var(--nexo-text);

        font-size: 20px;
        font-weight: 950;
    }

    .store-section-subtitle {
        margin: 3px 0 0;

        color: var(--nexo-muted);

        font-size: 11px;
    }

    /* =========================================================
       GRID
    ========================================================== */

    .store-products-grid {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 10px;
    }

    /* =========================================================
       TARJETA PRODUCTO
    ========================================================== */

    .store-product-card {
        min-width: 0;

        display: flex;
        flex-direction: column;

        overflow: hidden;

        background: white;

        border: 1px solid var(--nexo-border);
        border-radius: 16px;

        box-shadow:
            0 4px 14px rgba(28,25,23,.04);

        transition:
            transform .2s ease,
            border-color .2s ease,
            box-shadow .2s ease;
    }

    .store-product-image {
        position: relative;

        aspect-ratio: 1 / .88;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        background:
            linear-gradient(
                145deg,
                #FFF7F2,
                #FFF0EA
            );
    }

    .store-product-image img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;

        transition:
            transform .25s ease;
    }

    .store-product-card:hover
    .store-product-image img {
        transform: scale(1.025);
    }

    .store-product-placeholder {
        font-size: 42px;
    }

    .store-product-body {
        flex: 1;

        display: flex;
        flex-direction: column;

        padding: 11px;
    }

    .store-product-category {
        overflow: hidden;

        color: var(--nexo-primary);

        font-size: 9px;
        font-weight: 900;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .store-product-title {
        min-height: 34px;

        margin: 5px 0 0;

        display: -webkit-box;

        overflow: hidden;

        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;

        color: var(--nexo-text);

        font-size: 13px;
        line-height: 1.3;
        font-weight: 900;
    }

    .store-product-price {
        margin-top: 8px;

        color: var(--nexo-primary);

        font-size: 18px;
        font-weight: 950;
    }

    .store-product-button {
        min-height: 36px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-top: 11px;
        padding: 0 9px;

        border-radius: 999px;

        background: var(--nexo-primary);
        color: white;

        font-size: 11px;
        font-weight: 900;

        transition:
            background .2s ease,
            transform .2s ease;
    }

    .store-product-button:hover {
        background: var(--nexo-primary-dark);

        transform: translateY(-1px);
    }

    /* =========================================================
       VACÍO
    ========================================================== */

    .store-empty {
        padding: 45px 20px;

        border: 1px solid var(--nexo-border);
        border-radius: 18px;

        background: white;

        text-align: center;
    }

    .store-empty-icon {
        font-size: 40px;
    }

    .store-empty h2 {
        margin: 10px 0 4px;

        font-size: 17px;
    }

    .store-empty p {
        margin: 0;

        color: var(--nexo-muted);

        font-size: 12px;
    }

    .store-empty a {
        display: inline-flex;

        margin-top: 14px;
        padding: 9px 15px;

        border-radius: 999px;

        background: var(--nexo-primary);
        color: white;

        font-size: 11px;
        font-weight: 900;
    }

    /* =========================================================
       PAGINACIÓN
    ========================================================== */

    .store-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;

        gap: 8px;

        margin-top: 22px;
    }

    .store-pagination-button {
        min-height: 37px;

        display: inline-flex;
        align-items: center;
        justify-content: center;

        padding: 0 13px;

        border: 1px solid var(--nexo-border);
        border-radius: 999px;

        background: white;
        color: var(--nexo-text);

        font-size: 11px;
        font-weight: 800;
    }

    .store-pagination-button:hover {
        border-color: var(--nexo-primary);
        color: var(--nexo-primary);
    }

    .store-pagination-button.disabled {
        opacity: .45;
        pointer-events: none;
    }

    .store-pagination-page {
        color: var(--nexo-muted);

        font-size: 10px;
        font-weight: 800;
    }

    /* =========================================================
       TABLET
    ========================================================== */

    @media (min-width: 700px) {

        .store-page {
            padding-top: 23px;
        }

        .store-header {
            padding: 24px;
        }

        .store-logo {
            width: 82px;
            height: 82px;

            flex-basis: 82px;

            border-radius: 20px;

            font-size: 35px;
        }

        .store-description {
            font-size: 13px;
        }

        .store-products-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap: 15px;
        }

        .store-product-card {
            border-radius: 18px;
        }

        .store-product-body {
            padding: 14px;
        }

        .store-product-title {
            min-height: 40px;

            font-size: 15px;
        }

        .store-product-category {
            font-size: 10px;
        }

        .store-product-price {
            font-size: 21px;
        }

        .store-product-button {
            min-height: 40px;

            font-size: 12px;
        }
    }

    /* =========================================================
       DESKTOP
    ========================================================== */

    @media (min-width: 1000px) {

        .store-header {
            padding: 28px;
        }

        .store-logo {
            width: 95px;
            height: 95px;

            flex-basis: 95px;

            font-size: 40px;
        }

        .store-title {
            font-size: 34px;
        }

        .store-description {
            font-size: 14px;
        }

        .store-meta-item {
            font-size: 11px;
        }

        .store-section-title {
            font-size: 24px;
        }

        .store-section-subtitle {
            font-size: 13px;
        }

        .store-products-grid {
            grid-template-columns:
                repeat(4, minmax(0, 1fr));
        }

        .store-product-card:hover {
            transform: translateY(-3px);

            border-color:
                rgba(232,93,47,.55);

            box-shadow:
                var(--nexo-shadow-hover);
        }
    }
</style>

@endpush


@section('content')

<div class="store-page">

    <div class="nexo-container">

        {{-- =====================================================
            BREADCRUMB
        ====================================================== --}}

        <nav
            class="store-breadcrumb"
            aria-label="Navegación"
        >

            <a href="{{ route('marketplace.index') }}">
                Marketplace
            </a>

            <span class="store-breadcrumb-separator">
                ›
            </span>

            <span>
                {{ $tienda->nombre_tienda }}
            </span>

        </nav>


        {{-- =====================================================
            INFORMACIÓN TIENDA
        ====================================================== --}}

        <section class="store-header">

            <div class="store-header-main">

                <div class="store-logo">

                    @if ($logoTienda)

                        <img
                            src="{{ $logoTienda }}"
                            alt="Logo de {{ $tienda->nombre_tienda }}"
                        >

                    @else

                        🏪

                    @endif

                </div>


                <div class="store-information">

                    <div class="store-label">
                        Tienda NexoEco
                    </div>


                    <h1 class="store-title">
                        {{ $tienda->nombre_tienda }}
                    </h1>


                    <p class="store-description">
                        {{
                            $tienda->descripcion_tienda
                            ?: 'Este emprendimiento aún no ha agregado una descripción de su tienda.'
                        }}
                    </p>


                    <div class="store-meta">

                        <span class="store-meta-item">
                            📦
                            {{ $productos->total() }}
                            productos
                        </span>


                        @if ($tienda->fecha_creacion)

                            <span class="store-meta-item">
                                🏪 En NexoEco desde
                                {{ $tienda->fecha_creacion->format('Y') }}
                            </span>

                        @endif


                        <a
                            href="#productos"
                            class="store-meta-item"
                        >
                            Ver catálogo ↓
                        </a>

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
            PRODUCTOS
        ====================================================== --}}

        <section
            id="productos"
            class="store-products"
        >

            <div class="store-section-header">

                <div>

                    <h2 class="store-section-title">
                        Productos de la tienda
                    </h2>

                    <p class="store-section-subtitle">
                        Explora el catálogo de
                        {{ $tienda->nombre_tienda }}.
                    </p>

                </div>

            </div>


            @if ($productos->count() > 0)

                <div class="store-products-grid">

                    @foreach ($productos as $producto)

                        <article class="store-product-card">

                            <a
                                href="{{ route(
                                    'productos.show',
                                    $producto
                                ) }}"
                            >

                                <div class="store-product-image">

                                    @if ($producto->imagen_resuelta)

                                        <img
                                            src="{{ $producto->imagen_resuelta }}"
                                            alt="{{ $producto->nombre_producto }}"
                                            loading="lazy"
                                        >

                                    @else

                                        <span
                                            class="store-product-placeholder"
                                            aria-hidden="true"
                                        >
                                            📦
                                        </span>

                                    @endif

                                </div>

                            </a>


                            <div class="store-product-body">

                                <div class="store-product-category">

                                    {{
                                        $producto
                                            ->categoria
                                            ?->nombre_categoria
                                        ?? 'Sin categoría'
                                    }}

                                </div>


                                <h3 class="store-product-title">

                                    <a
                                        href="{{ route(
                                            'productos.show',
                                            $producto
                                        ) }}"
                                    >
                                        {{ $producto->nombre_producto }}
                                    </a>

                                </h3>


                                <div class="store-product-price">

                                    ${{ number_format(
                                        (float) $producto->precio,
                                        2
                                    ) }}

                                </div>


                                <a
                                    href="{{ route(
                                        'productos.show',
                                        $producto
                                    ) }}"
                                    class="store-product-button"
                                >
                                    Ver producto
                                </a>

                            </div>

                        </article>

                    @endforeach

                </div>


                @if ($productos->hasPages())

                    <nav
                        class="store-pagination"
                        aria-label="Paginación de productos"
                    >

                        @if ($productos->onFirstPage())

                            <span
                                class="
                                    store-pagination-button
                                    disabled
                                "
                            >
                                ← Anterior
                            </span>

                        @else

                            <a
                                href="{{ $productos->previousPageUrl() }}"
                                class="store-pagination-button"
                            >
                                ← Anterior
                            </a>

                        @endif


                        <span class="store-pagination-page">

                            Página
                            {{ $productos->currentPage() }}
                            de
                            {{ $productos->lastPage() }}

                        </span>


                        @if ($productos->hasMorePages())

                            <a
                                href="{{ $productos->nextPageUrl() }}"
                                class="store-pagination-button"
                            >
                                Siguiente →
                            </a>

                        @else

                            <span
                                class="
                                    store-pagination-button
                                    disabled
                                "
                            >
                                Siguiente →
                            </span>

                        @endif

                    </nav>

                @endif


            @else

                <div class="store-empty">

                    <div class="store-empty-icon">
                        📦
                    </div>

                    <h2>
                        Esta tienda todavía no tiene productos
                    </h2>

                    <p>
                        Puedes seguir explorando otros
                        emprendimientos de NexoEco.
                    </p>

                    <a href="{{ route('marketplace.index') }}">
                        Volver al marketplace
                    </a>

                </div>

            @endif

        </section>

    </div>

</div>

@endsection