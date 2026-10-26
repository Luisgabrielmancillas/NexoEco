@extends($sellerArea ? 'layouts.seller-panel' : 'layouts.marketplace')
@section('title', ($sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes') . ' | NexoEco')
@section($sellerArea ? 'seller-content' : 'content')
<div class="nexo-container commerce-page commerce-workspace growth-workspace">
    <x-seller-growth-hero eyebrow="Conecta con tu comunidad" :title="$sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes'" :description="$sellerArea ? 'Cada consulta es una oportunidad. Resuelve dudas y acuerda los detalles con tus compradores.' : 'Pregunta, conoce los detalles y encuentra eso que estás buscando. Tus conversaciones, en un solo lugar.'" icon="chat" badge="Conversaciones privadas">
        <x-slot:actions><a class="seller-button secondary" href="{{ route($sellerArea ? 'vendedor.apartados.index' : 'apartados.index') }}"><x-seller-icon name="bookmark"/>{{ $sellerArea ? 'Apartados recibidos' : 'Mis apartados' }}</a></x-slot:actions>
        <x-slot:meta><span><x-seller-icon name="chat"/>{{ $chats->total() }} {{ $chats->total() === 1 ? 'conversación' : 'conversaciones' }}</span><span><x-seller-icon name="shield"/>Solo tú y la otra persona</span></x-slot:meta>
    </x-seller-growth-hero>
    @if($sellerArea)
        <form method="GET" class="seller-form growth-filter commerce-filter">
            <div class="commerce-filter-field"><label for="negotiation-filter">Estado de la negociación</label><select id="negotiation-filter" name="estado"><option value="">Todos los estados</option>@foreach(\App\Models\ProductChat::NEGOTIATION_STATES as $key => $label)<option value="{{ $key }}" @selected(request('estado') === $key)>{{ $label }}</option>@endforeach</select></div>
            <button class="seller-button secondary" type="submit">Filtrar conversaciones</button>
        </form>
    @endif
    <div class="commerce-section-heading"><div><span class="buyer-eyebrow">Tu bandeja de entrada</span><h2>Conversaciones recientes</h2></div><p>Ordenadas por su última actividad</p></div>
    <div class="commerce-list commerce-inbox">
        @forelse($chats as $chat)
            @php
                $partner = auth()->id() === $chat->buyer_id ? $chat->seller : $chat->buyer;
                $image = $chat->producto && !$chat->producto->trashed() ? \App\Support\MarketplaceImage::url($chat->producto->imagenPrincipal?->imagen_url ?: $chat->producto->imagen_url) : null;
            @endphp
            <a href="{{ route('chat.show', $chat) }}" class="commerce-card commerce-thread {{ $chat->unread_count ? 'has-unread' : '' }}">
                <span class="commerce-thread-image">@if($image)<img src="{{ $image }}" alt="" loading="lazy">@else<x-seller-icon name="box"/>@endif</span>
                <div class="commerce-thread-copy">
                    <div class="commerce-thread-title"><h3>{{ $chat->producto?->nombre_producto ?? 'Producto retirado' }}</h3>@if($chat->unread_count)<span class="commerce-unread">{{ $chat->unread_count }} sin leer</span>@endif</div>
                    <span class="commerce-thread-partner"><span class="commerce-person-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($partner->name, 0, 1)) }}</span>{{ $partner->name }}</span>
                    <p class="commerce-thread-preview">{{ \Illuminate\Support\Str::limit($chat->last_message_body ?: 'Todavía no hay mensajes. Abre la conversación para empezar.', 120) }}</p>
                    <div class="commerce-thread-meta"><time datetime="{{ $chat->updated_at->toIso8601String() }}">{{ $chat->updated_at->diffForHumans() }}</time>@if($sellerArea)<span class="commerce-badge">{{ \App\Models\ProductChat::NEGOTIATION_STATES[$chat->negotiation_status ?? 'nuevo'] }}</span>@endif</div>
                </div>
                <span class="commerce-thread-open" aria-hidden="true"><x-seller-icon name="arrow"/></span>
            </a>
        @empty
            <x-seller-growth-empty icon="chat" :title="$sellerArea ? (request('estado') ? 'No hay conversaciones con este estado' : 'Tu próxima conversación empieza con una consulta') : 'Encuentra algo que te guste y pregunta por él'" :description="$sellerArea ? 'Cuando alguien te escriba desde uno de tus productos, su conversación aparecerá aquí.' : 'Abre un producto y pulsa «Chatear con el vendedor» para preguntar por él.'" />
        @endforelse
    </div>
    <div class="commerce-pagination">{{ $chats->links() }}</div>
</div>
@endsection
