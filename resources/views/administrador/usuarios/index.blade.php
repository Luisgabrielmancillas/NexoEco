@extends('layouts.admin-panel')
@section('title', 'Usuarios · NexoEco')
@section('admin-content')
<div class="buyer-row admin-heading"><div><span class="buyer-eyebrow">Administración · Comunidad</span><h1>Usuarios</h1><p class="buyer-description">Organiza las cuentas por rol y administra su acceso desde un solo lugar.</p></div><a class="buyer-button" href="{{ route('admin.users.create', ['rol' => request('rol') === 'vendedor' ? 'comprador' : request('rol')]) }}">Crear cuenta rápida</a></div>
<section class="admin-user-roles" aria-labelledby="usuarios-por-rol">
    <h2 id="usuarios-por-rol">Usuarios por rol</h2>
    <nav class="admin-role-tabs" aria-label="Roles de usuario">
        <a class="admin-role-tab {{ !request('rol') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" @if(!request('rol')) aria-current="page" @endif><span>Todos</span><strong>{{ $totalUsers }}</strong></a>
        @foreach(\App\Support\AccountRoles::LABELS as $role => $label)<a class="admin-role-tab {{ request('rol') === $role ? 'active' : '' }}" href="{{ route('admin.users.index', ['rol' => $role]) }}" @if(request('rol') === $role) aria-current="page" @endif><span>{{ $label }}</span><strong>{{ $roleCounts[$role] }}</strong></a>@endforeach
    </nav>
    <p class="buyer-muted">Una cuenta puede tener varios roles.</p>
</section>
<section class="buyer-panel admin-recent-users" aria-labelledby="ultimas-cuentas">
    <div class="buyer-row"><div><h2 id="ultimas-cuentas">Últimas cuentas registradas</h2><p>{{ request('rol') ? 'Altas recientes de '.mb_strtolower(\App\Support\AccountRoles::LABELS[request('rol')]).'.' : 'Altas recientes de todos los roles.' }}</p></div></div>
    <div class="admin-recent-grid">
    @forelse($recentUsers as $account)
        <article class="admin-recent-account">
            <div class="admin-user-cell"><div class="admin-avatar" aria-hidden="true">@if($account->profilePhotoUrl())<img src="{{ $account->profilePhotoUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}@endif</div><div><h3>{{ $account->nombre_completo ?: $account->name }}</h3><p>{{ $account->email }}</p></div></div>
            <div class="admin-role-badges">@foreach($account->tipos_usuario as $role)<span>{{ ucfirst($role->nombre_tipo) }}</span>@endforeach</div>
            <div class="admin-recent-footer"><time>{{ ($account->fecha_registro ?? $account->created_at)?->timezone('America/Mexico_City')->format('d/m/Y H:i') }}</time><a class="admin-action-button" href="{{ route('admin.users.show', $account) }}" aria-label="Ver cuenta de {{ $account->nombre_completo ?: $account->name }}">Ver</a></div>
        </article>
    @empty<p class="buyer-muted">Aún no hay cuentas registradas con este rol.</p>@endforelse
    </div>
</section>
<section aria-labelledby="lista-usuarios">
<div class="admin-user-list-heading"><h2 id="lista-usuarios">{{ \App\Support\AccountRoles::LABELS[request('rol')] ?? 'Todas las cuentas' }}</h2><span>{{ $users->total() }} {{ $users->total() === 1 ? 'cuenta encontrada' : 'cuentas encontradas' }}</span></div>
<form method="GET" class="buyer-form admin-filters">
    @if(request('rol'))<input type="hidden" name="rol" value="{{ request('rol') }}">@endif
    <div class="buyer-field"><label for="q">Buscar usuario</label><input id="q" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Nombre o correo"></div>
    <div class="buyer-field"><label for="estado">Estado</label><select id="estado" name="estado"><option value="">Todos</option><option value="activo" @selected(request('estado') === 'activo')>Activos</option><option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option></select></div>
    <button class="buyer-button" type="submit">Filtrar</button>
    @if(request('q') || request('estado'))<a class="buyer-button secondary" href="{{ route('admin.users.index', ['rol' => request('rol')]) }}">Limpiar</a>@endif
</form>
@if(request('rol') === 'vendedor')<div class="buyer-notice">Aquí aparecen vendedores habilitados. Las cuentas en revisión siguen como compradores hasta que se aprueben sus documentos. <a class="buyer-text-link" href="{{ route('admin.solicitudes.index') }}">Revisar solicitudes</a></div>@endif
<div class="admin-table-wrap"><table class="admin-table admin-users-table"><thead><tr><th>Usuario</th><th>Roles</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead><tbody>
@forelse($users as $account)
<tr>
    <td><div class="admin-user-cell"><div class="admin-avatar" aria-hidden="true">@if($account->profilePhotoUrl())<img src="{{ $account->profilePhotoUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($account->name, 0, 1)) }}@endif</div><div><strong>{{ $account->nombre_completo ?: $account->name }}</strong><p>{{ $account->email }}</p></div></div></td>
    <td data-label="Roles"><div class="admin-role-badges">@foreach($account->tipos_usuario as $role)<span>{{ ucfirst($role->nombre_tipo) }}</span>@endforeach</div>@if($account->solicitudVendedor?->estado === 'en_revision')<p>Vendedor en revisión</p>@endif</td>
    <td data-label="Estado"><span class="account-pill {{ !$account->activo ? 'pending' : '' }}">{{ $account->activo ? 'Activo' : 'Inactivo' }}</span><p>{{ $account->hasVerifiedEmail() ? 'Correo verificado' : 'Correo sin verificar' }}</p></td>
    <td data-label="Registro">{{ ($account->fecha_registro ?? $account->created_at)?->timezone('America/Mexico_City')->format('d/m/Y') }}</td>
    <td data-label="Acciones"><div class="admin-actions">
        <a class="admin-action-button" href="{{ route('admin.users.show', $account) }}" aria-label="Ver cuenta de {{ $account->nombre_completo ?: $account->name }}">Ver</a>
        <a class="admin-action-button edit" href="{{ route('admin.users.edit', $account) }}" aria-label="Editar cuenta de {{ $account->nombre_completo ?: $account->name }}">Editar</a>
        @if($account->id !== auth()->id())<form method="POST" action="{{ route('admin.users.destroy', $account) }}">@csrf @method('DELETE')<button class="admin-action-button {{ $account->activo ? 'danger' : 'restore' }}" type="submit" aria-label="{{ $account->activo ? 'Desactivar' : 'Reactivar' }} cuenta de {{ $account->nombre_completo ?: $account->name }}">{{ $account->activo ? 'Desactivar' : 'Reactivar' }}</button></form>@endif
    </div></td>
</tr>
@empty<tr class="admin-users-empty"><td colspan="5">No hay usuarios con estos filtros.</td></tr>@endforelse
</tbody></table></div>{{ $users->links() }}
</section>
@endsection