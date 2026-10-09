@extends($sellerArea ? 'layouts.seller-panel' : 'layouts.marketplace')
@section('title', 'Conversación | NexoEco')
@section($sellerArea ? 'seller-content' : 'content')
<div class="nexo-container commerce-page">
    <div class="commerce-heading"><div><a href="{{ route($sellerArea ? 'vendedor.mensajes.index' : 'chat.index') }}">← {{ $sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes' }}</a><h1>{{ $chat->producto?->nombre_producto ?? 'Producto retirado' }}</h1><p>{{ $sellerArea ? 'Comprador' : 'Vendedor' }}: {{ $sellerArea ? $chat->buyer->name : $chat->seller->name }}</p></div>@if($chat->producto && !$chat->producto->trashed())<a href="{{ route('productos.show', $chat->producto) }}">Ver producto</a>@endif</div>
    <section class="commerce-card chat-room" data-product-chat data-messages-url="{{ route('chat.messages', $chat) }}" data-send-url="{{ route('chat.send', $chat) }}">
        <p class="chat-status" role="status" data-chat-status>Conectando…</p>
        <button type="button" class="chat-history" data-chat-history hidden>Cargar mensajes anteriores</button>
        <div class="chat-messages" data-chat-messages role="log" aria-live="polite" aria-label="Mensajes de la conversación"></div>
        <form class="chat-compose" data-chat-form>
            <label class="sr-only" for="chat-body">Escribe tu mensaje</label>
            <textarea id="chat-body" name="body" rows="2" maxlength="5000" required placeholder="Pregunta por disponibilidad, entrega o detalles del producto"></textarea>
            <button class="seller-button" type="submit">Enviar</button>
        </form>
        <noscript>Activa JavaScript para enviar y recibir mensajes en este chat.</noscript>
    </section>
</div>
@endsection
