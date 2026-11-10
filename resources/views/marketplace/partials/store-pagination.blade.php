@if($paginator->hasPages())
@php($entity = $entity ?? 'tiendas')
<nav class="store-pagination" aria-label="Paginación de {{ $entity }}">
    <p>Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ number_format($paginator->total()) }} {{ $entity }}. Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}.</p>
    <div class="action-row">@if($paginator->onFirstPage())<span class="store-pagination-disabled" aria-disabled="true">Anterior</span>@else<a class="buyer-button secondary" rel="prev" href="{{ $paginator->previousPageUrl() }}">Anterior</a>@endif
    @php($pages = array_unique(array_merge([1], range(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)), [$paginator->lastPage()])))
    @php($previous = 0)
    @foreach($pages as $page)
        @if($previous && $page > $previous + 1)<span class="store-pagination-disabled" aria-hidden="true">…</span>@endif
        @if($page === $paginator->currentPage())<span class="buyer-button" aria-current="page">{{ $page }}</span>@else<a class="buyer-button secondary" aria-label="Ir a la página {{ $page }}" href="{{ $paginator->url($page) }}">{{ $page }}</a>@endif
        @php($previous = $page)
    @endforeach
    @if($paginator->hasMorePages())<a class="buyer-button secondary" rel="next" href="{{ $paginator->nextPageUrl() }}">Siguiente</a>@else<span class="store-pagination-disabled" aria-disabled="true">Siguiente</span>@endif</div>
</nav>
@endif
