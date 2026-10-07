@extends('layouts.moderator-panel')
@section('title', 'Opiniones y comentarios · Moderación NexoEco')
@section('moderator-content')
<div class="mod-section-heading"><div><h2>Opiniones y comentarios</h2><p>Consulta las reseñas y elimina los comentarios inapropiados.</p></div><span class="mod-count">{{ $counts['opiniones'] }} publicadas</span></div>
<form method="GET" class="mod-card mod-filters"><input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar comentario" aria-label="Buscar comentario" maxlength="150"><select name="estado" aria-label="Estado de la opinión"><option value="">Todos los estados</option>@foreach(['publicada'=>'Publicadas','eliminada'=>'Eliminadas'] as $value=>$label)<option value="{{ $value }}" @selected(request('estado') === $value)>{{ $label }}</option>@endforeach</select><button class="mod-button secondary">Buscar</button></form>
<div class="mod-opinion-grid">
    @forelse($opiniones as $opinion)
        <article class="mod-card mod-opinion" id="opinion-{{ $opinion->getKey() }}">
            <div class="mod-opinion-heading">
                <div>
                    <span class="mod-overline">OP-{{ $opinion->getKey() }} · {{ $opinion->id_producto ? 'Producto' : 'Tienda' }}</span>
                    <h3>{{ $opinion->producto?->nombre_producto ?? $opinion->tienda?->nombre_tienda ?? 'Contenido eliminado' }}</h3>
                </div>
                @include('moderador.partials.status', ['state'=>$opinion->trashed() ? 'eliminada' : 'publicada'])
            </div>
            <p class="mod-rating">{{ str_repeat('★', $opinion->calificacion) }}{{ str_repeat('☆',5-$opinion->calificacion) }} <span>{{ $opinion->calificacion }}/5</span></p>
            <blockquote class="mod-content-text">{{ $opinion->comentario }}</blockquote>
            <p class="mod-muted">{{ $opinion->usuario?->name ?? 'Cuenta eliminada' }} · {{ $opinion->created_at->format('d/m/Y H:i') }}</p>
            @if($opinion->trashed() && $opinion->motivo_moderacion)
                <p class="mod-notice">{{ $opinion->motivo_moderacion }}</p>
            @endif
            @if(!$opinion->trashed())
                <details class="mod-opinion-review">
                    <summary>Eliminar opinión<x-mod-icon name="arrow"/></summary>
                    @include('moderador.partials.delete-form', ['tipo'=>'opiniones', 'content'=>$opinion])
                </details>
            @endif
        </article>
    @empty
        <div class="mod-card mod-empty"><x-mod-icon name="comment"/><h3>No hay opiniones{{ request('estado') ? ' en este estado' : '' }}</h3><p>Las reseñas y comentarios se publican directamente.</p></div>
    @endforelse
</div>
<div class="mod-pagination">{{ $opiniones->links() }}</div>
@endsection
