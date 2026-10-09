@extends('layouts.base')
@section('body-class', auth()->user()?->tieneTipo('administrador') ? '' : 'market-workspace')
@section('site-header')
@if(auth()->user()?->tieneTipo('administrador'))
    @include('layouts.administrador.panel-header')
@else

    @php($marketHome = auth()->check() ? 'comprador.dashboard' : 'marketplace.index')

    {{-- =====================================================
        HEADER
    ====================================================== --}}

    <header class="market-header">

        <div class="nexo-container market-header-main">

            <button type="button" class="market-header-menu-toggle" data-mobile-nav-toggle aria-label="Abrir menú" aria-expanded="false" aria-controls="buyer-navigation-links"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg></button>

            {{-- LOGO --}}
            <a
                href="{{ route($marketHome) }}"
                class="market-brand"
            >
                <span class="market-brand-dot"></span>

                <span>NexoEco</span>
            </a>


            <div class="market-desktop-location">@include('marketplace.partials.location-button')</div>
            {{-- BUSCADOR DESKTOP --}}
            <form
                action="{{ route($marketHome) }}"
                method="GET"
                class="market-search"
                role="search"
            >
                @if(request('seccion'))<input type="hidden" name="seccion" value="{{ request('seccion') }}">@endif
                @if(request('categoria'))<input type="hidden" name="categoria" value="{{ request('categoria') }}">@endif

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

                    <x-notification-bell :notificaciones-sin-leer="$notificacionesSinLeer" :notificacion-reciente="$notificacionReciente" />
                    <a
                        href="{{ route('profile.edit') }}"
                        class="market-header-button market-account-button {{ request()->routeIs('profile.*') ? 'is-active' : '' }}"
                        aria-label="Mi cuenta" title="Mi cuenta"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2" stroke-linecap="round"/></svg><span class="market-account-label">Mi cuenta</span>
                    </a>

                @endguest

            </div>

        </div>


        {{-- BUSCADOR MÓVIL --}}
        <div class="nexo-container market-mobile-search">

            @include('marketplace.partials.location-button')

            <form
                action="{{ route($marketHome) }}"
                method="GET"
                class="market-search"
                role="search"
            >
                @if(request('seccion'))<input type="hidden" name="seccion" value="{{ request('seccion') }}">@endif
                @if(request('categoria'))<input type="hidden" name="categoria" value="{{ request('categoria') }}">@endif

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

    @include('marketplace.partials.location-dialog')
    @include('marketplace.partials.route-dialog')

        @hasSection('account-navigation')
            @yield('account-navigation')
        @else
            @include('layouts.comprador.navigation')
        @endif
    @if(session('status') && ! request()->routeIs('verification.*', 'vendedor.register', 'profile.*'))
        <div class="nexo-container"><div class="buyer-notice" role="status">{{ session('status') }}</div></div>
    @endif
    @include('marketplace.partials.location-awareness')


    {{-- =====================================================
        CONTENIDO
    ====================================================== --}}


@endif
@endsection
