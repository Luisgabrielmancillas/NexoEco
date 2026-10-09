@extends('layouts.base')
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
    </nav>
</header>
@endsection
@section('content')
<main class="nexo-container seller-page">
    @if(session('success'))<div class="seller-alert" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="seller-alert error" role="alert"><strong>Revisa los datos del formulario</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@if($errors->has('logo') || $errors->has('portada') || $errors->has('imagen'))<p>Vuelve a seleccionar las imágenes antes de guardar.</p>@endif</div>@endif
    @yield('seller-content')
</main>
@endsection
