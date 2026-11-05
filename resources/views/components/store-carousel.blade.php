@props(['id', 'label', 'viewAllUrl', 'variant' => 'local'])
<div class="store-carousel store-carousel-{{ $variant }}" data-store-carousel data-interval="5000" role="region" aria-roledescription="carrusel" aria-label="{{ $label }}">
    <div class="store-carousel-toolbar">
        <a class="buyer-text-link" href="{{ $viewAllUrl }}">Ver todas<span class="sr-only">: {{ $label }}</span><x-market-icon name="chevron"/></a>
    </div>
    <div id="{{ $id }}" class="store-carousel-track" data-carousel-track tabindex="0" aria-label="{{ $label }}. Usa las flechas para cambiar de tiendas y la barra espaciadora para pausar o reanudar el avance.">
        {{ $slot }}
    </div>
</div>
