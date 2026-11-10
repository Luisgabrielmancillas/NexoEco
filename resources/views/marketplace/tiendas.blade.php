@extends('layouts.marketplace')
@section('title', ($cercanas ? 'Todas las tiendas cerca de ti' : 'Todas las tiendas').' | NexoEco')
@section('content')
<main class="nexo-container store-directory">
    <a class="market-back-button" href="{{ route('comprador.dashboard', request()->only('q', 'categoria', 'seccion')) }}#{{ $cercanas ? 'tiendas-cercanas' : 'tiendas' }}">← Volver al inicio</a>
    <div class="buyer-section-heading"><div><span class="buyer-eyebrow">Conoce a tu comunidad</span><h1>{{ $cercanas ? 'Todas las tiendas cerca de ti' : 'Todas las tiendas' }}</h1><p>{{ $cercanas ? 'Ordenadas por distancia desde tu ubicación guardada, hasta '.config('discovery.nearby_radius_km').' km.' : 'Explora los negocios y encuentra tu próximo favorito.' }}</p></div>@if($cercanas)<button type="button" class="buyer-text-link" data-dialog-open="buyer-location-dialog">Cambiar ubicación</button>@endif</div>
    <nav class="buyer-tabs" aria-label="Vista del directorio"><a href="{{ route('comprador.tiendas', request()->only('q', 'categoria', 'seccion')) }}" @if(!$cercanas) aria-current="page" class="active" @endif>Todas las tiendas</a><a href="{{ route('comprador.tiendas', array_merge(request()->only('q', 'categoria', 'seccion'), ['cercanas' => 1])) }}" @if($cercanas) aria-current="page" class="active" @endif>Cerca de mí</a></nav>
    <form method="GET" action="{{ route('comprador.tiendas') }}" class="store-directory-filters" role="search" aria-label="Buscar en las tiendas">
        @if($cercanas)<input type="hidden" name="cercanas" value="1">@endif
        @if($seccionSeleccionada)<input type="hidden" name="seccion" value="{{ $seccionSeleccionada }}">@endif
        <div><label for="store-search">Nombre o descripción de la tienda</label><input type="search" id="store-search" name="q" maxlength="100" value="{{ $busqueda }}" placeholder="¿Qué negocio buscas?"></div>
        <div><label for="store-category">Categoría de productos</label><select id="store-category" name="categoria"><option value="">Todas las categorías</option>@foreach($categorias as $categoria)<option value="{{ $categoria->getKey() }}" @selected($categoriaSeleccionada === $categoria->getKey())>{{ $categoria->nombre_categoria }}</option>@endforeach</select></div>
        <button class="buyer-button" type="submit">Buscar tiendas</button>
        @if($busqueda || $categoriaSeleccionada || $seccionSeleccionada)<a class="buyer-text-link" href="{{ route('comprador.tiendas', $cercanas ? ['cercanas' => 1] : []) }}">Limpiar filtros</a>@endif
    </form>
    @if($errors->any())<div class="buyer-notice" role="alert">{{ $errors->first() }}</div>@endif
    @if($cercanas && !$ubicacionMapa)
        <div class="buyer-empty"><h2>Elige tu ubicación para ver las tiendas cercanas</h2><p>Guarda un punto en el mapa para ordenar los negocios por distancia.</p><button type="button" class="buyer-button" data-dialog-open="buyer-location-dialog">Elegir ubicación en el mapa</button></div>
    @elseif($tiendas->isEmpty())
        <div class="buyer-empty"><h2>No encontramos tiendas{{ $cercanas ? ' cerca de este punto' : ' con esos filtros' }}</h2><p>Prueba otra búsqueda{{ $cercanas ? ' o cambia tu ubicación guardada' : ' o categoría' }}.</p></div>
    @else
        <p class="store-directory-count">{{ number_format($tiendas->total()) }} {{ $tiendas->total() === 1 ? 'tienda encontrada' : 'tiendas encontradas' }}</p>
        <div class="nearby-stores-grid">@foreach($tiendas as $nearbyStore)@include('marketplace.partials.nearby-store-card')@endforeach</div>
        <div class="market-pagination">{{ $tiendas->links('marketplace.partials.store-pagination') }}</div>
    @endif
</main>
@endsection
