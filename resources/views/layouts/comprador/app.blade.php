<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body
        class="font-sans antialiased"
        x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
        :class="{ 'dark': darkMode }"
        x-init="$watch('darkMode', value => localStorage.setItem('darkMode', value))"
    >
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

            @php
                $navigation = 'layouts.comprador.navigation';

                if (Auth::check()) {

                    if (Auth::user()->tieneTipo('Administrador')) {
                        $navigation = 'layouts.administrador.navigation';
                    }

                    elseif (Auth::user()->tieneTipo('Moderador')) {
                        $navigation = 'layouts.moderador.navigation';
                    }

                    elseif (Auth::user()->tieneTipo('Vendedor')) {
                        $navigation = 'layouts.vendedor.navigation';
                    }

                    elseif (Auth::user()->tieneTipo('Comprador')) {
                        $navigation = 'layouts.comprador.navigation';
                    }
                }
            @endphp

            @include($navigation)

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @yield('header')
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>

        </div>
    </body>
</html>