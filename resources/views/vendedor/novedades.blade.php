@extends('layouts.seller-panel')
@section('title', 'Seguidores y novedades | NexoEco')
@section('seller-class', 'growth-workspace')
@section('seller-content')
<x-seller-growth-hero eyebrow="Tu comunidad, cada vez más cerca" title="Seguidores y novedades" description="Comparte lo nuevo, cuenta lo que hace especial a tu negocio y dales a tus clientes un motivo para volver." icon="megaphone" tone="sage" badge="Conecta con tu comunidad">
    <x-slot:actions>@if($subscription && $tienda)<a class="seller-button" href="#crear-novedad"><x-seller-icon name="plus"/>Compartir una novedad</a>@endif<a class="growth-quiet-link" href="{{ route('vendedor.herramientas.index') }}">Mis herramientas ↗</a></x-slot:actions>
    <x-slot:meta><span class="growth-audience-count"><x-seller-icon name="users"/><strong>{{ number_format($followers) }}</strong> {{ $followers === 1 ? 'persona sigue' : 'personas siguen' }} tu tienda</span><span><x-seller-icon name="heart"/>Avisos dentro de NexoEco</span></x-slot:meta>
</x-seller-growth-hero>
@if($subscription && $tienda)
<section id="crear-novedad" class="seller-card growth-section">
    <div class="growth-section-heading"><div><span class="buyer-eyebrow">Algo nuevo que contar</span><h2>Comparte una novedad</h2><p class="seller-help">Un producto recién llegado, una historia del taller o una fecha especial. Haz que tu comunidad sea la primera en enterarse.</p></div><span class="growth-section-icon"><x-seller-icon name="megaphone"/></span></div>
    <form method="POST" action="{{ route('vendedor.novedades.store') }}" class="seller-form" data-growth-compose="news">
        @csrf
        <div class="growth-compose-layout">
            <div class="growth-compose-fields">
                <label for="update-title">Empieza con un buen título</label><input id="update-title" name="title" required maxlength="120" placeholder="Llegó nuestra colección de temporada">
                <label for="update-body">¿Qué quieres contarles?</label><textarea id="update-body" name="body" required maxlength="2000" rows="5" placeholder="Comparte los detalles y cuéntales por qué les va a encantar."></textarea>
                @if($subscription->plan === 'pro')<label for="update-publish">Elige el momento (opcional)</label><input id="update-publish" name="publish_at" type="datetime-local"><p class="seller-help">Déjalo vacío para publicar ahora. Para programar, elige una fecha en hora de Manzanillo; tu plan debe seguir activo ese día.</p>@else<p class="seller-help">Tu novedad se publica al guardar. Con Pro también puedes dejarla programada.</p>@endif
                <button class="seller-button small" type="submit">Guardar novedad <x-seller-icon name="arrow"/></button>
            </div>
            <aside class="growth-preview" aria-label="Vista previa de la novedad">
                <span class="growth-preview-eyebrow"><x-seller-icon name="sparkles"/>Una mirada antes de publicar</span>
                <div class="growth-preview-card"><span class="growth-preview-pill">{{ $tienda->nombre_tienda }}</span><h3 data-preview-title>Tu próxima novedad</h3><p data-preview-body>Las historias de tu negocio también merecen su espacio. Escribe y descubre cómo se verá tu mensaje.</p><div class="growth-preview-product"><x-seller-icon name="clock"/><span data-preview-timing>Publicación inmediata</span></div></div>
                <p class="seller-help">Vista previa orientativa. Se mostrará en tu tienda y tus seguidores recibirán un aviso en NexoEco.</p>
            </aside>
        </div>
    </form>
</section>
@else
<div class="seller-card growth-upgrade"><x-seller-icon name="users"/><h2>Haz que tu comunidad se entere</h2><p>Los avisos de nuevos productos están disponibles para todos los vendedores. Impulso y Pro agregan publicaciones de novedades; Pro también permite programarlas.</p><a class="seller-button secondary small" href="{{ route('vendedor.suscripcion.index') }}">Encuentra tu plan <x-seller-icon name="arrow"/></a></div>
@endif
<div class="seller-section-title"><div><span class="buyer-eyebrow">La historia de tu negocio, al día</span><h2>Tus publicaciones</h2><p>Aquí conviven las novedades que ya compartiste y las que están por llegar.</p></div></div>
<div class="growth-two-columns">
    @forelse($updates as $update)
    <article class="seller-card growth-news-card">
        <div><span class="commerce-badge" data-state="{{ $update->status }}">{{ ['scheduled' => 'Programada', 'published' => 'Publicada', 'blocked' => 'No publicada: plan sin vigencia'][$update->status] ?? $update->status }}</span></div>
        <h3>{{ $update->title }}</h3><p class="growth-preline">{{ $update->body }}</p>
        <p class="seller-help">{{ $update->publish_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }} · Hora de Manzanillo</p>
        <form method="POST" action="{{ route('vendedor.novedades.destroy', $update) }}">@csrf @method('DELETE')<button class="seller-button secondary small" type="submit">{{ $update->status === 'scheduled' ? 'Cancelar publicación' : 'Retirar publicación' }}</button></form>
    </article>
    @empty
    <x-seller-growth-empty icon="megaphone" title="Tu próxima historia empieza aquí" description="Todavía no has compartido novedades. Presenta una colección, celebra un logro o cuenta algo que tus clientes quieran conocer."/>
    @endforelse
</div>
<div class="seller-pagination">{{ $updates->links() }}</div>
@endsection
