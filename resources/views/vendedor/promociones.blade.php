@extends('layouts.seller-panel')
@section('title', 'Ofertas de mi tienda | NexoEco')
@section('seller-class', 'growth-workspace')
@section('seller-content')
<x-seller-growth-hero eyebrow="Dales un motivo más para elegirte" title="Promociones comerciales" description="Una oferta de temporada, un detalle especial o ese descuento que invita a iniciar una conversación." icon="tag" :badge="$subscription ? ($subscription->plan === 'pro' ? 'Ofertas sin límite · Pro' : 'Hasta 3 ofertas · Impulso') : 'Ofertas para tu tienda'">
    <x-slot:actions>
        @if($subscription && $products?->count())<a class="seller-button" href="#crear-oferta"><x-seller-icon name="plus"/>Crear una oferta</a>
        @elseif($subscription)<a class="seller-button" href="{{ route('vendedor.productos.create') }}"><x-seller-icon name="plus"/>Publicar mi primer producto</a>
        @else<a class="seller-button" href="{{ route('vendedor.suscripcion.index') }}">Conocer los planes <x-seller-icon name="arrow"/></a>@endif
        <a class="growth-quiet-link" href="{{ route('vendedor.suscripcion.index') }}#promociones">Promocionar en búsquedas ↗</a>
    </x-slot:actions>
    <x-slot:meta><span><x-seller-icon name="tag"/>Descuentos y temporadas</span><span><x-seller-icon name="users"/>Avisos a tus seguidores</span>@if($subscription?->plan === 'pro')<span><x-seller-icon name="calendar"/>Publica en el momento indicado</span>@endif</x-slot:meta>
</x-seller-growth-hero>
<aside class="growth-note"><x-seller-icon name="chat"/><p><strong>Una oferta abre la conversación.</strong> El comprador confirma los detalles contigo por chat. Las ofertas son informativas: el precio publicado, el anticipo y sus condiciones se mantienen.</p></aside>
@if($subscription && $products?->count())
<section id="crear-oferta" class="seller-card growth-section"><div class="growth-section-heading"><div><span class="buyer-eyebrow">Pequeños motivos. Nuevas conversaciones.</span><h2>Crear una oferta</h2><p class="seller-help">Elige un producto, cuenta qué lo hace especial y deja claros los detalles.</p></div><span class="growth-section-icon"><x-seller-icon name="tag"/></span></div>@include('vendedor.partials.offer-form', ['offer' => null])</section>
@else
<div class="seller-card growth-upgrade"><x-seller-icon name="tag"/><h2>{{ $subscription ? 'Todo empieza con tu primer producto' : 'Dale un espacio a tus mejores ofertas' }}</h2><p>{{ $subscription ? 'Publica un producto en tu catálogo y vuelve aquí para crear una oferta a su medida.' : 'Con Impulso puedes mantener 3 ofertas activas. Pro agrega ofertas ilimitadas y programación para planear tus temporadas.' }} Tu catálogo y las consultas por chat siguen disponibles sin suscripción.</p><a class="seller-button secondary small" href="{{ route($subscription ? 'vendedor.productos.create' : 'vendedor.suscripcion.index') }}">{{ $subscription ? 'Agregar producto' : 'Comparar planes' }} <x-seller-icon name="arrow"/></a></div>
@endif
<div class="seller-section-title"><div><span class="buyer-eyebrow">Tu escaparate de oportunidades</span><h2>Tus promociones</h2><p>Consulta tus ofertas, ajusta sus detalles y conserva el historial de temporadas anteriores.</p></div><span class="subscription-period">{{ $offers->total() }} en tu historial</span></div>
<div class="growth-two-columns">
    @forelse($offers as $offer)
    @php
        $offerState = !$offer->enabled ? 'disabled' : (!$subscription || $offer->subscription_id !== $subscription->id || !$offer->producto ? 'blocked' : ($offer->ends_at?->lessThanOrEqualTo(now()) ? 'ended' : ($offer->starts_at->isFuture() ? 'scheduled' : 'active')));
        $offerLabels = ['disabled' => 'Desactivada', 'blocked' => 'Sin vigencia', 'ended' => 'Terminada', 'scheduled' => 'Programada', 'active' => 'Activa'];
    @endphp
    <article class="seller-card growth-offer">
        <span class="growth-offer-accent" aria-hidden="true"><x-seller-icon name="tag"/></span>
        <span class="commerce-badge" data-state="{{ $offerState }}">{{ $offerLabels[$offerState] }}</span>
        <h3>{{ $offer->title }}</h3><p>{{ $offer->producto?->nombre_producto ?? 'Producto retirado' }}</p>
        @if($offer->discount_percent)<div class="growth-discount"><strong>{{ $offer->discount_percent }}<span>%</span></strong><span>de descuento<br><small>Oferta informativa</small></span></div>@endif
        <p class="seller-help">Desde {{ $offer->starts_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }}{{ $offer->ends_at ? ' hasta '.$offer->ends_at->timezone('America/Mexico_City')->format('d/m/Y H:i') : ' · Sin fecha de fin' }}.</p>
        @if($subscription && $offer->subscription_id === $subscription->id && $offer->producto)<details><summary>Editar promoción</summary>@include('vendedor.partials.offer-form')</details>@endif
        @if($offer->enabled)<form method="POST" action="{{ route('vendedor.promociones.destroy', $offer) }}">@csrf @method('DELETE')<button class="seller-button secondary small" type="submit">Desactivar promoción</button></form>@endif
    </article>
    @empty
    <x-seller-growth-empty icon="tag" title="Una buena oferta puede iniciar una gran conversación" description="Todavía no hay promociones comerciales. Crea la primera y dale a tus clientes un motivo más para visitar tu tienda."/>
    @endforelse
</div>
<div class="seller-pagination">{{ $offers->links() }}</div>
@endsection
