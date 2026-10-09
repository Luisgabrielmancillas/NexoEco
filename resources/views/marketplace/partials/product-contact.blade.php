<div id="entrega" class="product-contact-area">
    @if($errors->any())<div class="commerce-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if(auth()->id() !== $producto->tienda?->id_vendedor)
        <section class="product-chat-card">
            <div class="product-contact-heading"><span class="product-icon-circle"><x-market-icon name="chat"/></span><div><h2>¿Te interesa? Hablemos.</h2><p>Pregunta por disponibilidad, detalles y opciones de entrega.</p></div></div>
            <form method="POST" action="{{ route('chat.start', $producto) }}">@csrf<button class="product-action product-action-chat" type="submit"><x-market-icon name="chat"/>Chatear con el vendedor <span aria-hidden="true">→</span></button></form>
            <p class="product-action-note">Conversación privada, directamente con quien vende.</p>
        </section>
        @if($producto->apartados_activos)
            @php($mpReady = app(\App\Services\MercadoPagoConnection::class)->available($producto->tienda?->user?->mercadoPagoAccount))
            <section class="product-reservation-card">
                <div class="product-reservation-top"><span class="product-option-badge"><x-market-icon name="bookmark"/>Apartado disponible</span><span class="product-payment-brand">Mercado Pago</span></div>
                <h2>Aparta hoy, acuerda la entrega</h2><p class="product-reservation-copy">Este vendedor acepta anticipos. Confirma la disponibilidad por chat y revisa sus condiciones antes de continuar.</p>
                <div class="product-deposit-breakdown"><div><span>Anticipo para apartar</span><strong>${{ number_format((float) $producto->apartado_monto, 2) }} <small>MXN</small></strong></div><div><span>Resto a acordar</span><strong>${{ number_format(max(0, (float) $producto->precio - (float) $producto->apartado_monto), 2) }} <small>MXN</small></strong></div></div>
                <details class="product-reservation-terms"><summary>Antes de apartar: condiciones del vendedor <x-market-icon name="chevron-down"/></summary><p>{{ $producto->apartado_condiciones }}</p></details>
                @if($mpReady)
                    <form method="POST" action="{{ route('apartados.store', $producto) }}">@csrf<label class="commerce-consent"><input type="checkbox" name="acepto_condiciones" value="1" required> He leído y acepto las condiciones del apartado.</label><button class="product-action product-action-payment" type="submit"><x-market-icon name="bookmark"/>Apartar con Mercado Pago <span aria-hidden="true">→</span></button></form>
                    <p class="product-action-note">Solo pagas el anticipo. El vendedor lo recibe en su cuenta.</p>
                @else
                    <p class="product-payment-pending" role="status">El apartado en línea estará disponible cuando el vendedor conecte su cuenta. Mientras tanto, puedes consultar por chat.</p>
                @endif
            </section>
        @endif
    @else
        <section class="product-chat-card"><div class="product-contact-heading"><span class="product-icon-circle"><x-market-icon name="store"/></span><div><h2>Este es uno de tus productos</h2><p>Atiende las consultas o actualiza los detalles de tu publicación.</p></div></div><a class="product-action product-action-chat" href="{{ route('vendedor.mensajes.index') }}"><x-market-icon name="chat"/>Ver mensajes de mis compradores <span aria-hidden="true">→</span></a><a class="product-owner-edit" href="{{ route('vendedor.productos.edit', $producto) }}">Editar producto</a></section>
    @endif
</div>
