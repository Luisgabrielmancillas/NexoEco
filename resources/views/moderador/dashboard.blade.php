@extends('layouts.moderator-panel')
@section('title', 'Resumen · Moderación NexoEco')
@section('moderator-content')
<div class="mod-section-heading">
    <div><h2>Tu jornada de moderación</h2><p>Un vistazo a los casos abiertos y a la actividad del equipo.</p></div>
    <span class="mod-today">{{ now()->locale('es')->translatedFormat('d \d\e F, Y') }}</span>
</div>
<section class="mod-stats" aria-label="Resumen de moderación">
    @foreach([
        ['Reportes abiertos', $counts['reportes'], 'flag', 'rose', 'Casos enviados por la comunidad', route('moderador.reportes')],
        ['Vendedores por verificar', $counts['vendedores'], 'users', 'orange', 'Solicitudes con documentos por revisar', route('moderador.vendedores', ['estado' => 'en_revision'])],
        ['Contenido eliminado hoy', $deletedToday, 'close', 'neutral', 'Productos y opiniones retirados', route('moderador.analiticas')],
        ['Reportes resueltos hoy', $resolvedToday, 'check', 'green', 'Casos cerrados con respuesta', route('moderador.reportes', ['estado' => 'resuelto'])],
    ] as [$label, $number, $icon, $color, $hint, $url])
        <a class="mod-stat {{ $color }}" href="{{ $url }}">
            <span class="mod-stat-icon"><x-mod-icon name="{{ $icon }}"/></span>
            <div><p>{{ $label }}</p><strong>{{ $number }}</strong><small>{{ $hint }}</small></div>
        </a>
    @endforeach
</section>

<div class="mod-section-heading"><div><h2>Casos por atender</h2><p>Abre un caso para revisar su información y darle seguimiento.</p></div></div>
<div class="mod-summary-grid">
    <section class="mod-card mod-queue" aria-labelledby="recent-reports-title">
        <div class="mod-queue-heading"><h3 id="recent-reports-title"><x-mod-icon name="flag"/>Reportes recientes</h3><a href="{{ route('moderador.reportes') }}">Ver reportes<x-mod-icon name="arrow"/></a></div>
        @forelse($recentReports as $report)
            <a class="mod-queue-item" href="{{ route('moderador.reportes') }}">
                <span class="mod-queue-icon"><x-mod-icon name="{{ $report->tipo === 'productos' ? 'file' : 'comment' }}"/></span>
                <div>
                    <strong>{{ $report->tipo === 'productos' ? 'Publicación' : 'Opinión' }} #{{ $report->id_contenido }}</strong>
                    <p>{{ \Illuminate\Support\Str::limit($report->motivo, 110) }}</p>
                    <small>{{ $report->usuario?->name ?? 'Cuenta eliminada' }} · {{ $report->created_at->format('d/m/Y H:i') }}</small>
                </div>
                <x-mod-icon name="arrow"/>
            </a>
        @empty
            <div class="mod-empty"><x-mod-icon name="check"/><h3>No hay reportes abiertos</h3><p>Los nuevos casos enviados por la comunidad aparecerán aquí.</p></div>
        @endforelse
    </section>
    <section class="mod-card mod-queue" aria-labelledby="pending-sellers-title">
        <div class="mod-queue-heading"><h3 id="pending-sellers-title"><x-mod-icon name="users"/>Documentos de vendedores</h3><a href="{{ route('moderador.vendedores', ['estado' => 'en_revision']) }}">Ver solicitudes<x-mod-icon name="arrow"/></a></div>
        @forelse($pendingSellers as $application)
            <a class="mod-queue-item" href="{{ route('moderador.solicitudes.show', $application) }}">
                <span class="mod-queue-icon"><x-mod-icon name="users"/></span>
                <div>
                    <strong>{{ $application->usuario?->nombre_completo ?: ($application->usuario?->name ?? 'Cuenta eliminada') }}</strong>
                    <p>Solicitud #{{ $application->getKey() }} · {{ $application->documentos_count }} {{ $application->documentos_count === 1 ? 'documento' : 'documentos' }}</p>
                    <small>Recibida {{ $application->fecha_solicitud?->format('d/m/Y H:i') ?? 'sin fecha registrada' }}</small>
                </div>
                <span class="mod-queue-action">Revisar<x-mod-icon name="arrow"/></span>
            </a>
        @empty
            <div class="mod-empty"><x-mod-icon name="check"/><h3>No hay documentos por revisar</h3><p>Las nuevas solicitudes de vendedor se mostrarán aquí.</p></div>
        @endforelse
    </section>
</div>

<div class="mod-section-heading"><div><h2>Acciones recientes</h2><p>Decisiones importantes del equipo, con nombre, fecha y hora.</p></div><a class="mod-button secondary" href="{{ route('moderador.analiticas') }}">Ver historial<x-mod-icon name="arrow"/></a></div>
<section class="mod-card mod-summary-activity" aria-label="Acciones recientes de moderación">
    @forelse($recentActivity as $record)
        <article class="mod-activity-item">
            <span class="mod-queue-icon"><x-mod-icon name="{{ $record->tipo_objeto === 'reporte' ? 'flag' : ($record->tipo_objeto === 'productos' ? 'file' : ($record->tipo_objeto === 'opiniones' ? 'comment' : 'users')) }}"/></span>
            <div><strong>{{ $record->accion }}</strong><p>{{ $record->descripcion }}</p><small>{{ $record->nombre_actor }}</small></div>
            <time datetime="{{ $record->registrada_en->toIso8601String() }}">{{ $record->registrada_en->format('d/m/Y H:i:s') }}</time>
        </article>
    @empty
        <div class="mod-empty"><x-mod-icon name="clock"/><h3>Aún no hay acciones de moderación</h3><p>Las revisiones de vendedores, las eliminaciones de contenido y las respuestas a reportes quedarán registradas aquí.</p></div>
    @endforelse
</section>
@endsection
