<header class="market-header admin-header">
    <div class="nexo-container market-header-main">
        <a href="{{ route('administrador.dashboard') }}" class="market-brand" aria-label="NexoEco · Inicio de administración"><span class="market-brand-dot"></span><span>NexoEco</span></a>
        <span class="admin-header-label">Administración</span>
        <a href="{{ route('profile.edit') }}" class="market-header-button admin-account-button {{ request()->routeIs('profile.*') ? 'is-active' : '' }}">Mi perfil</a>
    </div>
</header>
@include('layouts.administrador.panel-navigation')
