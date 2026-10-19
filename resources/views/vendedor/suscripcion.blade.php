@extends('layouts.seller-panel')
@section('title', 'Planes y promoción | NexoEco')
@section('seller-class', 'growth-workspace growth-subscriptions')
@section('seller-content')
@php
    $active = $subscription?->entitled() ?? false;
    $open = $subscription && ($active || $subscription->status !== 'cancelled');
    $currentPlan = $subscription?->planDetails();
@endphp
<section class="subscription-hero">
    <div class="subscription-hero-copy"><span class="growth-kicker"><span aria-hidden="true"></span>Hecho para negocios como el tuyo</span><h1>Tu próximo cliente<br>merece <em>encontrarte.</em></h1><p>Dale más visibilidad a lo que haces y más herramientas a tu día a día. Tú pones el talento; elige cómo hacerlo crecer.</p><div class="growth-hero-actions"><a class="seller-button" href="#planes">Encuentra tu plan <x-seller-icon name="arrow"/></a><a class="growth-quiet-link" href="#comparar-planes">Comparar beneficios ↓</a></div><div class="growth-hero-meta"><span><x-seller-icon name="shield"/>Sin permanencia obligatoria</span><span><x-seller-icon name="store"/>Vender sigue siendo gratis</span></div></div>
    <div class="subscription-illustration" aria-hidden="true"><div class="subscription-orbit"></div><div class="subscription-orbit inner"></div><span class="subscription-float"><span class="subscription-float-dot"></span>Más cerca de tus clientes</span><span class="subscription-shop"><x-seller-icon name="store"/><span>Tu negocio, al frente</span></span><span class="subscription-float bottom"><x-seller-icon name="sparkles"/>Visibilidad con propósito</span><span class="subscription-art-spark">✦</span></div>
</section>
<section class="seller-card subscription-status" aria-labelledby="subscription-status-title">
    <span class="growth-status-icon {{ $active ? 'is-active' : '' }}"><x-seller-icon :name="$active ? 'check' : 'store'"/></span><div><span class="buyer-eyebrow">Tu punto de partida</span><h2 id="subscription-status-title">{{ $active ? $currentPlan['name'].' activo' : ($open ? $currentPlan['name'].' · '.(['pending' => 'Pendiente de pago', 'authorized' => 'Esperando pago', 'paused' => 'En pausa'][$subscription->status] ?? 'Sin vigencia') : 'Vendes sin suscripción') }}</h2>
    <p class="seller-help">@if($active){{ $subscription->status === 'cancelled' ? 'Renovación cancelada. Beneficios hasta el ' : 'Periodo pagado hasta el ' }}{{ $subscription->paid_until->format('d/m/Y H:i') }}. @if($subscription->status === 'authorized')Renovación automática de ${{ number_format((float)$subscription->amount, 0) }} MXN al mes.@endif @elseif($open)Los beneficios comienzan después de confirmar el primer pago.@elseTu tienda, catálogo, mensajes y apartados siguen disponibles. Los planes agregan visibilidad a tu negocio.@endif</p></div>
    @if($open && $subscription->status !== 'cancelled')<form method="POST" action="{{ route('vendedor.suscripcion.sync', $subscription) }}">@csrf<button class="seller-button secondary small" type="submit">Actualizar estado</button></form>@endif
</section>
@if(!$available)<div class="subscription-availability" role="status"><x-seller-icon name="clock"/><div><strong>Conoce los planes. Contratación próximamente.</strong><p>Estamos preparando los pagos de suscripciones. Por ahora puedes comparar los beneficios.</p></div></div>@endif
<section id="planes" aria-labelledby="plans-title">
    <div class="seller-section-title"><div><span class="buyer-eyebrow">A tu ritmo, con más posibilidades</span><h2 id="plans-title">Un plan para tu siguiente paso</h2><p>Opciones claras pensadas en ti. Precios en pesos mexicanos.</p></div><span class="subscription-period"><span class="growth-live-dot" aria-hidden="true"></span>Mensual · MXN</span></div>
    <div class="subscription-plans">
        @foreach($plans as $key => $plan)
        <article class="seller-card subscription-plan {{ $key === 'pro' ? 'pro' : '' }}">
            <div class="subscription-plan-top"><span class="seller-icon"><x-seller-icon name="{{ $key === 'pro' ? 'sparkles' : 'chart' }}"/></span><span class="subscription-tag">{{ $key === 'pro' ? 'Más alcance. Más control.' : ($key === 'impulso' ? 'Tu primer impulso' : 'Para iniciar') }}</span></div>
            <h3>{{ $plan['name'] }}</h3><p class="subscription-description">{{ $plan['description'] }}</p>
            <div class="subscription-price">${{ number_format($plan['price'], 0) }}<span> MXN / mes</span></div>
            <div class="subscription-plan-highlight"><strong>{{ $plan['product_limit'] }}</strong><span>productos promocionados<br><small>{{ $key === 'pro' ? 'Prioridad alta en búsquedas' : ($key === 'impulso' ? 'Prioridad media en búsquedas' : 'Sin promociones activas') }}</small></span><x-seller-icon name="arrow"/></div>
            <ul class="subscription-benefits">@foreach($plan['benefits'] as $benefit)<li><span aria-hidden="true"><x-seller-icon name="check"/></span>{{ $benefit }}</li>@endforeach</ul>
            @if(!$tienda)<a class="seller-button {{ $key === 'impulso' || $key === 'gratis' ? 'secondary' : '' }}" href="{{ route('vendedor.tienda.create') }}">Crea tu tienda para comenzar</a>
            @elseif($open)<div class="subscription-plan-current">{{ $subscription->plan === $key ? ($active ? 'Tu plan actual' : 'Solicitud actual') : 'Disponible al terminar tu suscripción actual' }}</div>
                @if($subscription->plan === $key && $subscription->status === 'pending' && $available)<form method="POST" action="{{ route('vendedor.suscripcion.checkout') }}">@csrf<input type="hidden" name="plan" value="{{ $key }}"><label class="subscription-consent"><input type="checkbox" name="acepto_renovacion" value="1" required><span>Acepto el cobro de ${{ $plan['price'] }} MXN al mes y la renovación automática hasta cancelar.</span></label><button type="submit" class="seller-button">Continuar contratación</button></form>@endif
            @elseif($available)
                @if($key === 'gratis')
                    <form method="POST" action="{{ route('vendedor.suscripcion.gratis') }}">@csrf<button class="seller-button secondary" type="submit">Empezar {{ $plan['name'] }}</button></form>
                @else
                    <form method="POST" action="{{ route('vendedor.suscripcion.checkout') }}">@csrf<input type="hidden" name="plan" value="{{ $key }}"><label class="subscription-consent"><input type="checkbox" name="acepto_renovacion" value="1" required><span>Acepto el cobro de ${{ $plan['price'] }} MXN al mes y la renovación automática hasta cancelar.</span></label><button class="seller-button {{ $key === 'impulso' ? 'secondary' : '' }}" type="submit">Elegir {{ $plan['name'] }} <span aria-hidden="true">↗</span></button></form>
                @endif
            @else<button class="seller-button secondary" type="button" disabled>Disponible próximamente</button>@endif
        </article>
        @endforeach
    </div>
    <p class="subscription-footnote">Puedes cancelar la renovación desde aquí y conservar el periodo pagado. La promoción aumenta la visibilidad; los resultados dependen de las búsquedas y del interés de los compradores.</p>
<<<<<<< HEAD
    
    <div id="comparar-planes" class="growth-table-wrap growth-section">
        <table class="growth-table growth-plan-comparison">
            <caption>Compara todos los beneficios <span>Elige lo que tu negocio necesita hoy.</span></caption>
            <thead>
                <tr>
                    <th scope="col">Funcionalidad</th>
                    <th scope="col">Básico <small>Gratis</small></th>
                    <th scope="col">Impulso <small>$149 MXN/mes</small></th>
                    <th scope="col">Pro <small>$299 MXN/mes</small></th>
                </tr>
            </thead>
            <tbody>
                @foreach(config('subscriptions.comparison') as [$feature, $gratis, $impulso, $pro])
                <tr>
                    <th scope="row">{{ $feature }}</th>
                    <td>{{ $gratis }}</td>
                    <td>{{ $impulso }}</td>
                    <td>{{ $pro }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="growth-scroll-hint"><x-seller-icon name="arrow"/>Desliza la tabla para comparar los planes.</p>
=======
    <div id="comparar-planes" class="growth-table-wrap growth-section"><table class="growth-table growth-plan-comparison"><caption>Compara todos los beneficios <span>Elige lo que tu negocio necesita hoy.</span></caption><thead><tr><th scope="col">Funcionalidad</th><th scope="col">Impulso <small>$149 MXN/mes</small></th><th scope="col">Pro <small>$299 MXN/mes</small></th></tr></thead><tbody>@foreach(config('subscriptions.comparison') as [$feature, $impulso, $pro])<tr><th scope="row">{{ $feature }}</th><td>{{ $impulso }}</td><td>{{ $pro }}</td></tr>@endforeach</tbody></table></div><p class="growth-scroll-hint"><x-seller-icon name="arrow"/>Desliza la tabla para comparar los planes.</p>
>>>>>>> c08ac48a2b1a27273a1dd16872fe105ad8b980f3
</section>
@if($active)
<nav class="growth-shortcuts growth-section" aria-label="Beneficios de tu plan"><a href="{{ route('vendedor.herramientas.index') }}">Configurar herramientas de chat y tienda ↗</a><a href="{{ route('vendedor.promociones.index') }}">Crear ofertas ↗</a><a href="{{ route('vendedor.estadisticas.index') }}">Estadísticas de interés comercial ↗</a></nav>
<section class="subscription-management" id="promociones" aria-labelledby="promotions-title">
    <div class="seller-section-title"><div><span class="buyer-eyebrow">Tú eliges qué destacar</span><h2 id="promotions-title">Tus productos promocionados</h2><p>Activa o quita promociones sin costo adicional dentro de tu plan.</p></div><span class="subscription-period">{{ count($selected) }} / {{ $currentPlan['product_limit'] }} en promoción</span></div>
    @if($products?->count())<div class="subscription-products">@foreach($products as $product)
        @php($image = \App\Support\MarketplaceImage::url($product->imagenPrincipal?->imagen_url ?: $product->imagen_url))
        @php($promoted = in_array($product->getKey(), $selected))
        <article class="seller-card subscription-product"><div class="subscription-product-image">@if($image)<img src="{{ $image }}" alt="" loading="lazy">@else<x-seller-icon name="box"/>@endif</div><div><h3>{{ $product->nombre_producto }}</h3><p>${{ number_format($product->precio, 2) }} MXN</p><span class="{{ $promoted ? 'promotion-badge' : 'seller-help' }}">{{ $promoted ? 'Promoción activa' : 'Sin promoción' }}</span></div><form method="POST" action="{{ route('vendedor.suscripcion.promote', $product) }}">@csrf<input type="hidden" name="promoted" value="{{ $promoted ? '0' : '1' }}"><button class="seller-button secondary small" type="submit" aria-label="{{ $promoted ? 'Quitar promoción de ' : 'Promocionar ' }}{{ $product->nombre_producto }}" @disabled(!$promoted && count($selected) >= $currentPlan['product_limit'])>{{ $promoted ? 'Quitar' : 'Promocionar' }}</button></form></article>
    @endforeach</div><div class="seller-pagination">{{ $products->links() }}</div>@else<div class="seller-card"><h3>Tu primera promoción empieza con un producto</h3><p class="seller-help">Publica algo en tu catálogo y después selecciónalo aquí.</p><a class="seller-button small" href="{{ route('vendedor.productos.create') }}">Agregar producto</a></div>@endif
</section>
<section class="subscription-management" aria-labelledby="metrics-title"><div class="seller-section-title"><div><span class="buyer-eyebrow">Mide tu visibilidad</span><h2 id="metrics-title">{{ $subscription->plan === 'pro' ? 'Resultados de tus promociones' : 'Resumen de tus promociones' }}</h2><p>Últimos 30 días · Apariciones al cargar el catálogo y visitas a las fichas promocionadas.</p></div><a class="seller-text-link" href="{{ route('vendedor.estadisticas.index') }}">Ver estadísticas del negocio ↗</a></div>
<div class="subscription-metrics">@foreach(['product' => 'Productos', 'store' => 'Tienda'] as $kind => $label)<article class="seller-card"><h3>{{ $label }}</h3><div><strong>{{ number_format($metrics->get($kind)?->impressions ?? 0) }}</strong><span>Apariciones</span></div><div><strong>{{ number_format($metrics->get($kind)?->visits ?? 0) }}</strong><span>Visitas</span></div></article>@endforeach</div><p class="seller-help">Se omiten tus propias visitas y se cuenta como máximo una aparición o visita por sesión, elemento y hora. Son métricas orientativas, no ventas.</p></section>
@endif
<section class="subscription-management" aria-labelledby="faq-title"><div class="seller-section-title"><div><span class="buyer-eyebrow">Elige con tranquilidad</span><h2 id="faq-title">Antes de elegir</h2><p>Todo lo que necesitas saber antes de dar el siguiente paso.</p></div></div><div class="subscription-faq">
<details class="seller-card"><summary>¿Dónde aparecen mis promociones?</summary><p>Tus productos seleccionados aparecen antes que los productos sin promoción en las búsquedas y categorías que coincidan. Tu tienda participa en la sección de tiendas recomendadas. Pro tiene prioridad sobre Impulso. Todas las promociones se identifican como Patrocinado.</p></details>
<details class="seller-card"><summary>¿Puedo seguir vendiendo sin pagar un plan?</summary><p>Sí. Tu tienda, tus publicaciones, el chat y los apartados continúan disponibles. La suscripción es opcional y agrega promoción.</p></details>
<details class="seller-card"><summary>¿Cómo cambio de plan o cancelo?</summary><p>Cancela la renovación y, al terminar el periodo pagado, contrata el otro plan. No se generan dos suscripciones simultáneas ni cobros por cambio de productos. Si tu solicitud aún está pendiente, puedes cancelarla y elegir otro plan.</p></details>
<details class="seller-card"><summary>¿Cuándo se activan los beneficios?</summary><p>Después de que Mercado Pago confirme un pago aprobado. Si una renovación falla, los beneficios terminan al vencer el periodo cubierto. Al cancelar la renovación conservas el tiempo que ya pagaste.</p></details>
</div></section>
@if($open && $subscription->status !== 'cancelled')<details class="seller-card subscription-cancel"><summary>{{ $subscription->status === 'pending' ? 'Cancelar solicitud pendiente' : 'Cancelar renovación automática' }}</summary><p class="seller-help">Se detendrán los próximos cobros. Los beneficios de un periodo ya pagado continúan hasta su vencimiento.</p><form method="POST" action="{{ route('vendedor.suscripcion.cancel', $subscription) }}">@csrf<label class="subscription-consent"><input type="checkbox" name="confirmar" value="1" required><span>Confirmo que quiero cancelar la renovación de {{ $currentPlan['name'] }}.</span></label><button class="seller-button secondary small" type="submit">Confirmar cancelación</button></form></details>@endif
<<<<<<< HEAD
@endsection
=======
@endsection
>>>>>>> c08ac48a2b1a27273a1dd16872fe105ad8b980f3
