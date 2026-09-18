<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'NexoEco - Marketplace local')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <style>
        :root {
            --nexo-bg: #FAF7F2;
            --nexo-white: #FFFFFF;
            --nexo-text: #1C1917;
            --nexo-muted: #746C66;
            --nexo-border: #E8E0D4;
            --nexo-primary: #E85D2F;
            --nexo-primary-dark: #D94F25;
            --nexo-primary-soft: #FFF0EA;
            --nexo-blue: #2F7EE8;
            --nexo-yellow: #E9A400;

            --nexo-radius-sm: 10px;
            --nexo-radius: 16px;
            --nexo-radius-lg: 22px;

            --nexo-shadow:
                0 6px 20px rgba(28, 25, 23, 0.06);

            --nexo-shadow-hover:
                0 14px 30px rgba(232, 93, 47, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--nexo-bg);
            color: var(--nexo-text);

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        .nexo-container {
            width: min(100% - 24px, 1280px);
            margin-inline: auto;
        }

        /* =====================================================
           HEADER
        ====================================================== */

        .market-header {
            position: sticky;
            top: 0;
            z-index: 100;

            background: rgba(255, 255, 255, 0.97);
            border-bottom: 1px solid var(--nexo-border);

            backdrop-filter: blur(12px);
        }

        .market-header-main {
            min-height: 64px;

            display: flex;
            align-items: center;
            gap: 14px;
        }

        .market-brand {
            display: flex;
            align-items: center;
            gap: 8px;

            flex-shrink: 0;

            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .market-brand-dot {
            width: 11px;
            height: 11px;

            flex-shrink: 0;

            border-radius: 50%;
            background: var(--nexo-primary);
        }

        .market-search {
            flex: 1;

            display: flex;
            align-items: center;

            max-width: 680px;
            margin-inline: auto;

            background: white;
            border: 2px solid var(--nexo-primary);
            border-radius: 999px;

            overflow: hidden;
        }

        .market-search input {
            min-width: 0;
            flex: 1;

            padding: 11px 16px;

            border: 0;
            outline: 0;

            color: var(--nexo-text);
            background: transparent;
        }

        .market-search input::placeholder {
            color: #A49C95;
        }

        .market-search button {
            align-self: stretch;

            padding: 0 20px;

            border: 0;
            cursor: pointer;

            background: var(--nexo-primary);
            color: white;

            font-weight: 800;

            transition: background .2s ease;
        }

        .market-search button:hover {
            background: var(--nexo-primary-dark);
        }

        .market-actions {
            display: flex;
            align-items: center;
            gap: 8px;

            flex-shrink: 0;
        }

        .market-header-button {
            min-height: 40px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 15px;

            border: 1px solid var(--nexo-border);
            border-radius: 999px;

            background: white;
            color: var(--nexo-text);

            font-size: 14px;
            font-weight: 800;

            transition:
                border-color .2s ease,
                background .2s ease,
                color .2s ease;
        }

        .market-header-button:hover {
            border-color: var(--nexo-primary);
            color: var(--nexo-primary);
        }

        .market-header-button.primary {
            border-color: var(--nexo-primary);
            background: var(--nexo-primary);
            color: white;
        }

        .market-header-button.primary:hover {
            background: var(--nexo-primary-dark);
            color: white;
        }

        .market-mobile-search {
            display: none;
            padding-bottom: 11px;
        }

        /* =====================================================
           FOOTER
        ====================================================== */

        .market-footer {
            margin-top: 42px;

            background: white;
            border-top: 1px solid var(--nexo-border);
        }

        .market-footer-inner {
            min-height: 76px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 16px;

            color: var(--nexo-muted);
            font-size: 13px;
        }

        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 760px) {

            .nexo-container {
                width: min(100% - 20px, 1280px);
            }

            .market-header-main {
                min-height: 58px;
            }

            .market-search {
                display: none;
            }

            .market-mobile-search {
                display: block;
            }

            .market-mobile-search .market-search {
                display: flex;
                max-width: none;
                width: 100%;
            }

            .market-header-button.desktop-login {
                display: none;
            }

            .market-header-button {
                min-height: 36px;

                padding: 0 12px;

                font-size: 12px;
            }

            .market-brand {
                font-size: 18px;
            }

            .market-footer-inner {
                min-height: auto;

                flex-direction: column;

                padding-block: 22px;

                text-align: center;
            }
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- =====================================================
        HEADER
    ====================================================== --}}

    <header class="market-header">

        <div class="nexo-container market-header-main">

            {{-- LOGO --}}
            <a
                href="{{ route('marketplace.index') }}"
                class="market-brand"
            >
                <span class="market-brand-dot"></span>

                <span>NexoEco</span>
            </a>


            {{-- BUSCADOR DESKTOP --}}
            <form
                action="{{ route('marketplace.index') }}"
                method="GET"
                class="market-search"
                role="search"
            >

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    maxlength="100"
                    placeholder="Buscar productos, tiendas o categorías..."
                    aria-label="Buscar productos"
                >

                <button type="submit">
                    Buscar
                </button>

            </form>


            {{-- ACCIONES --}}
            <div class="market-actions">

                @guest

                    <a
                        href="{{ route('login') }}"
                        class="market-header-button desktop-login"
                    >
                        Iniciar sesión
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="market-header-button primary"
                    >
                        Crear cuenta
                    </a>

                @else

                    <a
                        href="{{ route('profile.edit') }}"
                        class="market-header-button"
                    >
                        👤 Mi cuenta
                    </a>

                @endguest

            </div>

        </div>


        {{-- BUSCADOR MÓVIL --}}
        <div class="nexo-container market-mobile-search">

            <form
                action="{{ route('marketplace.index') }}"
                method="GET"
                class="market-search"
                role="search"
            >

                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    maxlength="100"
                    placeholder="Buscar en NexoEco..."
                    aria-label="Buscar productos"
                >

                <button
                    type="submit"
                    aria-label="Buscar"
                >
                    🔎
                </button>

            </form>

        </div>

    </header>


    {{-- =====================================================
        CONTENIDO
    ====================================================== --}}

    @yield('content')


    {{-- =====================================================
        FOOTER
    ====================================================== --}}

    <footer class="market-footer">

        <div class="nexo-container market-footer-inner">

            <span>
                © {{ date('Y') }} NexoEco.
                Todos los derechos reservados.
            </span>

            <span>
                Marketplace local de Manzanillo
            </span>

        </div>

    </footer>

    @stack('scripts')

</body>
</html>