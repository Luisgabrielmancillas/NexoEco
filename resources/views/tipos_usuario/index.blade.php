@extends('layouts.admin-panel')
@section('title', 'Tipos de cuenta | Administración NexoEco')
@section('admin-content')
<div class="seller-heading"><div><h1>Tipos de cuenta</h1><p>Los permisos de los paneles dependen de los nombres de los roles. Revisa las cuentas asignadas antes de cambiarlos.</p></div><div class="admin-actions"><a class="admin-action-button" href="{{ route('admin.users.index') }}">Volver a usuarios</a><a class="seller-button" href="{{ route('tipos-usuario.create') }}">Crear tipo de cuenta</a></div></div>
<div class="growth-table-wrap" tabindex="0" role="region" aria-label="Tipos de cuenta registrados">
<table class="growth-table"><caption class="sr-only">Tipos de cuenta y acciones disponibles</caption><thead><tr><th scope="col">Nombre</th><th scope="col">Cuentas asignadas</th><th scope="col">Acciones</th></tr></thead><tbody>
@forelse($tipos as $tipo)<tr><th scope="row">{{ $tipo->nombre_tipo }}</th><td>{{ $tipo->users_count }}</td><td><div class="admin-actions"><a class="admin-action-button" href="{{ route('tipos-usuario.show', $tipo) }}">Ver {{ $tipo->nombre_tipo }}</a><a class="admin-action-button" href="{{ route('tipos-usuario.edit', $tipo) }}">Editar</a></div></td></tr>
@empty<tr><td colspan="3">Todavía no hay tipos de cuenta registrados.</td></tr>@endforelse
</tbody></table></div>
<div class="seller-pagination">{{ $tipos->links() }}</div>
@endsection
