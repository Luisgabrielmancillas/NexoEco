<nav class="buyer-nav admin-nav" aria-label="Navegación administrativa" data-buyer-nav>
    <div class="nexo-container buyer-nav-inner">
        <button type="button" class="buyer-menu-toggle" data-mobile-nav-toggle aria-expanded="false" aria-controls="buyer-navigation-links"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round"/></svg><span>Menú administrativo</span></button>
        <div class="buyer-nav-links" id="buyer-navigation-links">
            <a class="buyer-nav-link {{ request()->routeIs('administrador.dashboard') ? 'active' : '' }}" href="{{ route('administrador.dashboard') }}">Resumen</a>
            <a class="buyer-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Usuarios</a>
            <a class="buyer-nav-link {{ request()->routeIs('admin.consultas.*') ? 'active' : '' }}" href="{{ route('admin.consultas.index') }}">Consultas</a>
            <a class="buyer-nav-link {{ request()->routeIs('admin.solicitudes.*') ? 'active' : '' }}" href="{{ route('admin.solicitudes.index') }}">Solicitudes de vendedor</a>
            <form class="buyer-nav-logout" method="POST" action="{{ route('logout') }}">@csrf<button class="buyer-nav-link" type="submit">Cerrar sesión</button></form>
        </div>
    </div>
</nav>
