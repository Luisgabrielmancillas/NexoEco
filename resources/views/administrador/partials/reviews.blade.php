<section class="buyer-panel" id="opiniones">
    <h2>Opiniones de los usuarios</h2>
    @forelse($opiniones as $opinion)
        <article class="admin-list-item"><div class="buyer-row"><h3>{{ $opinion->usuario->nombre_completo ?: $opinion->usuario->name }}</h3><span class="opinion-rating">{{ $opinion->calificacion }}/5 ★</span></div><p class="buyer-review-text">{{ $opinion->comentario }}</p><p class="buyer-muted">{{ $opinion->created_at->format('d/m/Y H:i') }}</p></article>
    @empty<p class="buyer-muted">Aún no hay opiniones publicadas.</p>@endforelse
    {{ $opiniones->withQueryString()->fragment('opiniones')->links() }}
</section>
