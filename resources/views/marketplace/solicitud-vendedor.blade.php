@extends('layouts.marketplace')

@section('title', 'Solicitud para vender - NexoEco')

@push('styles')

<style>
    .seller-request-page {
        padding: 22px 0 45px;
    }

    .seller-request-container {
        width: 100%;
        max-width: 760px;

        margin-inline: auto;
    }

    .seller-request-card {
        overflow: hidden;

        background: #ffffff;

        border: 1px solid var(--nexo-border);
        border-radius: 22px;

        box-shadow:
            0 8px 28px rgba(28, 25, 23, .055);
    }

    .seller-request-header {
        padding: 20px;

        background:
            linear-gradient(
                135deg,
                #E85D2F,
                #F4774D
            );

        color: white;
    }

    .seller-request-badge {
        display: inline-flex;

        padding: 5px 9px;

        border-radius: 999px;

        background:
            rgba(255, 255, 255, .14);

        font-size: 10px;
        font-weight: 900;
    }

    .seller-request-title {
        margin: 9px 0 0;

        font-size: 24px;
        line-height: 1.1;

        font-weight: 950;
    }

    .seller-request-description {
        margin: 7px 0 0;

        color:
            rgba(255, 255, 255, .9);

        font-size: 12px;
        line-height: 1.5;
    }

    .seller-request-body {
        padding: 18px;
    }

    .request-status {
        padding: 14px;

        border-radius: 14px;

        background: #FFF8E7;

        border: 1px solid #F4D999;
    }

    .request-status-title {
        font-size: 14px;
        font-weight: 900;
    }

    .request-status-text {
        margin-top: 4px;

        color: #746C66;

        font-size: 12px;
        line-height: 1.5;
    }

    .request-field {
        margin-top: 14px;
    }

    .request-label {
        display: block;

        margin-bottom: 5px;

        color: var(--nexo-text);

        font-size: 12px;
        font-weight: 900;
    }

    .request-input,
    .request-textarea {
        width: 100%;

        border: 1px solid var(--nexo-border);
        border-radius: 12px;

        background: white;

        color: var(--nexo-text);

        font-family: inherit;
        font-size: 13px;

        outline: none;
    }

    .request-input {
        min-height: 44px;

        padding: 0 12px;
    }

    .request-textarea {
        min-height: 95px;

        padding: 11px 12px;

        resize: vertical;
    }

    .request-input:focus,
    .request-textarea:focus {
        border-color: var(--nexo-primary);

        box-shadow:
            0 0 0 3px rgba(232, 93, 47, .09);
    }

    .request-help {
        margin-top: 4px;

        color: var(--nexo-muted);

        font-size: 10px;
        line-height: 1.4;
    }

    .request-error {
        margin-top: 4px;

        color: #B42318;

        font-size: 10px;
        font-weight: 700;
    }

    .request-documents {
        margin-top: 20px;
        padding-top: 18px;

        border-top: 1px solid var(--nexo-border);
    }

    .request-documents-title {
        margin: 0;

        font-size: 15px;
        font-weight: 900;
    }

    .request-security {
        margin-top: 7px;

        padding: 10px 11px;

        border-radius: 11px;

        background: var(--nexo-primary-soft);

        color: #74605A;

        font-size: 10px;
        line-height: 1.45;
    }

    .request-file {
        width: 100%;

        padding: 9px;

        border:
            1px dashed var(--nexo-border);

        border-radius: 12px;

        background: #FBF9F7;

        font-family: inherit;
        font-size: 11px;
    }

    .request-submit {
        width: 100%;
        min-height: 46px;

        margin-top: 20px;

        border: 0;
        border-radius: 999px;

        background: var(--nexo-primary);
        color: white;

        cursor: pointer;

        font-family: inherit;
        font-size: 13px;
        font-weight: 900;
    }

    .request-submit:hover {
        background:
            var(--nexo-primary-dark);
    }

    .request-actions {
        display: flex;
        justify-content: center;

        margin-top: 12px;
    }

    .request-back {
        color: var(--nexo-primary);

        font-size: 11px;
        font-weight: 900;
    }

    @media (min-width: 700px) {
        .seller-request-page {
            padding-top: 30px;
        }

        .seller-request-header {
            padding: 26px;
        }

        .seller-request-body {
            padding: 25px;
        }

        .seller-request-title {
            font-size: 30px;
        }
    }
</style>

@endpush


@section('content')

<div class="seller-request-page">

    <div class="nexo-container">

        <div class="seller-request-container">

            <section class="seller-request-card">

                <header class="seller-request-header">

                    <span class="seller-request-badge">
                        🏪 Vender en NexoEco
                    </span>

                    <h1 class="seller-request-title">
                        Verificación de vendedor
                    </h1>

                    <p class="seller-request-description">
                        Antes de habilitar tu tienda necesitamos
                        verificar algunos datos. Mientras tanto tu
                        cuenta seguirá funcionando como comprador.
                    </p>

                </header>


                <div class="seller-request-body">


                    @if (
                        $solicitud->estado ===
                        \App\Models\SolicitudVendedor::ESTADO_EN_REVISION
                    )

                        <div class="request-status">

                            <div class="request-status-title">
                                ⏳ Tu solicitud está en revisión
                            </div>

                            <div class="request-status-text">
                                Recibimos tus datos y documentos.
                                Un moderador de NexoEco los revisará.
                                Mientras esperas puedes seguir comprando
                                normalmente.
                            </div>

                        </div>


                        <div class="request-actions">

                            <a
                                href="{{ route('marketplace.index') }}"
                                class="request-back"
                            >
                                ← Continuar al marketplace
                            </a>

                        </div>


                    @elseif (
                        $solicitud->estado ===
                        \App\Models\SolicitudVendedor::ESTADO_APROBADA
                    )

                        <div class="request-status">

                            <div class="request-status-title">
                                ✅ Tu solicitud fue aprobada
                            </div>

                            <div class="request-status-text">
                                Tu cuenta ya fue aprobada para vender
                                dentro de NexoEco.
                            </div>

                        </div>


                    @elseif (
                        $solicitud->estado ===
                        \App\Models\SolicitudVendedor::ESTADO_RECHAZADA
                    )

                        <div class="request-status">

                            <div class="request-status-title">
                                Solicitud revisada
                            </div>

                            <div class="request-status-text">
                                Tu solicitud no fue aprobada.

                                @if ($solicitud->motivo_revision)

                                    <br><br>

                                    <strong>Motivo:</strong>
                                    {{ $solicitud->motivo_revision }}

                                @endif
                            </div>

                        </div>


                    @else

                        @if (
                            $solicitud->estado ===
                            \App\Models\SolicitudVendedor::ESTADO_REQUIERE_CORRECCION
                        )

                            <div class="request-status">

                                <div class="request-status-title">
                                    ⚠️ Necesitamos una corrección
                                </div>

                                <div class="request-status-text">

                                    {{
                                        $solicitud->motivo_revision
                                        ?: 'Revisa tus datos y vuelve a enviar la solicitud.'
                                    }}

                                </div>

                            </div>

                        @endif


                        <form
                            method="POST"
                            action="{{
                                route(
                                    'vendedor.solicitud.store'
                                )
                            }}"
                            enctype="multipart/form-data"
                        >

                            @csrf


                            <div class="request-field">

                                <label
                                    for="rfc"
                                    class="request-label"
                                >
                                    RFC
                                </label>

                                <input
                                    id="rfc"
                                    name="rfc"
                                    type="text"
                                    maxlength="13"
                                    value="{{
                                        old(
                                            'rfc',
                                            $solicitud->rfc
                                        )
                                    }}"
                                    class="request-input"
                                    autocomplete="off"
                                    required
                                >

                                @error('rfc')
                                    <div class="request-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="request-field">

                                <label
                                    for="curp"
                                    class="request-label"
                                >
                                    CURP
                                </label>

                                <input
                                    id="curp"
                                    name="curp"
                                    type="text"
                                    maxlength="18"
                                    value="{{
                                        old(
                                            'curp',
                                            $solicitud->curp
                                        )
                                    }}"
                                    class="request-input"
                                    autocomplete="off"
                                    required
                                >

                                @error('curp')
                                    <div class="request-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="request-field">

                                <label
                                    for="telefono"
                                    class="request-label"
                                >
                                    Teléfono
                                </label>

                                <input
                                    id="telefono"
                                    name="telefono"
                                    type="tel"
                                    maxlength="20"
                                    value="{{
                                        old(
                                            'telefono',
                                            $solicitud->telefono
                                        )
                                    }}"
                                    class="request-input"
                                    autocomplete="tel"
                                    required
                                >

                                @error('telefono')
                                    <div class="request-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <div class="request-field">

                                <label
                                    for="domicilio_fiscal"
                                    class="request-label"
                                >
                                    Domicilio fiscal
                                </label>

                                <textarea
                                    id="domicilio_fiscal"
                                    name="domicilio_fiscal"
                                    class="request-textarea"
                                    maxlength="1000"
                                    required
                                >{{ old(
                                    'domicilio_fiscal',
                                    $solicitud->domicilio_fiscal
                                ) }}</textarea>

                                @error('domicilio_fiscal')
                                    <div class="request-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                            </div>


                            <section class="request-documents">

                                <h2 class="request-documents-title">
                                    Documentos
                                </h2>

                                <div class="request-security">
                                    🔒 Tus documentos son privados y no
                                    aparecerán en tu perfil público.
                                    Solo se utilizarán para el proceso
                                    de revisión de vendedor.
                                </div>


                                <div class="request-field">

                                    <label
                                        for="identificacion_frente"
                                        class="request-label"
                                    >
                                        Identificación oficial
                                    </label>

                                    <input
                                        id="identificacion_frente"
                                        name="identificacion_frente"
                                        type="file"
                                        class="request-file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >

                                    <div class="request-help">
                                        Frente de INE u otra identificación
                                        oficial admitida. JPG, PNG o PDF,
                                        máximo 5 MB.
                                    </div>

                                    @error('identificacion_frente')
                                        <div class="request-error">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="request-field">

                                    <label
                                        for="identificacion_reverso"
                                        class="request-label"
                                    >
                                        Reverso de identificación
                                        <span style="font-weight:600;color:var(--nexo-muted);">
                                            (si aplica)
                                        </span>
                                    </label>

                                    <input
                                        id="identificacion_reverso"
                                        name="identificacion_reverso"
                                        type="file"
                                        class="request-file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                    >

                                    @error('identificacion_reverso')
                                        <div class="request-error">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="request-field">

                                    <label
                                        for="constancia_fiscal"
                                        class="request-label"
                                    >
                                        Constancia de situación fiscal
                                    </label>

                                    <input
                                        id="constancia_fiscal"
                                        name="constancia_fiscal"
                                        type="file"
                                        class="request-file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >

                                    @error('constancia_fiscal')
                                        <div class="request-error">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>


                                <div class="request-field">

                                    <label
                                        for="comprobante_domicilio"
                                        class="request-label"
                                    >
                                        Comprobante de domicilio
                                    </label>

                                    <input
                                        id="comprobante_domicilio"
                                        name="comprobante_domicilio"
                                        type="file"
                                        class="request-file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        required
                                    >

                                    @error('comprobante_domicilio')
                                        <div class="request-error">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </section>


                            @error('documentos')
                                <div class="request-error">
                                    {{ $message }}
                                </div>
                            @enderror


                            <button
                                type="submit"
                                class="request-submit"
                            >
                                Enviar documentos a revisión
                            </button>

                        </form>

                    @endif

                </div>

            </section>

        </div>

    </div>

</div>

@endsection