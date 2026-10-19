@extends('layouts.marketplace')
@section('title', 'Mis opiniones · NexoEco')
@section('content')
<div class="nexo-container"><main class="buyer-page">
    <span class="buyer-eyebrow">Tu experiencia cuenta</span><h1>Mis opiniones</h1>
    <p class="buyer-description">Consulta y administra las reseñas que has publicado sobre productos y tiendas.</p>
    <nav class="buyer-tabs" aria-label="Tipo de opiniones">
        <a class="{{ !$tipo ? 'active' : '' }}" href="{{ route('comprador.opiniones') }}">Todas</a>
        <a class="{{ $tipo === 'tiendas' ? 'active' : '' }}" href="{{ route('comprador.opiniones', ['tipo' => 'tiendas']) }}">Tiendas</a>
        <a class="{{ $tipo === 'productos' ? 'active' : '' }}" href="{{ route('comprador.opiniones', ['tipo' => 'productos']) }}">Productos</a>
    </nav>
    @forelse($opiniones as $opinion)
        @php
            $esProducto = (bool) $opinion->id_producto;
            $destino = $esProducto ? $opinion->producto : $opinion->tienda;
            $url = route($esProducto ? 'productos.show' : 'tiendas.show', $destino).'#opiniones';
        @endphp
        <article class="buyer-panel">
            <div class="buyer-row"><div><span class="buyer-eyebrow">{{ $esProducto ? 'Producto' : 'Tienda' }}</span><h2 style="margin-top:8px;">@if($esProducto && $destino->trashed()){{ $destino->nombre_producto }}@else<a href="{{ $url }}">{{ $esProducto ? $destino->nombre_producto : $destino->nombre_tienda }}</a>@endif</h2></div><span class="opinion-rating">{{ str_repeat('★', $opinion->calificacion) }}{{ str_repeat('☆', 5 - $opinion->calificacion) }} · {{ $opinion->calificacion }}/5</span></div>
            <p class="buyer-review-text">{{ $opinion->comentario }}</p>
            @if($esProducto && $destino->trashed())<p class="buyer-muted">Este producto fue eliminado del marketplace.</p>@endif
            <div class="buyer-row" style="margin-top:18px;"><span class="buyer-muted">Publicada el {{ $opinion->created_at->format('d/m/Y') }}</span><div class="buyer-row">@if(!$esProducto || !$destino->trashed())<a class="buyer-text-link" href="{{ $url }}">Editar mi opinión</a>@endif<form method="POST" action="{{ route('comprador.opiniones.destroy', $opinion->id) }}">@csrf @method('DELETE')<button class="buyer-text-link" type="submit">Eliminar</button></form></div></div>
        </article>
    @empty
        <div class="buyer-empty"><h2>Aún no has publicado opiniones{{ $tipo ? ' de '.$tipo : '' }}</h2><p>Abre un producto o una tienda para compartir tu experiencia con la comunidad.</p><a class="buyer-button" href="{{ route('comprador.dashboard') }}">Explorar el marketplace</a></div>
    @endforelse
    {{ $opiniones->links() }}
</main></div>
@endsection
