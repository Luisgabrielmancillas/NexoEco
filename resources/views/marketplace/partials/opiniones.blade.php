@php
    $canReview = auth()->check() && auth()->user()->hasVerifiedEmail();
    $reviewErrors = $errors->hasAny(['calificacion', 'comentario']);
    $displayOpiniones = collect($opiniones->items());
    // Keep the author's controls available even when their review is on another page.
    if ($miOpinion && !$displayOpiniones->contains(fn ($review) => $review->getKey() === $miOpinion->getKey())) {
        $displayOpiniones->prepend($miOpinion->loadMissing('usuario'));
    }
@endphp
<section id="opiniones" class="reviews-section">
    <div class="buyer-section-heading">
        <div><h2>Opiniones de {{ $tipo === 'productos' ? 'este producto' : 'esta tienda' }}</h2><p>@if($item->opiniones_count){{ number_format($item->opiniones_avg_calificacion, 1) }} de 5 · {{ $item->opiniones_count }} {{ $item->opiniones_count === 1 ? 'opinión' : 'opiniones' }}@else Aún no hay opiniones publicadas.@endif</p></div>
    </div>
    @forelse($displayOpiniones as $opinion)
        @php($ownReview = $canReview && $opinion->id_usuario === auth()->id())
        <article class="buyer-panel review-card {{ $ownReview ? 'review-card-own' : '' }}">
            <div class="review-header"><div><strong>{{ $opinion->usuario->nombre_completo ?: $opinion->usuario->name }}</strong>@if($ownReview)<span class="review-owner-label">Tu opinión</span>@endif</div><span class="opinion-rating" aria-label="{{ $opinion->calificacion }} de 5 estrellas">{{ str_repeat('★', $opinion->calificacion) }}{{ str_repeat('☆', 5 - $opinion->calificacion) }} · {{ $opinion->calificacion }}/5</span></div>
            <p class="buyer-review-text">{{ $opinion->comentario }}</p>
            <time class="buyer-muted" datetime="{{ $opinion->created_at->toDateString() }}">{{ $opinion->created_at->format('d/m/Y') }}</time>
            @if($ownReview)
                <details class="review-editor" @if($reviewErrors || request()->integer('editar_opinion') === $opinion->getKey()) open @endif>
                    <summary class="review-icon-button" aria-label="Editar mi opinión" title="Editar mi opinión"><x-seller-icon name="edit"/></summary>
                    <div class="review-editor-content">
                        <h3>Editar mi opinión</h3>
                        @include('marketplace.partials.review-form', ['editing' => true])
                    </div>
                </details>
                <form class="review-delete" method="POST" action="{{ route('comprador.opiniones.destroy', $opinion->getKey()) }}">
                    @csrf @method('DELETE')
                    <button class="review-icon-button danger" type="submit" aria-label="Eliminar mi opinión" title="Eliminar mi opinión"><x-seller-icon name="trash"/></button>
                </form>
            @elseif($opinion->id_usuario !== auth()->id())
                @include('marketplace.partials.content-report', ['reportType' => 'opiniones', 'reportId' => $opinion->getKey()])
            @endif
        </article>
    @empty
        <div class="buyer-panel"><p>Comparte tu experiencia para ayudar a otros compradores.</p></div>
    @endforelse
    @if($opiniones->hasPages())<div class="review-pagination">{{ $opiniones->links() }}</div>@endif
    @if($canReview)
        @unless($miOpinion)
            <div class="buyer-panel"><h3>Escribe tu opinión</h3>@include('marketplace.partials.review-form', ['editing' => false])</div>
        @endunless
    @else
        <div class="buyer-panel"><a class="buyer-text-link" href="{{ auth()->check() ? route('verification.notice') : route('login') }}">{{ auth()->check() ? 'Verifica tu correo' : 'Inicia sesión' }} para escribir una opinión</a></div>
    @endif
</section>
