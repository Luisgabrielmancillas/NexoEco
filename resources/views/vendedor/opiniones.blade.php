@extends('layouts.seller-panel')
@section('title', 'Opiniones recibidas | NexoEco')
@section('seller-content')
<div class="seller-heading"><div><h1>Historial de opiniones</h1><p>Reseñas de tu tienda y tus productos. Tus respuestas serán públicas y el comprador recibirá una notificación.</p></div><a class="seller-button secondary" href="{{ route('comprador.opiniones') }}">Mis opiniones como comprador</a></div>
@forelse($opiniones as $opinion)
    <article class="seller-card" id="opinion-{{ $opinion->id }}">
        <h2>{{ $opinion->producto?->nombre_producto ?? $opinion->tienda?->nombre_tienda }}</h2>
        <p><strong>{{ $opinion->usuario?->nombre_completo ?: $opinion->usuario?->name }}</strong> · {{ $opinion->calificacion }}/5 · {{ $opinion->created_at->format('d/m/Y') }}</p>
        <p class="buyer-review-text">{{ $opinion->comentario }}</p>
        @include('marketplace.partials.review-reply')
        @if($opinion->id_usuario !== auth()->id())
        <details><summary>{{ $opinion->respuesta_vendedor ? 'Editar respuesta' : 'Responder al comprador' }}</summary>
            <form class="seller-form" method="POST" action="{{ route('vendedor.opiniones.reply', $opinion) }}">
                @csrf @method('PUT')
                <div class="seller-field"><label for="respuesta-{{ $opinion->id }}">Tu respuesta pública</label><textarea id="respuesta-{{ $opinion->id }}" name="respuesta_vendedor" required minlength="5" maxlength="2000" rows="3">{{ $opinion->respuesta_vendedor }}</textarea></div>
                <button type="submit" class="seller-button">Publicar respuesta</button>
            </form>
        </details>
        @endif
    </article>
@empty
    <div class="seller-card"><h2>Aún no hay opiniones</h2><p>Cuando un comprador opine sobre tu tienda o tus productos, podrás responder aquí.</p></div>
@endforelse
{{ $opiniones->links() }}
@endsection
