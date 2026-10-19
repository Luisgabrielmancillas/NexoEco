@extends('layouts.admin-panel')
@section('title', 'Editar usuario · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración · Usuarios</span><h1>Cuenta #{{ $user->id }}</h1><p class="buyer-description">{{ $user->tipos_usuario->pluck('nombre_tipo')->implode(', ') }} · {{ $user->activo ? 'Activa' : 'Inactiva' }}</p></div>
<section class="buyer-panel"><h2>Datos del usuario</h2><form class="buyer-form admin-form" method="POST" action="{{ route('admin.users.update', $user) }}">@csrf @method('PUT')<div class="buyer-field"><label for="name">Nombre completo</label><input id="name" name="name" value="{{ old('name', $user->nombre_completo ?: $user->name) }}" maxlength="150" required></div><div class="buyer-field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="255" required></div><p class="buyer-muted" style="margin:16px 0;">Las direcciones actualizadas por un administrador quedan verificadas.</p><button class="buyer-button" type="submit">Guardar cambios</button></form></section>
@if($user->solicitudVendedor)<div class="buyer-panel"><h2>Solicitud de vendedor</h2><p>{{ str_replace('_', ' ', $user->solicitudVendedor->estado) }}</p><a class="buyer-text-link" href="{{ route('admin.solicitudes.show', $user->solicitudVendedor) }}">Abrir documentación</a></div>@endif
<a class="buyer-text-link" href="{{ route('admin.users.index') }}">Volver a usuarios</a>
@endsection
