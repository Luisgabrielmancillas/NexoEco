@extends($user->tieneTipo('administrador') ? 'layouts.admin-panel' : 'layouts.marketplace')
@section('title', 'Mi cuenta · NexoEco')
@section($user->tieneTipo('administrador') ? 'admin-content' : 'content')
@php
    $nombre = $user->nombre_completo ?: $user->name;
    $iniciales = collect(preg_split('/\s+/u', trim($nombre)))->filter()->take(2)->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('');
@endphp
<div class="nexo-container"><main class="buyer-page account-page">
    <span class="buyer-eyebrow">{{ $user->tieneTipo('administrador') ? 'Administración · Perfil personal' : 'Tu espacio en NexoEco' }}</span>
    <h1>Mi cuenta</h1>
    <p class="buyer-description">Cuida tus datos, administra tu acceso y sigue conectando con tu comunidad.</p>
    <div class="account-layout">
        <aside>
            <div class="buyer-panel account-summary">
                <div class="account-avatar" aria-hidden="true">@if($user->profilePhotoUrl())<img src="{{ $user->profilePhotoUrl() }}" alt="">@else{{ $iniciales }}@endif</div>
                <h2>{{ $nombre }}</h2>
                <p class="account-email">{{ $user->email }}</p>
                <span class="account-pill {{ !$user->hasVerifiedEmail() ? 'pending' : '' }}">{{ $user->hasVerifiedEmail() ? 'Correo verificado' : 'Correo pendiente de verificar' }}</span>
                <nav class="account-shortcuts" aria-label="Opciones de mi cuenta">
                    <a href="#datos-cuenta">Mis datos</a>
                    <a href="#seguridad-cuenta">Seguridad</a>
                    @if($user->tieneTipo('administrador'))
                        <a href="{{ route('administrador.dashboard') }}">Panel administrativo</a>
                        <a href="{{ route('admin.consultas.index') }}">Gestionar consultas</a>
                    @else
                    <a href="{{ route('comprador.soporte', 'contacto') }}">Contactar con soporte</a>
                    @if(!$user->tieneTipo('vendedor'))
                        <a href="{{ route('vendedor.register') }}">Mi registro de vendedor</a>
                    @endif
                    @endif
                </nav>
            </div>
            @if($user->tieneTipo('administrador'))
                <div class="buyer-notice"><h2>Tu cuenta administrativa</h2>Actualiza tus datos personales y protege tu acceso al panel.<br><a class="buyer-text-link" href="{{ route('administrador.dashboard') }}">Volver a administración</a></div>
            @else
                <div class="buyer-notice"><h2>Una cuenta, toda tu comunidad</h2>Administra tus datos, tu seguridad y tus consultas de soporte.<br><a class="buyer-text-link" href="{{ route('comprador.dashboard') }}">Volver al marketplace</a></div>
            @endif
        </aside>
        <div class="account-content">
            @include('profile.partials.photo-form')
            @include('profile.partials.update-profile-information-form')
            @include('profile.partials.update-password-form')
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</main></div>
@endsection
