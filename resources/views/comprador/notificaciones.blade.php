@extends('layouts.marketplace')
@section('title', 'Notificaciones · NexoEco')
@section('content')
<div class="nexo-container"><main class="buyer-page">
    <div class="buyer-row"><div><span class="buyer-eyebrow">Al día con tu cuenta</span><h1>Notificaciones</h1></div>
        @if($notificacionesSinLeer)<form method="POST" action="{{ route('comprador.notificaciones.readAll') }}">@csrf<button class="buyer-button secondary" type="submit">Marcar todas como leídas</button></form>@endif
    </div>
    <p class="buyer-description">Aquí aparecen las novedades de tu cuenta, tus opiniones y tus solicitudes de soporte.</p>
    @forelse($notificaciones as $notificacion)
        @php
            $destino = $notificacion->data['url'] ?? '';
            $urlSegura = (str_starts_with($destino, '/') && !str_starts_with($destino, '//')) || str_starts_with($destino, url('/').'/');
        @endphp
        <article class="buyer-panel {{ !$notificacion->read_at ? 'buyer-unread' : '' }}">
            <div class="buyer-row"><h2>{{ $notificacion->data['titulo'] ?? 'Novedad en tu cuenta' }}</h2>@unless($notificacion->read_at)<span class="buyer-badge">Nueva</span>@endunless</div>
            <p>{{ $notificacion->data['mensaje'] ?? '' }}</p>
            <div class="buyer-row" style="margin-top:16px;"><span class="buyer-muted">{{ $notificacion->created_at->format('d/m/Y H:i') }}</span><div class="buyer-row">
                @if($urlSegura)<a class="buyer-text-link" href="{{ $destino }}">Ver detalle</a>@endif
                @unless($notificacion->read_at)<form method="POST" action="{{ route('comprador.notificaciones.read', $notificacion->id) }}">@csrf<button class="buyer-text-link" type="submit">Marcar como leída</button></form>@endunless
            </div></div>
        </article>
    @empty
        <div class="buyer-empty"><h2>No tienes notificaciones todavía</h2><p>Cuando haya actividad en tu cuenta, podrás consultarla aquí.</p><a class="buyer-button" href="{{ route('comprador.dashboard') }}">Ir al marketplace</a></div>
    @endforelse
    {{ $notificaciones->links() }}
</main></div>
@endsection
