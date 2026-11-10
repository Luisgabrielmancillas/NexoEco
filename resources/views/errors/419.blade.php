@extends('layouts.base')
@php($logoutRecovery = $logoutRecovery ?? request()->routeIs('logout'))
@section('title', 'Actualiza tu sesión | NexoEco')
@section('content')
<main class="session-recovery-page">
    <a class="market-brand" href="{{ url('/') }}" aria-label="NexoEco, volver al inicio"><span class="market-brand-dot"></span>NexoEco</a>
    <dialog class="app-message-dialog" open data-session-recovery data-exit-url="{{ url('/') }}" aria-labelledby="session-recovery-title" aria-describedby="session-recovery-description">
        <div class="app-message-icon" aria-hidden="true"><x-seller-icon name="shield"/></div>
        <h1 id="session-recovery-title">{{ $logoutRecovery ? 'Confirma el cierre de sesión' : 'Actualiza tu sesión' }}</h1>
        <p id="session-recovery-description">{{ $logoutRecovery ? 'Esta página tenía una sesión anterior. Tu cuenta sigue abierta; confirma para cerrar sesión de forma segura.' : 'La sesión de esta página venció o cambió en otra pestaña. Vuelve al inicio y abre de nuevo la sección para continuar.' }}</p>
        @if($logoutRecovery)
            <form class="app-message-actions" method="POST" action="{{ route('logout') }}">
                @csrf
                <a class="ds-secondary-button" href="{{ url('/') }}">Continuar en NexoEco</a>
                <button class="ds-primary-button" type="submit" autofocus>Cerrar sesión</button>
            </form>
        @else
            <div class="app-message-actions"><a class="ds-primary-button" href="{{ url('/') }}" autofocus>Volver al inicio</a></div>
        @endif
    </dialog>
</main>
@endsection
