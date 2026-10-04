@extends('layouts.marketplace')

@section('title', 'Crear cuenta - NexoEco')

@push('styles')

<style>
    /* =========================================================
       REGISTRO NEXOECO
    ========================================================== */

    .register-page {
        width: 100%;
        min-height: calc(100vh - 140px);

        display: flex;
        align-items: flex-start;
        justify-content: center;

        padding: 22px 0 40px;
    }

    .register-container {
        width: 100%;
        max-width: 1080px;

        margin-inline: auto;
    }

    .register-layout {
        width: 100%;

        display: grid;
        grid-template-columns: 1fr;

        gap: 16px;
    }

    /* =========================================================
       INTRO
    ========================================================== */

    .register-intro {
        position: relative;

        overflow: hidden;

        padding: 22px 20px;

        border-radius: 22px;

        background:
            linear-gradient(
                135deg,
                #E85D2F 0%,
                #F06A3F 58%,
                #FF8759 100%
            );

        color: #ffffff;

        box-shadow:
            0 12px 30px rgba(232, 93, 47, .16);
    }

    .register-intro::before {
        content: "";

        position: absolute;

        width: 190px;
        height: 190px;

        right: -70px;
        top: -90px;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, .09);
    }

    .register-intro::after {
        content: "";

        position: absolute;

        width: 150px;
        height: 150px;

        left: -60px;
        bottom: -90px;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, .07);
    }

    .register-intro-content {
        position: relative;
        z-index: 2;

        width: 100%;
    }

    .register-badge {
        display: inline-flex;
        align-items: center;

        gap: 6px;

        padding: 6px 10px;

        border:
            1px solid rgba(255, 255, 255, .2);

        border-radius: 999px;

        background:
            rgba(255, 255, 255, .12);

        font-size: 11px;
        font-weight: 900;
    }

    .register-intro-title {
        max-width: 480px;

        margin: 13px 0 0;

        font-size: clamp(25px, 7vw, 39px);
        line-height: 1.05;

        font-weight: 950;
        letter-spacing: -.8px;
    }

    .register-intro-text {
        max-width: 520px;

        margin: 11px 0 0;

        color:
            rgba(255, 255, 255, .9);

        font-size: 13px;
        line-height: 1.55;
    }

    .register-benefits {
        display: grid;
        grid-template-columns: 1fr;

        gap: 8px;

        margin-top: 18px;
    }

    .register-benefit {
        display: flex;
        align-items: center;

        gap: 9px;

        padding: 9px 11px;

        border:
            1px solid rgba(255, 255, 255, .16);

        border-radius: 12px;

        background:
            rgba(255, 255, 255, .09);

        font-size: 11px;
        font-weight: 800;
    }

    .register-benefit-icon {
        width: 28px;
        height: 28px;

        flex-shrink: 0;

        display: flex;
        align-items: center;
        justify-content: center;

        border-radius: 8px;

        background:
            rgba(255, 255, 255, .15);

        font-size: 14px;
    }

    /* =========================================================
       FORMULARIO
    ========================================================== */

    .register-card {
        width: 100%;

        padding: 19px 16px 20px;

        border:
            1px solid var(--nexo-border);

        border-radius: 22px;

        background: #ffffff;

        box-shadow:
            0 8px 28px rgba(28, 25, 23, .055);
    }

    .register-card-header {
        margin-bottom: 17px;
    }

    .register-card-title {
        margin: 0;

        color: var(--nexo-text);

        font-size: 22px;
        line-height: 1.1;

        font-weight: 950;
        letter-spacing: -.4px;
    }

    .register-card-subtitle {
        margin: 5px 0 0;

        color: var(--nexo-muted);

        font-size: 12px;
        line-height: 1.45;
    }

    /* =========================================================
       FORM GROUP
    ========================================================== */

    .form-group {
        margin-top: 14px;
    }

    .form-group:first-of-type {
        margin-top: 0;
    }

    .form-label {
        display: block;

        margin-bottom: 6px;

        color: var(--nexo-text);

        font-size: 12px;
        font-weight: 900;
    }

    .form-input {
        width: 100%;
        min-height: 44px;

        padding: 0 13px;

        border:
            1px solid var(--nexo-border);

        border-radius: 12px;

        background: #ffffff;
        color: var(--nexo-text);

        outline: none;

        font-family: inherit;
        font-size: 13px;

        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }

    .form-input::placeholder {
        color: #A49B95;
    }

    .form-input:hover {
        border-color: #DCCFC3;
    }

    .form-input:focus {
        border-color:
            var(--nexo-primary);

        box-shadow:
            0 0 0 3px rgba(232, 93, 47, .10);
    }

    .form-input.has-error {
        border-color: #DC6868;
        background: #FFF9F9;
    }

    .form-error {
        margin-top: 5px;

        color: #B42318;

        font-size: 10px;
        line-height: 1.4;
        font-weight: 700;
    }

    /* =========================================================
       TIPO DE CUENTA
    ========================================================== */

    .account-type-title {
        margin-bottom: 7px;

        color: var(--nexo-text);

        font-size: 12px;
        font-weight: 900;
    }

    .account-type-grid {
        display: grid;
        grid-template-columns: 1fr;

        gap: 8px;
    }

    .account-option {
        position: relative;

        display: flex;
        align-items: flex-start;

        gap: 11px;

        padding: 12px;

        border:
            1px solid var(--nexo-border);

        border-radius: 14px;

        background: #ffffff;

        cursor: pointer;

        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease,
            transform .2s ease;
    }

    .account-option:hover {
        border-color:
            rgba(232, 93, 47, .55);

        transform: translateY(-1px);
    }

    .account-option:has(
        input[type="radio"]:checked
    ) {
        border-color:
            var(--nexo-primary);

        background:
            var(--nexo-primary-soft);

        box-shadow:
            0 0 0 2px rgba(232, 93, 47, .07);
    }

    .account-option input[type="radio"] {
        width: 17px;
        height: 17px;

        flex-shrink: 0;

        margin: 2px 0 0;

        accent-color:
            var(--nexo-primary);
    }

    .account-option-content {
        min-width: 0;
        flex: 1;
    }

    .account-option-title {
        display: flex;
        align-items: center;

        gap: 6px;

        color: var(--nexo-text);

        font-size: 13px;
        font-weight: 900;
    }

    .account-option-description {
        display: block;

        margin-top: 3px;

        color: var(--nexo-muted);

        font-size: 10px;
        line-height: 1.45;
    }

    .seller-note {
        display: inline-flex;
        align-items: center;

        gap: 4px;

        margin-top: 5px;
        padding: 3px 7px;

        border-radius: 999px;

        background: #ffffff;

        color: var(--nexo-primary);

        font-size: 9px;
        font-weight: 900;
    }

    /* =========================================================
       PASSWORD
    ========================================================== */

    .password-wrapper {
        position: relative;
    }

    .password-wrapper .form-input {
        padding-right: 60px;
    }

    .password-toggle {
        position: absolute;

        top: 50%;
        right: 8px;

        transform: translateY(-50%);

        min-width: 42px;
        height: 30px;

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 0 7px;

        border: 0;
        border-radius: 8px;

        background: transparent;
        color: var(--nexo-muted);

        cursor: pointer;

        font-family: inherit;
        font-size: 10px;
        font-weight: 900;
    }

    .password-toggle:hover {
        background: var(--nexo-bg);
        color: var(--nexo-primary);
    }

    /* =========================================================
       BOTÓN
    ========================================================== */

    .register-submit {
        width: 100%;
        min-height: 46px;

        display: flex;
        align-items: center;
        justify-content: center;

        margin-top: 18px;
        padding: 0 16px;

        border: 0;
        border-radius: 999px;

        background:
            var(--nexo-primary);

        color: #ffffff;

        cursor: pointer;

        font-family: inherit;
        font-size: 13px;
        font-weight: 900;

        transition:
            background .2s ease,
            transform .2s ease,
            box-shadow .2s ease;
    }

    .register-submit:hover {
        background:
            var(--nexo-primary-dark);

        transform: translateY(-1px);

        box-shadow:
            0 6px 16px rgba(232, 93, 47, .18);
    }

    .register-submit:active {
        transform: translateY(0);
    }

    /* =========================================================
       LOGIN
    ========================================================== */

    .register-login {
        margin-top: 15px;

        color: var(--nexo-muted);

        font-size: 11px;

        text-align: center;
    }

    .register-login a {
        color: var(--nexo-primary);

        font-weight: 900;
    }

    .register-login a:hover {
        text-decoration: underline;
    }

    .register-terms {
        max-width: 420px;

        margin:
            10px auto 0;

        color: #918882;

        font-size: 9px;
        line-height: 1.45;

        text-align: center;
    }

    /* =========================================================
       TABLET
    ========================================================== */

    @media (min-width: 700px) {

        .register-page {
            padding:
                28px 0 48px;
        }

        .register-card {
            padding: 25px;
        }

        .account-type-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

        .register-benefits {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }
    }

    /* =========================================================
       DESKTOP
    ========================================================== */

    @media (min-width: 950px) {

        .register-page {
            width: 100%;

            align-items: flex-start;
            justify-content: center;

            padding:
                34px 0 55px;
        }

        .register-container {
            width: 100%;
            max-width: 1080px;

            margin-inline: auto;
        }

        .register-layout {
            width: 100%;

            grid-template-columns:
                minmax(0, .92fr)
                minmax(440px, 1.08fr);

            align-items: stretch;

            gap: 20px;
        }

        .register-intro {
            min-height: 100%;

            display: flex;
            align-items: center;

            padding: 34px;
        }

        .register-benefits {
            grid-template-columns: 1fr;

            margin-top: 24px;
        }

        .register-card {
            padding: 27px;
        }

        .register-card-title {
            font-size: 25px;
        }
    }

    /* =========================================================
       DESKTOP GRANDE
    ========================================================== */

    @media (min-width: 1250px) {

        .register-container {
            max-width: 1120px;
        }

        .register-layout {
            grid-template-columns:
                minmax(430px, .95fr)
                minmax(500px, 1.05fr);

            gap: 22px;
        }
    }

    /* =========================================================
       MÓVILES PEQUEÑOS
    ========================================================== */

    @media (max-width: 420px) {

        .register-page {
            padding:
                14px 0 30px;
        }

        .register-intro {
            padding:
                19px 16px;

            border-radius: 18px;
        }

        .register-card {
            padding:
                18px 14px;

            border-radius: 18px;
        }

        .register-intro-title {
            font-size: 27px;
        }

        .account-option {
            padding: 11px;
        }
    }
</style>

@endpush


@section('content')

<div class="register-page">

    <div class="nexo-container">

        <div class="register-container">

            <div class="register-layout">


                {{-- =================================================
                    PRESENTACIÓN
                ================================================== --}}

                <section class="register-intro">

                    <div class="register-intro-content">

                        <span class="register-badge">
                            ✨ Únete a NexoEco
                        </span>


                        <h1 class="register-intro-title">
                            Compra y vende dentro de tu comunidad
                        </h1>


                        <p class="register-intro-text">
                            Crea tu cuenta para descubrir productos
                            locales o comenzar a vender dentro de
                            NexoEco.
                        </p>


                        <div class="register-benefits">

                            <div class="register-benefit">

                                <div class="register-benefit-icon">
                                    🛍️
                                </div>

                                <span>
                                    Compra productos locales
                                </span>

                            </div>


                            <div class="register-benefit">

                                <div class="register-benefit-icon">
                                    🏪
                                </div>

                                <span>
                                    Crea tu tienda como vendedor
                                </span>

                            </div>


                            <div class="register-benefit">

                                <div class="register-benefit-icon">
                                    🤝
                                </div>

                                <span>
                                    Conecta con tu comunidad
                                </span>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                    FORMULARIO
                ================================================== --}}

                <section class="register-card">

                    <div class="register-card-header">

                        <h2 class="register-card-title">
                            Crear cuenta
                        </h2>

                        <p class="register-card-subtitle">
                            Completa tus datos. Te tomará menos
                            de un minuto.
                        </p>

                    </div>


                    <form
                        method="POST"
                        action="{{ route('register') }}"
                    >

                        @csrf


                        {{-- =========================================
                            NOMBRE COMPLETO
                        ========================================== --}}

                        <div class="form-group">

                            <label
                                for="nombre_completo"
                                class="form-label"
                            >
                                Nombre completo
                            </label>


                            <input
                                id="nombre_completo"
                                type="text"
                                name="nombre_completo"
                                value="{{ old('nombre_completo') }}"
                                class="
                                    form-input
                                    @error('nombre_completo')
                                        has-error
                                    @enderror
                                "
                                placeholder="Ej. María López"
                                autocomplete="name"
                                maxlength="255"
                                required
                                autofocus
                            >


                            @error('nombre_completo')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- =========================================
                            CORREO
                        ========================================== --}}

                        <div class="form-group">

                            <label
                                for="email"
                                class="form-label"
                            >
                                Correo electrónico
                            </label>


                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="
                                    form-input
                                    @error('email')
                                        has-error
                                    @enderror
                                "
                                placeholder="tu@correo.com"
                                autocomplete="email"
                                maxlength="255"
                                required
                            >


                            @error('email')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- =========================================
                            TIPO DE REGISTRO
                        ========================================== --}}

                        <div class="form-group">

                            <div class="account-type-title">
                                ¿Cómo quieres usar NexoEco?
                            </div>


                            <div class="account-type-grid">

                                {{-- COMPRADOR --}}

                                <label class="account-option">

                                    <input
                                        type="radio"
                                        name="tipo_registro"
                                        value="comprador"
                                        required
                                        {{
                                            old(
                                                'tipo_registro',
                                                'comprador'
                                            ) === 'comprador'
                                                ? 'checked'
                                                : ''
                                        }}
                                    >


                                    <span class="account-option-content">

                                        <span class="account-option-title">
                                            🛍️ Comprar
                                        </span>

                                        <span class="account-option-description">
                                            Explora productos y compra
                                            a emprendedores locales.
                                        </span>

                                    </span>

                                </label>


                                {{-- VENDEDOR --}}

                                <label class="account-option">

                                    <input
                                        type="radio"
                                        name="tipo_registro"
                                        value="vendedor"
                                        required
                                        {{
                                            old(
                                                'tipo_registro'
                                            ) === 'vendedor'
                                                ? 'checked'
                                                : ''
                                        }}
                                    >


                                    <span class="account-option-content">

                                        <span class="account-option-title">
                                            🏪 Vender
                                        </span>

                                        <span class="account-option-description">
                                            Solicita acceso para vender. Revisaremos tus datos antes de habilitar tu tienda.
                                        </span>

                                        <span class="seller-note">
                                            ✓ También podrás comprar
                                        </span>

                                    </span>

                                </label>

                            </div>


                            @error('tipo_registro')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- =========================================
                            CONTRASEÑA
                        ========================================== --}}

                        <div class="form-group">

                            <label
                                for="password"
                                class="form-label"
                            >
                                Contraseña
                            </label>


                            <div class="password-wrapper">

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    class="
                                        form-input
                                        @error('password')
                                            has-error
                                        @enderror
                                    "
                                    placeholder="Crea una contraseña segura"
                                    autocomplete="new-password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-password-toggle="password"
                                    aria-label="Mostrar contraseña"
                                >
                                    Ver
                                </button>

                            </div>


                            @error('password')

                                <div class="form-error">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- =========================================
                            CONFIRMAR CONTRASEÑA
                        ========================================== --}}

                        <div class="form-group">

                            <label
                                for="password_confirmation"
                                class="form-label"
                            >
                                Confirmar contraseña
                            </label>


                            <div class="password-wrapper">

                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    class="form-input"
                                    placeholder="Escribe nuevamente tu contraseña"
                                    autocomplete="new-password"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-password-toggle="password_confirmation"
                                    aria-label="Mostrar confirmación de contraseña"
                                >
                                    Ver
                                </button>

                            </div>

                        </div>


                        {{-- =========================================
                            CREAR CUENTA
                        ========================================== --}}

                        <button
                            type="submit"
                            class="register-submit"
                        >
                            Crear mi cuenta
                        </button>


                        {{-- =========================================
                            LOGIN
                        ========================================== --}}

                        <div class="register-login">

                            ¿Ya tienes una cuenta?

                            <a href="{{ route('login') }}">
                                Iniciar sesión
                            </a>

                        </div>


                        <div class="register-terms">
                            Al crear tu cuenta podrás acceder a
                            las funciones disponibles según el
                            tipo de cuenta seleccionado.
                        </div>

                    </form>

                </section>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            const buttons =
                document.querySelectorAll(
                    '[data-password-toggle]'
                );


            buttons.forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const inputId =
                                this.dataset.passwordToggle;

                            const input =
                                document.getElementById(
                                    inputId
                                );


                            if (!input) {
                                return;
                            }


                            const passwordVisible =
                                input.type === 'text';


                            input.type =
                                passwordVisible
                                    ? 'password'
                                    : 'text';


                            this.textContent =
                                passwordVisible
                                    ? 'Ver'
                                    : 'Ocultar';


                            this.setAttribute(
                                'aria-label',
                                passwordVisible
                                    ? 'Mostrar contraseña'
                                    : 'Ocultar contraseña'
                            );

                        }
                    );

                }
            );

        }
    );
</script>

@endpush