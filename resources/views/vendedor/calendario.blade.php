@extends('layouts.seller-panel')
@section('title', 'Calendario de apartados | NexoEco')
@section('seller-class', 'growth-workspace')
@section('seller-content')
<x-seller-growth-hero eyebrow="Cada acuerdo, en su momento" title="Calendario de apartados" description="Ten a la vista tus próximas entregas y prepara cada encuentro con tus compradores. Un negocio organizado se siente mejor." icon="calendar" tone="lavender" badge="Organización Pro">
    <x-slot:actions><a class="seller-button" href="{{ route('vendedor.apartados.index') }}">Administrar apartados <x-seller-icon name="arrow"/></a>@if($subscription?->plan === 'pro')<a class="growth-quiet-link" href="{{ route('vendedor.calendario') }}">Volver al mes actual ↗</a>@endif</x-slot:actions>
    <x-slot:meta><span><x-seller-icon name="clock"/>Hora local de Manzanillo</span><span><x-seller-icon name="shield"/>Recordatorios privados</span></x-slot:meta>
</x-seller-growth-hero>
@if($subscription?->plan !== 'pro')
<div class="seller-card growth-upgrade"><x-seller-icon name="calendar"/><h2>Organiza tus entregas con Pro</h2><p>Visualiza tus acuerdos por fecha y configura recordatorios para darles seguimiento. Todos los vendedores pueden administrar solicitudes y marcar entregas pendientes, completadas o canceladas desde Apartados recibidos.</p><a class="seller-button" href="{{ route('vendedor.suscripcion.index') }}">Conocer Pro <x-seller-icon name="arrow"/></a></div>
@else
<section class="seller-card" data-growth-calendar>
    <div class="growth-calendar-heading"><div><span class="buyer-eyebrow">Tu agenda de entregas</span><h2>{{ $month->locale('es')->translatedFormat('F Y') }}</h2><p class="seller-help">{{ $appointments->count() }} {{ $appointments->count() === 1 ? 'entrega registrada' : 'entregas registradas' }} este mes</p></div><nav class="growth-calendar-nav" aria-label="Cambiar mes"><a href="{{ route('vendedor.calendario', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}" aria-label="Mes anterior">←</a><a href="{{ route('vendedor.calendario', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}" aria-label="Mes siguiente">→</a></nav></div>
    <div class="growth-calendar">
        @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $label)<strong class="growth-calendar-label">{{ $label }}</strong>@endforeach
        @for($blank = 1; $blank < $month->isoWeekday(); $blank++)<div class="growth-calendar-blank" aria-hidden="true"></div>@endfor
        @for($day = 1; $day <= $month->daysInMonth; $day++)
            @php($date = $month->copy()->day($day))
            @php($count = $appointments->filter(fn ($a) => $a->delivery_at->copy()->timezone('America/Mexico_City')->isSameDay($date))->count())
            <a href="#calendar-deliveries" class="{{ $count ? 'has-deliveries' : '' }} {{ $date->isToday() ? 'today' : '' }}" data-calendar-day="{{ $date->toDateString() }}" data-calendar-label="{{ $date->locale('es')->translatedFormat('j \d\e F') }}" aria-label="{{ $date->format('d/m/Y') }}: {{ $count }} entregas" @if($date->isToday()) aria-current="date" @endif><span>{{ $day }}</span>@if($count)<small>{{ $count }} {{ $count === 1 ? 'entrega' : 'entregas' }}</small>@endif</a>
        @endfor
    </div>
    <div class="growth-calendar-legend"><span><i class="today-dot" aria-hidden="true"></i>Hoy</span><span><i aria-hidden="true"></i>Con entregas registradas</span><span>Selecciona un día para consultar sus acuerdos.</span></div>
</section>
<section id="calendar-deliveries" class="growth-section">
    <div class="seller-section-title"><div><span class="buyer-eyebrow">Todo listo para el siguiente encuentro</span><h2>Entregas del mes</h2><p>Consulta los detalles y abre un apartado para actualizar su seguimiento.</p></div></div>
    <div class="growth-calendar-filter" data-calendar-filter hidden><span data-calendar-filter-label role="status"></span><button type="button" data-calendar-reset>Ver todo el mes</button></div>
    <div class="growth-two-columns">
        @forelse($appointments as $appointment)
        <article class="seller-card" data-calendar-appointment="{{ $appointment->delivery_at->copy()->timezone('America/Mexico_City')->toDateString() }}">
            <div class="growth-appointment-time"><x-seller-icon name="calendar"/><time datetime="{{ $appointment->delivery_at->toIso8601String() }}">{{ $appointment->delivery_at->copy()->timezone('America/Mexico_City')->format('d/m/Y H:i') }}</time></div>
            <span class="commerce-badge" data-state="{{ $appointment->fulfillment_status }}">{{ \App\Models\Apartado::FULFILLMENT_STATES[$appointment->fulfillment_status] }}</span>
            <h3>{{ $appointment->producto_nombre }}</h3><p>{{ $appointment->buyer->name }}</p>
            <p class="seller-help">Estado del anticipo: {{ $appointment->estado }}. El seguimiento de entrega se registra por separado.</p>
            <a class="seller-text-link" href="{{ route('vendedor.apartados.index', ['apartado' => $appointment->id]) }}">Ver y editar seguimiento ↗</a>
        </article>
        @empty
        <x-seller-growth-empty data-calendar-month-empty icon="calendar" title="Tu agenda tiene espacio para nuevos acuerdos" description="No hay entregas registradas este mes. Agrega la fecha acordada desde Apartados recibidos para verla aquí."><a class="seller-button secondary small" href="{{ route('vendedor.apartados.index') }}">Ir a mis apartados <x-seller-icon name="arrow"/></a></x-seller-growth-empty>
        @endforelse
        <div data-calendar-empty hidden><x-seller-growth-empty icon="calendar" title="Un día sin entregas registradas" description="Selecciona otra fecha o vuelve a consultar todos los acuerdos del mes."/></div>
    </div>
</section>
@endif
@endsection
