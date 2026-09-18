@extends('layouts.marketplace')

@section(
    'title',
    $producto->nombre_producto . ' - NexoEco'
)

@push('styles')

<style>
    /* =========================================================
       PÁGINA PRODUCTO
    ========================================================== */

    .product-detail-page {
        padding: 16px 0 0;
    }

    /* =========================================================
       BREADCRUMB
    ========================================================== */

    .product-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;

        gap: 6px;

        margin-bottom: 14px;

        color: var(--nexo-muted);

        font-size: 12px;
    }

    .product-breadcrumb a {
        color: inherit;

        font-weight: 700;

        transition: color .2s ease;
    }

    .product-breadcrumb a:hover {
        color: var(--nexo-primary);
    }

    .breadcrumb-separator {
        opacity: .5;
    }

    /* =========================================================
       TARJETA PRINCIPAL
    ========================================================== */

    .product-detail-card {
        overflow: hidden;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 22px;

        box-shadow:
            0 8px 28px rgba(28, 25, 23, .06);
    }

    .product-detail-grid {
        display: grid;
        grid-template-columns: 1fr;
    }

    /* =========================================================
       GALERÍA
    ========================================================== */

    .product-gallery {
        min-width: 0;

        padding: 12px;

        background: #FBF8F4;

        border-bottom:
            1px solid var(--nexo-border);
    }

    .product-main-image {
        position: relative;

        width: 100%;
        aspect-ratio: 1 / .9;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border-radius: 17px;

        background:
            linear-gradient(
                145deg,
                #FFF6F1,
                #FFF0EA
            );
    }

    .product-main-image img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: contain;
    }

    .product-main-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;

        gap: 8px;

        color: var(--nexo-muted);

        text-align: center;
    }

    .product-main-placeholder-icon {
        font-size: 65px;
    }

    .product-main-placeholder-text {
        font-size: 12px;
        font-weight: 700;
    }

    /* =========================================================
       MINIATURAS
    ========================================================== */

    .product-thumbnails {
        display: flex;

        gap: 8px;

        margin-top: 9px;
        padding-bottom: 2px;

        overflow-x: auto;

        scrollbar-width: none;
    }

    .product-thumbnails::-webkit-scrollbar {
        display: none;
    }

    .product-thumbnail {
        width: 62px;
        height: 62px;

        flex: 0 0 62px;

        padding: 3px;

        overflow: hidden;

        border: 2px solid transparent;
        border-radius: 11px;

        background: #ffffff;

        cursor: pointer;

        transition:
            border-color .2s ease,
            transform .2s ease;
    }

    .product-thumbnail img {
        width: 100%;
        height: 100%;

        display: block;

        border-radius: 7px;

        object-fit: cover;
    }

    .product-thumbnail.active {
        border-color: var(--nexo-primary);
    }

    .product-thumbnail:hover {
        transform: translateY(-1px);
    }

    /* =========================================================
       INFORMACIÓN
    ========================================================== */

    .product-info {
        min-width: 0;

        padding: 18px 16px 20px;
    }

    .product-category-badge {
        display: inline-flex;
        align-items: center;

        padding: 5px 9px;

        border-radius: 999px;

        background:
            var(--nexo-primary-soft);

        color:
            var(--nexo-primary);

        font-size: 10px;
        font-weight: 900;
    }

    .product-detail-title {
        margin: 10px 0 0;

        color: var(--nexo-text);

        font-size:
            clamp(23px, 6vw, 34px);

        line-height: 1.08;

        font-weight: 950;
        letter-spacing: -.6px;
    }

    .product-detail-code {
        margin-top: 7px;

        color: var(--nexo-muted);

        font-size: 11px;
    }

    .product-detail-price {
        margin-top: 17px;

        color: var(--nexo-primary);

        font-size: 29px;
        line-height: 1;

        font-weight: 950;
        letter-spacing: -.6px;
    }

    /* =========================================================
       TIENDA
    ========================================================== */

    .seller-card {
        display: flex;
        align-items: center;

        gap: 11px;

        margin-top: 18px;
        padding: 12px;

        border: 1px solid var(--nexo-border);
        border-radius: 14px;

        background: #ffffff;
        color: var(--nexo-text);

        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            transform .2s ease;
    }

    .seller-card:hover {
        border-color:
            rgba(232, 93, 47, .5);

        box-shadow:
            0 5px 16px rgba(28, 25, 23, .06);

        transform: translateY(-1px);
    }

    .seller-logo {
        width: 46px;
        height: 46px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;

        border-radius: 12px;

        background:
            var(--nexo-primary-soft);

        font-size: 20px;
    }

    .seller-logo img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .seller-information {
        min-width: 0;
        flex: 1;
    }

    .seller-label {
        color: var(--nexo-muted);

        font-size: 9px;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .seller-name {
        margin-top: 2px;

        overflow: hidden;

        color: var(--nexo-text);

        font-size: 14px;
        font-weight: 900;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .seller-action {
        margin-top: 3px;

        color: var(--nexo-primary);

        font-size: 10px;
        font-weight: 900;
    }

    .seller-arrow {
        flex-shrink: 0;

        color: var(--nexo-primary);

        font-size: 18px;
        font-weight: 900;
    }

    /* =========================================================
       DESCRIPCIÓN
    ========================================================== */

    .product-description {
        margin-top: 20px;
        padding-top: 18px;

        border-top:
            1px solid var(--nexo-border);
    }

    .product-section-title {
        margin: 0;

        color: var(--nexo-text);

        font-size: 15px;
        font-weight: 900;
    }

    .product-description-text {
        margin: 8px 0 0;

        color: #625B56;

        font-size: 13px;
        line-height: 1.65;

        white-space: pre-line;
    }

    /* =========================================================
       COMPRA
    ========================================================== */

    .purchase-panel {
        margin-top: 20px;
        padding: 14px;

        border: 1px solid #F0D8CE;
        border-radius: 16px;

        background: #FFF8F4;
    }

    .purchase-panel-title {
        display: flex;
        align-items: center;

        gap: 7px;

        font-size: 14px;
        font-weight: 900;
    }

    .purchase-panel-text {
        margin: 6px 0 0;

        color: #756A64;

        font-size: 12px;
        line-height: 1.55;
    }

    .purchase-primary {
        width: 100%;
        min-height: 45px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-top: 13px;
        padding: 0 16px;

        border: 0;
        border-radius: 999px;

        background:
            var(--nexo-primary);

        color: #ffffff;

        font-size: 13px;
        font-weight: 900;

        cursor: pointer;

        transition:
            background .2s ease,
            transform .2s ease;
    }

    .purchase-primary:hover {
        background:
            var(--nexo-primary-dark);

        transform: translateY(-1px);
    }

    .purchase-primary.disabled {
        background: #BEB6B0;

        cursor: default;
        transform: none;
    }

    .purchase-note {
        margin-top: 8px;

        color: var(--nexo-muted);

        font-size: 10px;
        line-height: 1.4;

        text-align: center;
    }

    /* =========================================================
       INFO ADICIONAL
    ========================================================== */

    .product-extra-grid {
        display: grid;
        grid-template-columns: 1fr;

        gap: 10px;

        margin-top: 14px;
    }

    .product-extra-card {
        display: flex;
        align-items: flex-start;

        gap: 10px;

        padding: 13px;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 14px;
    }

    .product-extra-icon {
        width: 35px;
        height: 35px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 10px;

        background:
            var(--nexo-primary-soft);

        font-size: 17px;
    }

    .product-extra-title {
        font-size: 12px;
        font-weight: 900;
    }

    .product-extra-text {
        margin-top: 2px;

        color: var(--nexo-muted);

        font-size: 10px;
        line-height: 1.4;
    }

    /* =========================================================
       TABLET
    ========================================================== */

    @media (min-width: 700px) {
        .product-detail-page {
            padding-top: 22px;
        }

        .product-info {
            padding: 25px;
        }

        .product-gallery {
            padding: 18px;
        }

        .product-extra-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .product-detail-price {
            font-size: 34px;
        }

        .product-description-text {
            font-size: 14px;
        }
    }

    /* =========================================================
       DESKTOP
    ========================================================== */

    @media (min-width: 950px) {
        .product-detail-grid {
            grid-template-columns:
                minmax(0, 1.12fr)
                minmax(380px, .88fr);
        }

        .product-gallery {
            padding: 22px;

            border-right:
                1px solid var(--nexo-border);

            border-bottom: 0;
        }

        .product-main-image {
            aspect-ratio: 1 / .88;
        }

        .product-info {
            padding: 30px;
        }

        .product-detail-title {
            font-size: 36px;
        }

        .product-detail-price {
            font-size: 38px;
        }

        .seller-card {
            padding: 14px;
        }

        .purchase-panel {
            padding: 16px;
        }
    }
</style>

@endpush


@section('content')

<div class="product-detail-page">

    <div class="nexo-container">

        {{-- =====================================================
            BREADCRUMB
        ====================================================== --}}

        <nav
            class="product-breadcrumb"
            aria-label="Navegación"
        >

            <a href="{{ route('marketplace.index') }}">
                Marketplace
            </a>

            <span class="breadcrumb-separator">
                ›
            </span>


            @if ($producto->categoria)

                <a
                    href="{{ route('marketplace.index', [
                        'categoria' => $producto->id_categoria
                    ]) }}"
                >
                    {{ $producto->categoria->nombre_categoria }}
                </a>

                <span class="breadcrumb-separator">
                    ›
                </span>

            @endif


            <span>
                {{ $producto->nombre_producto }}
            </span>

        </nav>


        {{-- =====================================================
            PRODUCTO
        ====================================================== --}}

        <article class="product-detail-card">

            <div class="product-detail-grid">


                {{-- =================================================
                    GALERÍA
                ================================================== --}}

                <section class="product-gallery">

                    <div class="product-main-image">

                        @if ($imagenes->isNotEmpty())

                            <img
                                id="producto-imagen-principal"
                                src="{{ $imagenes->first()['url'] }}"
                                alt="{{ $imagenes->first()['alt'] }}"
                            >

                        @else

                            <div class="product-main-placeholder">

                                <div class="product-main-placeholder-icon">
                                    📦
                                </div>

                                <div class="product-main-placeholder-text">
                                    Producto sin imagen
                                </div>

                            </div>

                        @endif

                    </div>


                    @if ($imagenes->count() > 1)

                        <div
                            class="product-thumbnails"
                            aria-label="Galería del producto"
                        >

                            @foreach ($imagenes as $index => $imagen)

                                <button
                                    type="button"
                                    class="
                                        product-thumbnail
                                        {{ $index === 0 ? 'active' : '' }}
                                    "
                                    data-product-image="{{ $imagen['url'] }}"
                                    data-product-alt="{{ $imagen['alt'] }}"
                                    aria-label="Ver imagen {{ $index + 1 }}"
                                >

                                    <img
                                        src="{{ $imagen['url'] }}"
                                        alt=""
                                        loading="lazy"
                                    >

                                </button>

                            @endforeach

                        </div>

                    @endif

                </section>


                {{-- =================================================
                    INFORMACIÓN
                ================================================== --}}

                <section class="product-info">


                    {{-- CATEGORÍA --}}

                    @if ($producto->categoria)

                        <a
                            href="{{ route('marketplace.index', [
                                'categoria' => $producto->id_categoria
                            ]) }}"
                            class="product-category-badge"
                        >
                            {{ $producto->categoria->nombre_categoria }}
                        </a>

                    @endif


                    {{-- NOMBRE --}}

                    <h1 class="product-detail-title">
                        {{ $producto->nombre_producto }}
                    </h1>


                    {{-- CÓDIGO --}}

                    @if ($producto->codigo_producto)

                        <div class="product-detail-code">
                            Código:
                            {{ $producto->codigo_producto }}
                        </div>

                    @endif


                    {{-- PRECIO --}}

                    <div class="product-detail-price">

                        ${{ number_format(
                            (float) $producto->precio,
                            2
                        ) }}

                    </div>


                    {{-- =================================================
                        TIENDA
                    ================================================== --}}

                    @if ($producto->tienda)

                        @php
                            $logoTienda = null;

                            $rutaLogo =
                                $producto->tienda->logo_tienda;

                            if ($rutaLogo) {

                                if (
                                    \Illuminate\Support\Str::startsWith(
                                        $rutaLogo,
                                        [
                                            'http://',
                                            'https://'
                                        ]
                                    )
                                ) {

                                    $logoTienda = $rutaLogo;

                                } elseif (
                                    \Illuminate\Support\Str::startsWith(
                                        $rutaLogo,
                                        '/'
                                    )
                                ) {

                                    $logoTienda = asset(
                                        ltrim($rutaLogo, '/')
                                    );

                                } elseif (
                                    \Illuminate\Support\Str::startsWith(
                                        $rutaLogo,
                                        'storage/'
                                    )
                                ) {

                                    $logoTienda =
                                        asset($rutaLogo);

                                } else {

                                    $logoTienda = asset(
                                        'storage/' .
                                        ltrim($rutaLogo, '/')
                                    );

                                }
                            }
                        @endphp


                        <a
                            href="{{ route(
                                'tiendas.show',
                                $producto->tienda
                            ) }}"
                            class="seller-card"
                            aria-label="Visitar tienda {{ $producto->tienda->nombre_tienda }}"
                        >

                            <div class="seller-logo">

                                @if ($logoTienda)

                                    <img
                                        src="{{ $logoTienda }}"
                                        alt="Logo de {{ $producto->tienda->nombre_tienda }}"
                                    >

                                @else

                                    🏪

                                @endif

                            </div>


                            <div class="seller-information">

                                <div class="seller-label">
                                    Vendido por
                                </div>

                                <div class="seller-name">
                                    {{ $producto->tienda->nombre_tienda }}
                                </div>

                                <div class="seller-action">
                                    Ver tienda y más productos
                                </div>

                            </div>


                            <div
                                class="seller-arrow"
                                aria-hidden="true"
                            >
                                ›
                            </div>

                        </a>

                    @endif


                    {{-- =================================================
                        DESCRIPCIÓN
                    ================================================== --}}

                    <div class="product-description">

                        <h2 class="product-section-title">
                            Descripción
                        </h2>

                        <p class="product-description-text">
                            {{
                                $producto->descripcion
                                ?: 'El vendedor aún no ha agregado una descripción para este producto.'
                            }}
                        </p>

                    </div>


                    {{-- =================================================
                        COMPRA
                    ================================================== --}}

                    <div
                        id="entrega"
                        class="purchase-panel"
                    >

                        <div class="purchase-panel-title">
                            🤝 Compra local
                        </div>

                        <p class="purchase-panel-text">
                            En NexoEco el comprador y el vendedor
                            pueden acordar directamente el lugar y
                            horario para realizar la entrega.
                        </p>


                        @guest

                            <a
                                href="{{ route('login') }}"
                                class="purchase-primary"
                            >
                                Iniciar sesión para comprar
                            </a>

                            <div class="purchase-note">
                                Necesitas una cuenta para continuar
                                con la compra.
                            </div>

                        @else

                            <button
                                type="button"
                                class="
                                    purchase-primary
                                    disabled
                                "
                                disabled
                            >
                                Compra disponible próximamente
                            </button>

                            <div class="purchase-note">
                                El proceso de compra se habilitará
                                en el siguiente módulo.
                            </div>

                        @endguest

                    </div>

                </section>

            </div>

        </article>


        {{-- =====================================================
            INFORMACIÓN EXTRA
        ====================================================== --}}

        <section class="product-extra-grid">

            <div class="product-extra-card">

                <div class="product-extra-icon">
                    🏪
                </div>

                <div>

                    <div class="product-extra-title">
                        Apoya el comercio local
                    </div>

                    <div class="product-extra-text">
                        Compra directamente a emprendedores
                        que forman parte de NexoEco.
                    </div>

                </div>

            </div>


            <div class="product-extra-card">

                <div class="product-extra-icon">
                    🤝
                </div>

                <div>

                    <div class="product-extra-title">
                        Acuerda tu entrega
                    </div>

                    <div class="product-extra-text">
                        El comprador y vendedor pueden coordinar
                        un punto y horario conveniente.
                    </div>

                </div>

            </div>

        </section>

    </div>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const mainImage =
                document.getElementById(
                    'producto-imagen-principal'
                );

            if (!mainImage) {
                return;
            }


            const thumbnails =
                document.querySelectorAll(
                    '.product-thumbnail'
                );


            thumbnails.forEach(
                function (thumbnail) {

                    thumbnail.addEventListener(
                        'click',
                        function () {

                            const newImage =
                                this.dataset.productImage;

                            const newAlt =
                                this.dataset.productAlt
                                || 'Producto';


                            if (!newImage) {
                                return;
                            }


                            mainImage.src = newImage;
                            mainImage.alt = newAlt;


                            thumbnails.forEach(
                                function (item) {

                                    item.classList.remove(
                                        'active'
                                    );

                                }
                            );


                            this.classList.add(
                                'active'
                            );

                        }
                    );

                }
            );

        }
    );
</script>

@endpush