@if($opinion->respuesta_vendedor)
    <div class="seller-review-reply">
        <strong>Respuesta del vendedor</strong>
        <p class="buyer-review-text">{{ $opinion->respuesta_vendedor }}</p>
        <time datetime="{{ $opinion->respuesta_fecha?->toIso8601String() }}">{{ $opinion->respuesta_fecha?->format('d/m/Y') }}</time>
    </div>
@endif
