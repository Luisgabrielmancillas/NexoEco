@extends('layouts.marketplace')
@section('title', 'Verifica tu correo - NexoEco')
@include('auth.partials.seller-styles')

@section('content')
<div class="seller-page">
    <div class="nexo-container">
        <section class="seller-card" style="max-width:640px;margin:auto;">
            @if (session('status') === 'verification-code-sent')
                <div class="seller-notice seller-success" role="status">Enviamos un nuevo código de 4 dígitos a tu correo.</div>
            @elseif (session('status') === 'verification-send-failed')
                <div class="seller-notice" role="alert">No pudimos enviar el correo de verificación. Intenta reenviarlo.</div>
            @endif
            @include('auth.partials.email-verification', ['seller' => false])
        </section>
    </div>
</div>
@endsection
