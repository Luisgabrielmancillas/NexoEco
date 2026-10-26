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

            background: rgb(var(--color-surface-rgb) / 0.97);
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

            background: var(--color-surface);
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
            color: var(--color-text-secondary);
        }

        .market-search button {
            align-self: stretch;

            padding: 0 20px;

            border: 0;
            cursor: pointer;

            background: var(--nexo-primary);
            color: var(--color-on-primary);

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

            background: var(--color-surface);
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
            color: var(--color-on-primary);
        }

        .market-header-button.primary:hover {
            background: var(--nexo-primary-dark);
            color: var(--color-on-primary);
        }

        .market-mobile-search {
            display: none;
            padding-bottom: 11px;
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

        }
    </style>

    @stack('styles')
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
</head>

<body class="nexo-site @yield('body-class')">
    @yield('site-header')
    <div class="site-content">
        @yield('content')
    </div>

    @stack('scripts')

</body>
</html>
