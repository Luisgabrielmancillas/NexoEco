@extends('layouts.admin-panel')
@section('title', 'Solicitudes de vendedor · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración · Vendedores</span><h1>Solicitudes de vendedor</h1><p class="buyer-description">Revisa los documentos antes de habilitar el rol de vendedor.</p></div>
<nav class="buyer-tabs"><a class="{{ !request('estado') ? 'active' : '' }}" href="{{ route('admin.solicitudes.index') }}">Todas</a>@foreach(['en_revision' => 'En revisión', 'requiere_correccion' => 'Correcciones', 'aprobada' => 'Aprobadas', 'rechazada' => 'Rechazadas', 'pendiente_documentos' => 'Sin documentos'] as $state => $label)<a class="{{ request('estado') === $state ? 'active' : '' }}" href="{{ route('admin.solicitudes.index', ['estado' => $state]) }}">{{ $label }}</a>@endforeach</nav>
@forelse($solicitudes as $application)<article class="buyer-panel"><div class="buyer-row"><div><h2>{{ $application->usuario->nombre_completo ?: $application->usuario->name }}</h2><p>{{ $application->usuario->email }}</p><p>{{ $application->documentos_count }} documentos · {{ str_replace('_', ' ', $application->estado) }}</p></div><a class="buyer-button secondary" href="{{ route('admin.solicitudes.show', $application) }}">Revisar solicitud</a></div></article>@empty<div class="buyer-empty"><h2>No hay solicitudes con este estado</h2><p>Los registros de vendedor enviados aparecerán aquí.</p></div>@endforelse
{{ $solicitudes->links() }}
@endsection
