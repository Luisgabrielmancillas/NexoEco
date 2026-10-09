@extends($sellerArea ? 'layouts.seller-panel' : 'layouts.marketplace')
@section('title', ($sellerArea ? 'Apartados recibidos' : 'Mis apartados') . ' | NexoEco')
@section($sellerArea ? 'seller-content' : 'content')
<div class="nexo-container commerce-page">
    <div class="commerce-heading"><div><span class="commerce-eyebrow">{{ $sellerArea ? 'Mi tienda · anticipos de compradores' : 'Mi actividad como comprador' }}</span><h1>{{ $sellerArea ? 'Apartados recibidos' : 'Mis apartados' }}</h1><p>{{ $sellerArea ? 'Consulta los anticipos para tus productos y coordina la entrega con cada comprador.' : 'Consulta los productos que apartaste y continúa la conversación con sus vendedores.' }}</p></div><a href="{{ route($sellerArea ? 'vendedor.mensajes.index' : 'chat.index') }}">{{ $sellerArea ? 'Mensajes de mi tienda' : 'Mis mensajes' }}</a></div>
    @if(!$sellerArea && session('success'))<p class="commerce-alert" role="status">{{ session('success') }}</p>@endif
    @if(!$sellerArea && $errors->any())<div class="commerce-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if($sellerArea)
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
