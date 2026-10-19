@extends($sellerArea ? 'layouts.seller-panel' : 'layouts.marketplace')
@section('title', ($sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes') . ' | NexoEco')
@section($sellerArea ? 'seller-content' : 'content')
<div class="nexo-container commerce-page">
    <div class="commerce-heading"><div><span class="commerce-eyebrow">{{ $sellerArea ? 'Mi tienda · atención a compradores' : 'Mi actividad como comprador' }}</span><h1>{{ $sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes' }}</h1><p>{{ $sellerArea ? 'Responde las consultas sobre los productos que vendes.' : 'Tus conversaciones con los vendedores de los productos que te interesan.' }}</p></div><a href="{{ route($sellerArea ? 'vendedor.apartados.index' : 'apartados.index') }}">{{ $sellerArea ? 'Apartados recibidos' : 'Mis apartados' }}</a></div>
    <div class="commerce-list">
        @if($sellerArea)<form method="GET" class="seller-form growth-filter"><label for="negotiation-filter">Filtrar negociaciones</label><select id="negotiation-filter" name="estado"><option value="">Todos los estados</option>@foreach(\App\Models\ProductChat::NEGOTIATION_STATES as $key => $label)<option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>@endforeach</select><button class="seller-button secondary small" type="submit">Filtrar</button></form>@endif
        @forelse($chats as $chat)
            <a href="{{ route('chat.show', $chat) }}" class="commerce-card commerce-thread">
                <div><strong>{{ $chat->producto?->nombre_producto ?? 'Producto retirado' }}</strong><p>{{ auth()->id() === $chat->buyer_id ? $chat->seller->name : $chat->buyer->name }}</p><small>{{ $chat->updated_at->diffForHumans() }}</small></div>
                @if($chat->unread_count)<span class="commerce-badge">{{ $chat->unread_count }} sin leer</span>@endif
                @if($sellerArea)<span class="commerce-badge">{{ \App\Models\ProductChat::NEGOTIATION_STATES[$chat->negotiation_status ?? 'nuevo'] }}</span>@endif
                <span aria-hidden="true">→</span>
            </a>
        @empty
            <div class="commerce-card"><h2>{{ $sellerArea ? 'Tu próxima conversación empieza con una consulta' : 'Encuentra algo que te guste y pregunta por él' }}</h2><p>{{ $sellerArea ? 'Cuando alguien te escriba desde uno de tus productos, su conversación aparecerá aquí.' : 'Abre un producto y pulsa «Chatear con el vendedor» para preguntar por él.' }}</p><a href="{{ route($sellerArea ? 'vendedor.productos.index' : 'marketplace.index') }}">{{ $sellerArea ? 'Ver mis productos' : 'Explorar productos' }}</a></div>
        @endforelse
    </div>
    {{ $chats->links() }}
</div>
@endsection
