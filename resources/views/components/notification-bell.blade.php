@props(['seller' => false, 'notificacionesSinLeer' => 0, 'notificacionReciente' => null])
<a href="{{ route($seller ? 'vendedor.notificaciones' : 'comprador.notificaciones') }}" class="market-notifications" data-notification-bell data-count-url="{{ route('comprador.notificaciones.count', ['details' => 1]) }}" data-latest-id="{{ $notificacionReciente ?? '' }}" data-unread-count="{{ $notificacionesSinLeer }}" aria-label="Notificaciones{{ ($notificacionesSinLeer ?? 0) ? ': '.$notificacionesSinLeer.' sin leer' : '' }}" title="Notificaciones">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <span class="notification-count" aria-hidden="true" @if(($notificacionesSinLeer ?? 0) === 0) hidden @endif>{{ ($notificacionesSinLeer ?? 0) > 99 ? '99+' : ($notificacionesSinLeer ?? 0) }}</span>
</a>
