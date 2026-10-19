<section id="opiniones" style="margin-top:32px;">
    <div class="buyer-section-heading">
        <div><h2>Opiniones de {{ $tipo === 'productos' ? 'este producto' : 'esta tienda' }}</h2><p>@if($item->opiniones_count){{ number_format($item->opiniones_avg_calificacion, 1) }} de 5 · {{ $item->opiniones_count }} {{ $item->opiniones_count === 1 ? 'opinión' : 'opiniones' }}@else Aún no hay opiniones publicadas.@endif</p></div>
    </div>
    @forelse($opiniones as $opinion)
        <article class="buyer-panel">
            <div class="buyer-row"><strong>{{ $opinion->usuario->nombre_completo ?: $opinion->usuario->name }}</strong><span class="opinion-rating">{{ str_repeat('★', $opinion->calificacion) }}{{ str_repeat('☆', 5 - $opinion->calificacion) }} · {{ $opinion->calificacion }}/5</span></div>
            <p class="buyer-review-text">{{ $opinion->comentario }}</p><span class="buyer-muted">{{ $opinion->created_at->format('d/m/Y') }}</span>
            @if($opinion->id_usuario !== auth()->id())@include('marketplace.partials.content-report', ['reportType'=>'opiniones', 'reportId'=>$opinion->getKey()])@endif
        </article>
    @empty
        <div class="buyer-panel"><p>Comparte tu experiencia para ayudar a otros compradores.</p></div>
    @endforelse
    @if($opiniones->hasPages())<div style="margin:20px 0;">{{ $opiniones->links() }}</div>@endif
    @if(auth()->check() && auth()->user()->hasVerifiedEmail())
        <div class="buyer-panel">
            <h3>{{ $miOpinion ? 'Editar mi opinión' : 'Escribe tu opinión' }}</h3>
            <form method="POST" action="{{ route('comprador.opiniones.store', [$tipo, $item->getKey()]) }}" class="buyer-form">
                @csrf
                @if($errors->any())<div class="buyer-notice" role="alert">{{ $errors->first() }}</div>@endif
                <div class="buyer-field"><label for="calificacion">Tu calificación</label><select id="calificacion" name="calificacion" required><option value="">Selecciona las estrellas</option>@for($rating=5; $rating>=1; $rating--)<option value="{{ $rating }}" @selected((int) old('calificacion', $miOpinion?->calificacion) === $rating)>{{ $rating }} {{ $rating === 1 ? 'estrella' : 'estrellas' }}</option>@endfor</select></div>
                <div class="buyer-field"><label for="comentario">Tu experiencia</label><textarea id="comentario" name="comentario" minlength="5" maxlength="2000" required placeholder="Cuéntanos tu experiencia con {{ $tipo === 'productos' ? 'el producto' : 'la tienda' }}">{{ old('comentario', $miOpinion?->comentario) }}</textarea></div>
                <button class="buyer-button" type="submit">{{ $miOpinion ? 'Actualizar opinión' : 'Publicar opinión' }}</button>
            </form>
        </div>
    @else
        <div class="buyer-panel"><a class="buyer-text-link" href="{{ auth()->check() ? route('verification.notice') : route('login') }}">{{ auth()->check() ? 'Verifica tu correo' : 'Inicia sesión' }} para escribir una opinión</a></div>
    @endif
</section>
