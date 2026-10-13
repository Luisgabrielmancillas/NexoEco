@extends($sellerArea ? 'layouts.seller-panel' : 'layouts.marketplace')
@section('title', ($sellerArea ? 'Apartados recibidos' : 'Mis apartados') . ' | NexoEco')
@section($sellerArea ? 'seller-content' : 'content')
<div class="nexo-container commerce-page">
    <div class="commerce-heading"><div><span class="commerce-eyebrow">{{ $sellerArea ? 'Mi tienda · anticipos de compradores' : 'Mi actividad como comprador' }}</span><h1>{{ $sellerArea ? 'Apartados recibidos' : 'Mis apartados' }}</h1><p>{{ $sellerArea ? 'Consulta los anticipos para tus productos y coordina la entrega con cada comprador.' : 'Consulta los productos que apartaste y continúa la conversación con sus vendedores.' }}</p></div><a href="{{ route($sellerArea ? 'vendedor.mensajes.index' : 'chat.index') }}">{{ $sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes' }}</a></div>
    @if(!$sellerArea && session('success'))<p class="commerce-alert" role="status">{{ session('success') }}</p>@endif
    @if(!$sellerArea && $errors->any())<div class="commerce-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if($sellerArea)
        <nav class="growth-shortcuts" aria-label="Herramientas de apartados"><a href="{{ route('vendedor.estadisticas.index') }}">Estadísticas de apartados ↗</a><a href="{{ route('vendedor.calendario') }}">Calendario y recordatorios · Pro ↗</a>@if(request('apartado'))<a href="{{ route('vendedor.apartados.index') }}">Ver todos mis apartados</a>@endif</nav>
        <section class="commerce-card commerce-connection">
            <div><span class="commerce-eyebrow">Tu cuenta de cobro</span><h2>Mercado Pago</h2><p>{{ $account ? 'Los anticipos se reciben en tu cuenta vinculada. Puedes renovar la conexión cuando lo necesites.' : 'Conecta tu cuenta una vez y activa los apartados en los productos que quieras.' }}</p>@if($account)<span class="commerce-connection-status">Cuenta vinculada · {{ $account->live_mode ? 'Pagos reales' : 'Pruebas' }}</span>@endif</div>
            @if(app(\App\Services\MercadoPagoConnection::class)->configured())
                <form method="POST" action="{{ route('vendedor.mercadopago.connect') }}">@csrf<button class="seller-button" type="submit">{{ $account ? 'Renovar conexión' : 'Conectar Mercado Pago' }}</button></form>
            @else
                <p class="seller-help">La conexión de Mercado Pago estará disponible en breve. Puedes seguir atendiendo consultas por chat.</p>
            @endif
        </section>
    @endif
    <div class="commerce-list">
        @forelse($apartados as $apartado)
            <article class="commerce-card">
                <div class="commerce-heading"><h2>{{ $apartado->producto_nombre }}</h2><span class="commerce-badge">{{ ['pendiente' => 'Pendiente de pago', 'procesando' => 'Pago en proceso', 'pagado' => 'Apartado pagado', 'reembolsado' => 'Reembolsado', 'reembolso_parcial' => 'Reembolso parcial', 'rechazado' => 'Pago rechazado', 'revertido' => 'Pago revertido'][$apartado->estado] ?? $apartado->estado }}</span></div>
                <p><strong>${{ number_format((float) $apartado->monto, 2) }} {{ $apartado->moneda }}</strong> · {{ $sellerArea ? 'Comprador: '.$apartado->buyer->name : 'Vendedor: '.$apartado->seller->name }}</p>
                <p><small>{{ $apartado->created_at->format('d/m/Y') }} · Referencia: {{ $apartado->id }}@if($apartado->mp_payment_id) · Pago: {{ $apartado->mp_payment_id }}@endif</small></p>
                <details><summary>Condiciones acordadas</summary><p class="commerce-preline">{{ $apartado->condiciones }}</p></details>
                @if($sellerArea)<details class="growth-fulfillment"><summary>Seguimiento de entrega · {{ \App\Models\Apartado::FULFILLMENT_STATES[$apartado->fulfillment_status ?? 'pendiente'] }}</summary><form method="POST" action="{{ route('vendedor.apartados.seguimiento', $apartado) }}" class="seller-form">@csrf @method('PUT')<label for="fulfillment-{{ $apartado->id }}">Estado operativo</label><select id="fulfillment-{{ $apartado->id }}" name="fulfillment_status">@foreach(\App\Models\Apartado::FULFILLMENT_STATES as $key => $label)<option value="{{ $key }}" @selected(($apartado->fulfillment_status ?? 'pendiente') === $key)>{{ $label }}</option>@endforeach</select><label for="fulfillment-notes-{{ $apartado->id }}">Notas privadas del vendedor</label><textarea id="fulfillment-notes-{{ $apartado->id }}" name="seller_notes" maxlength="3000" rows="2">{{ $apartado->seller_notes }}</textarea>@if($subscription?->plan === 'pro')<div class="seller-fields"><div><label for="delivery-{{ $apartado->id }}">Fecha de entrega acordada</label><input id="delivery-{{ $apartado->id }}" name="delivery_at" type="datetime-local" value="{{ $apartado->delivery_at?->timezone('America/Mexico_City')->format('Y-m-d\TH:i') }}"></div><div><label for="followup-{{ $apartado->id }}">Recordatorio privado</label><input id="followup-{{ $apartado->id }}" name="follow_up_at" type="datetime-local" value="{{ $apartado->follow_up_at?->timezone('America/Mexico_City')->format('Y-m-d\TH:i') }}"><p class="seller-help">Recibirás una notificación dentro de NexoEco. No se envía un mensaje al comprador.</p></div></div>@else<p class="seller-help">Pro agrega fechas de entrega, calendario y recordatorios. El seguimiento de entrega está disponible sin suscripción.</p>@endif<p class="seller-help">Hora de Manzanillo. Estos datos no modifican el anticipo ni las condiciones acordadas.</p><button class="seller-button secondary small" type="submit">Guardar seguimiento</button></form></details>@endif
                <div class="commerce-actions">
                    @if(!$sellerArea && $apartado->producto && !$apartado->producto->trashed())
                        <form method="POST" action="{{ route('chat.start', $apartado->producto) }}">@csrf<button class="seller-button secondary" type="submit">Hablar con el vendedor</button></form>
                    @else
                        <a href="{{ route($sellerArea ? 'vendedor.mensajes.index' : 'chat.index') }}" class="seller-button secondary">{{ $sellerArea ? 'Ver consultas de compradores' : 'Ver mis mensajes' }}</a>
                    @endif
                    @if($apartado->provider === 'mercadopago')
                        @if(!$sellerArea && in_array($apartado->estado, ['pendiente', 'rechazado']))
                            <form method="POST" action="{{ route('apartados.resume', $apartado) }}">@csrf<button class="seller-button" type="submit">Continuar en Mercado Pago</button></form>
                        @endif
                        @if($apartado->mp_preference_id)
                            <form method="POST" action="{{ route('apartados.sync', $apartado) }}">@csrf<button class="seller-button secondary" type="submit">Actualizar estado</button></form>
                        @endif
                    @endif
                </div>
            </article>
        @empty
            <div class="commerce-card"><h2>{{ $sellerArea ? 'Los apartados de tus compradores aparecerán aquí' : 'Todavía no has apartado un producto' }}</h2><p>{{ $sellerArea ? 'Activa esta opción al publicar o editar un producto y define el monto del anticipo.' : 'Busca la opción «Apartar con Mercado Pago» en los productos que aceptan anticipos.' }}</p><a href="{{ route($sellerArea ? 'vendedor.productos.index' : 'marketplace.index') }}">{{ $sellerArea ? 'Administrar mis productos' : 'Explorar productos' }}</a></div>
        @endforelse
    </div>
    {{ $apartados->links() }}
</div>
@endsection
