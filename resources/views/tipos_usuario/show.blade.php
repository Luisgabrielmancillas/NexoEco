@extends('layouts.admin-panel')
@section('title', 'Tipo de cuenta | NexoEco')
@section('admin-content')
<section class="seller-card"><h1>{{ $tiposUsuario->nombre_tipo }}</h1><p>{{ $tiposUsuario->users_count }} cuentas tienen este tipo asignado.</p><div class="admin-actions"><a class="admin-action-button" href="{{ route('tipos-usuario.index') }}">Volver a tipos de cuenta</a><a class="admin-action-button" href="{{ route('tipos-usuario.edit', $tiposUsuario) }}">Editar tipo de cuenta</a></div>
@if($tiposUsuario->users_count)<p class="seller-help">Este tipo no se puede eliminar mientras tenga cuentas asignadas.</p>
@else<details class="mt-6"><summary>Eliminar este tipo de cuenta</summary><p>Esta acción es permanente. Confirma solo si ya no necesitas este tipo de cuenta.</p><form method="POST" action="{{ route('tipos-usuario.destroy', $tiposUsuario) }}">@csrf @method('DELETE')<button class="seller-button danger" type="submit">Confirmar eliminación de {{ $tiposUsuario->nombre_tipo }}</button></form></details>@endif
</section>
@endsection
