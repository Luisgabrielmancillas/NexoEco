@extends('layouts.base')

@push('styles')
<style>
    .nav-link-bloqueado {
        opacity: 0.5 !important;
        cursor: not-allowed !important;
    }
    .nav-link-bloqueado:hover {
        color: inherit !important;
        background-color: transparent !important;
    }
</style>
@endpush

@section('site-header')
<header class="market-header seller-header">
    <div class="nexo-container market-header-main">
        <a class="market-brand" href="{{ route('vendedor.dashboard') }}" aria-label="Resumen de mi tienda"><span class="market-brand-dot"></span>NexoEco <span class="seller-header-label">Mi tienda</span></a>
        <div class="seller-header-actions">
            <x-notification-bell :seller="true" :notificaciones-sin-leer="$notificacionesSinLeer" :notificacion-reciente="$notificacionReciente" />
            <a href="{{ route('comprador.dashboard') }}" class="seller-back-market">Volver al marketplace</a>
            <form action="{{ route('logout') }}" method="POST">@csrf<button class="market-header-button" type="submit">Cerrar sesión</button></form>
        </div>
    </div>
    <nav class="nexo-container seller-nav" aria-label="Navegación de mi tienda">
        <a href="{{ route('vendedor.dashboard') }}" class="{{ request()->routeIs('vendedor.dashboard', 'vendedor.tienda.*') ? 'active' : '' }}" @if(request()->routeIs('vendedor.dashboard', 'vendedor.tienda.*')) aria-current="page" @endif>Resumen</a>
        <a href="{{ route('vendedor.productos.index') }}" class="{{ request()->routeIs('vendedor.productos.*') ? 'active' : '' }}" @if(request()->routeIs('vendedor.productos.*')) aria-current="page" @endif>Productos</a>
        <a href="{{ route('vendedor.mensajes.index') }}" class="{{ request()->routeIs('vendedor.mensajes.*', 'chat.show') ? 'active' : '' }}" @if(request()->routeIs('vendedor.mensajes.*', 'chat.show')) aria-current="page" @endif>Mensajes</a>
        <a href="{{ route('vendedor.apartados.index') }}" class="{{ request()->routeIs('vendedor.apartados.*', 'vendedor.mercadopago.*') ? 'active' : '' }}">Apartados recibidos</a>
        <a href="{{ route('vendedor.suscripcion.index') }}" class="{{ request()->routeIs('vendedor.suscripcion.*') ? 'active' : '' }}" @if(request()->routeIs('vendedor.suscripcion.*')) aria-current="page" @endif>Planes y promoción</a>
        
        @php
            $tieneSuscripcion = auth()->user()->subscription?->entitled() ?? false;
        @endphp
        
        <a href="{{ $tieneSuscripcion ? route('vendedor.herramientas.index') : '#' }}" class="{{ request()->routeIs('vendedor.herramientas.*', 'vendedor.novedades.*', 'vendedor.calendario') ? 'active' : '' }} {{ !$tieneSuscripcion ? 'nav-link-bloqueado' : '' }}" @if(!$tieneSuscripcion) title="Solo disponible en planes de paga" onclick="event.preventDefault(); alert('Esta herramienta es exclusiva de los planes Impulso y Pro. ¡Mejora tu plan para usarla!');" @endif>Herramientas @if(!$tieneSuscripcion) <span aria-hidden="true" style="font-size: 0.8em; margin-left: 3px;">🔒</span> @endif</a>
        <a href="{{ route('vendedor.promociones.index') }}" class="{{ request()->routeIs('vendedor.promociones.*') ? 'active' : '' }}">Ofertas</a>
        <a href="{{ $tieneSuscripcion ? route('vendedor.estadisticas.index') : '#' }}" class="{{ request()->routeIs('vendedor.estadisticas.*') ? 'active' : '' }} {{ !$tieneSuscripcion ? 'nav-link-bloqueado' : '' }}" @if(!$tieneSuscripcion) title="Solo disponible en planes de paga" onclick="event.preventDefault(); alert('Esta herramienta es exclusiva de los planes Impulso y Pro. ¡Mejora tu plan para usarla!');" @endif>Estadísticas @if(!$tieneSuscripcion) <span aria-hidden="true" style="font-size: 0.8em; margin-left: 3px;">🔒</span> @endif</a>
    </nav>
</header>
@endsection
@section('content')
<main class="nexo-container seller-page @yield('seller-class')">
    @if(session('success'))<div class="seller-alert" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="seller-alert error" role="alert"><strong>Revisa los datos del formulario</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@if($errors->has('logo') || $errors->has('portada') || $errors->has('imagen'))<p>Vuelve a seleccionar las imágenes antes de guardar.</p>@endif</div>@endif
    @yield('seller-content')
</main>
@endsection
