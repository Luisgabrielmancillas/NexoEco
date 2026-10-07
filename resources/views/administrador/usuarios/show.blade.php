@extends('layouts.admin-panel')
@section('title', 'Ver usuario · NexoEco')
@section('admin-content')
<div class="buyer-row admin-heading"><div><span class="buyer-eyebrow">Administración · Usuarios</span><h1>{{ $user->nombre_completo ?: $user->name }}</h1><p class="buyer-description">Información de la cuenta #{{ $user->id }}.</p></div><a class="buyer-button" href="{{ route('admin.users.edit', $user) }}">Editar cuenta</a></div>
<section class="buyer-panel"><div class="admin-user-cell"><div class="account-avatar" aria-hidden="true">@if($user->profilePhotoUrl())<img src="{{ $user->profilePhotoUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}@endif</div><div><h2>Datos del usuario</h2><div class="admin-role-badges">@foreach($user->tipos_usuario as $role)<span>{{ ucfirst($role->nombre_tipo) }}</span>@endforeach</div></div></div>
    <dl class="admin-data"><dt>Nombre</dt><dd>{{ $user->nombre_completo ?: $user->name }}</dd><dt>Correo</dt><dd>{{ $user->email }}</dd><dt>Acceso</dt><dd><span class="account-pill {{ !$user->activo ? 'pending' : '' }}">{{ $user->activo ? 'Activo' : 'Inactivo' }}</span></dd><dt>Verificación</dt><dd>{{ $user->hasVerifiedEmail() ? 'Correo verificado' : 'Correo sin verificar' }}</dd><dt>Registro</dt><dd>{{ ($user->fecha_registro ?? $user->created_at)?->timezone('America/Mexico_City')->format('d/m/Y H:i:s') }} · Ciudad de México</dd></dl>
    @if($user->id !== auth()->id())<form method="POST" action="{{ route('admin.users.destroy', $user) }}">@csrf @method('DELETE')<button class="admin-action-button {{ $user->activo ? 'danger' : 'restore' }}" type="submit">{{ $user->activo ? 'Desactivar cuenta' : 'Reactivar cuenta' }}</button></form>@endif
</section>
@if($user->solicitudVendedor)<section class="buyer-panel"><h2>Solicitud de vendedor</h2><p>{{ str_replace('_', ' ', $user->solicitudVendedor->estado) }}</p><a class="buyer-button secondary" href="{{ route('admin.solicitudes.show', $user->solicitudVendedor) }}">Ver solicitud</a></section>@endif
<a class="buyer-button secondary" href="{{ route('admin.users.index') }}">Volver a usuarios</a>
@endsection
