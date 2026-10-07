@php
    $saved = in_array($item->getKey(), $tipo === 'productos' ? ($favoritosProductos ?? []) : ($favoritosTiendas ?? []));
    $name = $tipo === 'productos' ? $item->nombre_producto : $item->nombre_tienda;
    $label = ($saved ? 'Quitar de favoritos: ' : 'Agregar a favoritos: ').$name;
@endphp
@auth
    <form method="POST" action="{{ route('comprador.favoritos.store', [$tipo, $item->getKey()]) }}" data-favorite-form data-name="{{ $name }}" data-saved="{{ $saved ? 'true' : 'false' }}">
        @csrf
        <input type="hidden" name="_method" value="{{ $saved ? 'DELETE' : 'POST' }}">
        <button class="favorite-button" type="submit" aria-label="{{ $label }}" title="{{ $label }}" aria-pressed="{{ $saved ? 'true' : 'false' }}">
            <svg viewBox="0 0 24 24" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.8 5.7 6.3.9-4.5 4.4 1.1 6.2-5.7-3-5.7 3 1.1-6.2L2.9 9.6l6.3-.9L12 3Z"/></svg>
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="favorite-button" aria-label="Inicia sesión para agregar a favoritos: {{ $name }}" title="Inicia sesión para guardar en favoritos">
        <svg viewBox="0 0 24 24" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.8 5.7 6.3.9-4.5 4.4 1.1 6.2-5.7-3-5.7 3 1.1-6.2L2.9 9.6l6.3-.9L12 3Z"/></svg>
    </a>
@endauth
