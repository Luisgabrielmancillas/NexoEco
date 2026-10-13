@extends('layouts.marketplace')
@section('title', 'Tiendas que sigo | NexoEco')
@section('content')
<main class="nexo-container commerce-page growth-workspace growth-followed-page">
    <x-seller-growth-hero eyebrow="Los negocios que quieres tener cerca" title="Tiendas que sigo" description="Reencuéntrate con los negocios de tu comunidad y descubre sus próximos productos, ofertas e historias." icon="heart" tone="sage" badge="Tu comunidad en NexoEco">
        <x-slot:actions><a class="seller-button" href="{{ route('marketplace.index') }}">Descubrir más tiendas <x-seller-icon name="arrow"/></a><a class="growth-quiet-link" href="{{ route('comprador.notificaciones') }}">Ver notificaciones ↗</a></x-slot:actions>
        <x-slot:meta><span><x-seller-icon name="store"/>{{ $stores->total() }} {{ $stores->total() === 1 ? 'tienda en tu comunidad' : 'tiendas en tu comunidad' }}</span><span><x-seller-icon name="shield"/>Tú eliges a quién seguir</span></x-slot:meta>
    </x-seller-growth-hero>
    @if(session('success'))<p class="commerce-alert" role="status">{{ session('success') }}</p>@endif
    <div class="growth-followed-stores">
        @forelse($stores as $tienda)
        <div>@include('marketplace.partials.store-card')<form method="POST" action="{{ route('tiendas.follow', $tienda) }}">@csrf<input type="hidden" name="following" value="0"><button class="seller-button secondary small" type="submit">Dejar de seguir {{ $tienda->nombre_tienda }}</button></form></div>
        @empty
        <x-seller-growth-empty icon="heart" title="Tu comunidad empieza con una tienda" description="Abre el perfil de un negocio y elige Seguir tienda. Sus nuevos productos, ofertas y novedades llegarán a tus notificaciones."><a class="seller-button secondary small" href="{{ route('marketplace.index') }}">Explorar tiendas <x-seller-icon name="arrow"/></a></x-seller-growth-empty>
        @endforelse
    </div>
    {{ $stores->links() }}
</main>
@endsection
