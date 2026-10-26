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
            <x-market-icon name="heart"/>
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="favorite-button" aria-label="Inicia sesión para agregar a favoritos: {{ $name }}" title="Inicia sesión para guardar en favoritos">
        <x-market-icon name="heart"/>
    </a>
@endauth
