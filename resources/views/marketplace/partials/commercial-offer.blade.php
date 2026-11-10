@if($producto->activeOffer)
    @php($offerLabel = ($producto->activeOffer->discount_percent ? $producto->activeOffer->discount_percent.'% de descuento · ' : '').$producto->activeOffer->title)
    @if($compact ?? false)
        <span class="product-offer-ribbon" title="{{ $offerLabel }}" aria-label="Promoción informativa: {{ $offerLabel }}"><x-seller-icon name="tag"/><span>{{ $offerLabel }}</span></span>
    @else
        <aside class="commercial-offer" aria-label="Promoción informativa"><strong>{{ $offerLabel }}</strong>@if($producto->activeOffer->description)<p>{{ $producto->activeOffer->description }}</p>@endif @if($producto->activeOffer->ends_at)<small>Hasta el {{ $producto->activeOffer->ends_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }} · Hora de Manzanillo.</small>@endif<p>Oferta informativa. Confirma el precio final y la disponibilidad con el vendedor. El anticipo y sus condiciones se muestran por separado.</p></aside>
    @endif
@endif
