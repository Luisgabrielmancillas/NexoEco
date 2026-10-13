@extends($sellerArea ? 'layouts.seller-panel' : 'layouts.marketplace')
@section('title', 'Conversación | NexoEco')
@section($sellerArea ? 'seller-content' : 'content')
<div class="nexo-container commerce-page">
    <div class="commerce-heading"><div><a href="{{ route($sellerArea ? 'vendedor.mensajes.index' : 'chat.index') }}">← {{ $sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes' }}</a><h1>{{ $chat->producto?->nombre_producto ?? 'Producto retirado' }}</h1><p>{{ $sellerArea ? 'Comprador' : 'Vendedor' }}: {{ $sellerArea ? $chat->buyer->name : $chat->seller->name }}</p></div>@if($chat->producto && !$chat->producto->trashed())<a href="{{ route('productos.show', $chat->producto) }}">Ver producto</a>@endif</div>
    <section class="commerce-card chat-room" data-product-chat data-messages-url="{{ route('chat.messages', $chat) }}" data-send-url="{{ route('chat.send', $chat) }}">
        <p class="chat-status" role="status" data-chat-status>Conectando…</p>
        <button type="button" class="chat-history" data-chat-history hidden>Cargar mensajes anteriores</button>
        <div class="chat-messages" data-chat-messages role="log" aria-live="polite" aria-label="Mensajes de la conversación"></div>
        @if($sellerArea)<div class="growth-quick-replies"><span>Respuestas rápidas</span>@foreach($quickReplies as $reply)<button type="button" class="seller-button secondary small" data-quick-reply="{{ strtr($reply->body, ['{comprador}' => $chat->buyer->name, '{producto}' => $chat->producto?->nombre_producto ?? 'este producto']) }}">{{ $reply->title }}</button>@endforeach<a class="seller-text-link" href="{{ route('vendedor.herramientas.index') }}#respuestas">{{ $subscription ? 'Administrar respuestas' : 'Conoce las herramientas de los planes' }}</a></div>@endif
        <form class="chat-compose" data-chat-form>
            <label class="sr-only" for="chat-body">Escribe tu mensaje</label>
            <textarea id="chat-body" name="body" rows="2" maxlength="5000" required placeholder="Pregunta por disponibilidad, entrega o detalles del producto"></textarea>
            <button class="seller-button" type="submit">Enviar</button>
        </form>
        <noscript>Activa JavaScript para enviar y recibir mensajes en este chat.</noscript>
    </section>
    @if($sellerArea)
    <section class="commerce-card growth-negotiation"><div class="growth-section-heading"><div><span class="buyer-eyebrow">Organización privada del vendedor</span><h2>Estado de la negociación</h2><p class="seller-help">Disponible para todos los vendedores. Una venta declarada aquí no verifica un pago ni modifica un apartado.</p></div><span class="commerce-badge">{{ \App\Models\ProductChat::NEGOTIATION_STATES[$chat->negotiation_status ?? 'nuevo'] }}</span></div>
    <form method="POST" action="{{ route('vendedor.mensajes.negociacion', $chat) }}" class="seller-form">@csrf @method('PUT')<div class="seller-fields"><div><label for="negotiation-status">Estado</label><select id="negotiation-status" name="negotiation_status">@foreach(\App\Models\ProductChat::NEGOTIATION_STATES as $value => $label)<option value="{{ $value }}" @selected(old('negotiation_status', $chat->negotiation_status ?? 'nuevo') === $value)>{{ $label }}</option>@endforeach</select></div><div><label for="reported-sale">Importe de la venta declarada (opcional)</label><input id="reported-sale" name="reported_sale_amount" type="number" min="0.01" max="99999999.99" step="0.01" value="{{ old('reported_sale_amount', $chat->reported_sale_amount) }}"><p class="seller-help">Se guarda únicamente con el estado Venta concretada.</p></div><div class="seller-field full"><label for="negotiation-notes">Notas privadas</label><textarea id="negotiation-notes" name="negotiation_notes" maxlength="3000" rows="2">{{ old('negotiation_notes', $chat->negotiation_notes) }}</textarea></div></div><button type="submit" class="seller-button small">Guardar seguimiento</button></form></section>
    @endif
</div>
@endsection
