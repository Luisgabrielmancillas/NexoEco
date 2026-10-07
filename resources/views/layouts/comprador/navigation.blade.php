<nav class="buyer-nav market-side-nav" aria-label="{{ auth()->check() ? 'Navegación del comprador' : 'Navegación del marketplace' }}" data-buyer-nav>
    <div class="nexo-container buyer-nav-inner">
        <div class="buyer-nav-links" id="buyer-navigation-links">
        <div class="market-side-brand"><a href="{{ route(auth()->check() ? 'comprador.dashboard' : 'marketplace.index') }}" class="market-brand"><span class="market-brand-dot"></span>NexoEco</a><button type="button" data-mobile-nav-close aria-label="Cerrar menú"><x-market-icon name="close"/></button></div>
        <span class="market-side-label">Tu comunidad, cerca</span>
        <a href="{{ route(auth()->check() ? 'comprador.dashboard' : 'marketplace.index') }}" class="buyer-nav-link {{ request()->routeIs('comprador.dashboard', 'marketplace.index') ? 'active' : '' }}" @if(request()->routeIs('comprador.dashboard', 'marketplace.index')) aria-current="page" @endif><x-market-icon name="home"/>Inicio</a>
        @auth
        <details data-nav-dropdown>
            <summary class="{{ request()->routeIs('comprador.favoritos*') ? 'active' : '' }}"><x-market-icon name="heart"/>Favoritos<x-market-icon name="chevron-down" class="nav-dropdown-arrow"/></summary>
            <div class="buyer-dropdown">
                <a href="{{ route('comprador.favoritos', 'tiendas') }}">Tiendas favoritas</a>
                <a href="{{ route('comprador.favoritos', 'productos') }}">Productos favoritos</a>
            </div>
        </details>
        <a href="{{ route('comprador.opiniones') }}" class="buyer-nav-link {{ request()->routeIs('comprador.opiniones*') ? 'active' : '' }}"><x-market-icon name="star"/>Mis opiniones</a>
        <details data-nav-dropdown>
            <summary class="{{ request()->routeIs('comprador.soporte*') ? 'active' : '' }}"><x-market-icon name="help"/>Soporte<x-market-icon name="chevron-down" class="nav-dropdown-arrow"/></summary>
            <div class="buyer-dropdown">
                <a href="{{ route('comprador.soporte', 'preguntas-frecuentes') }}">Preguntas frecuentes</a>
                <a href="{{ route('comprador.soporte', 'ayuda') }}">Ayuda</a>
                <a href="{{ route('comprador.soporte', 'contacto') }}">Contacto</a>
            </div>
        </details>
        <div class="buyer-nav-account-actions">
        @if(auth()->user()->tieneTipo('vendedor'))
            <a href="{{ route('vendedor.dashboard') }}" class="buyer-nav-link buyer-store-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 10h18l-2-6H5l-2 6Zm2 0v10h14V10M9 20v-6h6v6" stroke-linecap="round" stroke-linejoin="round"/></svg>Mi tienda</a>
        @endif
        <form method="POST" action="{{ route('logout') }}" class="buyer-nav-logout">
            @csrf
            <button type="submit" class="buyer-nav-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M9 4H5a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h4M14 8l4 4-4 4M8 12h10" stroke-linecap="round" stroke-linejoin="round"/></svg>Cerrar sesión</button>
        </form>
        </div>
        @else
        <a href="{{ route('marketplace.index') }}#tiendas" class="buyer-nav-link"><x-market-icon name="store"/>Explorar tiendas</a>
        <a href="{{ route('marketplace.index') }}#productos" class="buyer-nav-link"><x-market-icon name="bags"/>Ver productos</a>
        <a href="{{ route('vendedor.register') }}" class="buyer-nav-link buyer-store-link"><x-market-icon name="store"/>Vende con nosotros</a>
        <div class="market-side-guest"><p>Descubre negocios de tu comunidad y guarda tus favoritos.</p><a href="{{ route('login') }}" class="buyer-button">Iniciar sesión</a><a href="{{ route('register') }}" class="buyer-text-link">Crear cuenta</a></div>
        @endauth
        </div>
        <button type="button" class="market-nav-backdrop" data-mobile-nav-close aria-label="Cerrar menú" tabindex="-1"></button>
    </div>
</nav>
