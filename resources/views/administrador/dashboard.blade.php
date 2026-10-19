@extends('layouts.admin-panel')
@section('title', 'Administración · NexoEco')
@section('admin-content')
<div class="admin-heading"><span class="buyer-eyebrow">Administración de NexoEco</span><h1>Tu comunidad, en un vistazo</h1><p class="buyer-description">Usuarios, consultas, solicitudes y actividad del equipo.</p></div>
<div class="admin-stats">
    <a class="admin-stat" href="{{ route('admin.users.index') }}"><span>Usuarios registrados</span><strong>{{ $stats['usuarios'] }}</strong><small>{{ $stats['activos'] }} activos · {{ $stats['inactivos'] }} inactivos</small></a>
    <a class="admin-stat" href="{{ route('admin.consultas.index') }}"><span>Consultas por atender</span><strong>{{ $stats['consultas'] }}</strong><small>Recibidas o en proceso</small></a>
    <a class="admin-stat" href="{{ route('admin.solicitudes.index', ['estado' => 'en_revision']) }}"><span>Vendedores en revisión</span><strong>{{ $stats['solicitudes'] }}</strong><small>Documentación enviada</small></a>
    <div class="admin-stat"><span>Productos publicados</span><strong>{{ $stats['productos'] }}</strong><small>{{ $stats['tiendas'] }} tiendas en el marketplace</small></div>
</div>
<div class="admin-columns">
    <section class="buyer-panel"><h2>Registro de usuarios</h2><p>Altas de los últimos seis meses.</p>
        @php($max = max(1, $months->max('total')))
        <div class="admin-bars" role="img" aria-label="{{ $months->map(fn ($month) => $month['mes'].': '.$month['total'].' usuarios')->implode('; ') }}">
            @foreach($months as $month)<div class="admin-bar"><strong>{{ $month['total'] }}</strong><div class="admin-bar-fill" style="height:{{ round($month['total'] / $max * 160) }}px;"></div><span>{{ $month['mes'] }}</span></div>@endforeach
        </div>
    </section>
    <section class="buyer-panel"><h2>Usuarios por rol</h2><p>Abre un rol para consultar sus cuentas.</p>
        @foreach($roles as $role => $item)<div class="admin-role"><div class="buyer-row"><a class="buyer-text-link" href="{{ route('admin.users.index', ['rol' => $role]) }}">{{ $item['nombre'] }}</a><strong>{{ $item['total'] }}</strong></div><div class="admin-track"><div style="width:{{ $stats['usuarios'] ? min(100, round($item['total'] / $stats['usuarios'] * 100)) : 0 }}%;"></div></div></div>@endforeach
        <p class="buyer-muted">Una cuenta puede tener varios roles. {{ $stats['sin_verificar'] }} cuentas tienen el correo pendiente de verificar.</p>
    </section>
</div>
<section class="buyer-panel admin-activity-panel" id="acciones-recientes"><div class="buyer-row"><div><h2>Acciones recientes</h2><p>Cambios importantes del equipo · Hora de Ciudad de México.</p></div></div>
    <div class="admin-activity-list">
    @forelse($recentActivities as $activity)
        <article class="admin-activity-item">
            <span class="admin-activity-marker" aria-hidden="true"></span>
            <div class="admin-activity-body"><h3>{{ $activity->accion }}</h3><p>{{ $activity->descripcion }}</p><div class="admin-activity-author"><strong>{{ $activity->nombre_actor }}</strong><span>{{ collect($activity->roles_actor)->map(fn ($role) => $role === 'administrador' ? 'Administrador' : 'Moderador')->implode(' · ') }}</span></div></div>
            @php($fechaActividad = $activity->registrada_en->copy()->timezone('America/Mexico_City'))
            <time datetime="{{ $fechaActividad->toIso8601String() }}">{{ $fechaActividad->format('d/m/Y') }}<span>{{ $fechaActividad->format('H:i:s') }}</span></time>
        </article>
    @empty<p class="buyer-muted">Aún no hay acciones registradas. La actividad del equipo aparecerá aquí a partir de ahora.</p>@endforelse
    </div>
    {{ $recentActivities->links() }}
</section>
@endsection
