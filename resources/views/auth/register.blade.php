@extends('layouts.marketplace')

@section('title', 'Crear cuenta - NexoEco')

@push('styles')

<style>
    .register-seller-cta { margin-top: 32px; padding: 24px; border: 1px solid rgb(var(--color-surface-rgb) / 0.4); border-radius: 18px; background: rgb(var(--color-surface-rgb) / 0.12); }
    .register-seller-eyebrow { font-size: 10px; font-weight: 800; letter-spacing: 1.8px; }
    .register-seller-cta h2 { margin: 10px 0 8px; font-size: 24px; font-weight: 800; }
    .register-seller-cta p { margin: 0 0 20px; font-size: 13px; line-height: 1.7; color: var(--color-on-primary); }
    .register-seller-cta a { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 16px; background: var(--color-surface); color: var(--color-primary-text); border-radius: 11px; font-size: 13px; font-weight: 800; }
    .register-seller-cta a:hover { background: var(--color-primary-soft); }
    .register-seller-cta a:focus-visible { outline: 3px solid var(--color-surface); outline-offset: 4px; }
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
                var(--color-primary) 0%,
                var(--color-primary) 58%,
                var(--color-primary) 100%
            );

        color: var(--color-on-primary);

        box-shadow:
            0 12px 30px rgb(var(--color-text-primary-rgb) / 0.16);
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
            rgb(var(--color-surface-rgb) / 0.09);
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
            rgb(var(--color-surface-rgb) / 0.07);
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
            1px solid rgb(var(--color-surface-rgb) / 0.2);

        border-radius: 999px;

        background:
            rgb(var(--color-surface-rgb) / 0.12);

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
            var(--color-on-primary);

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
            1px solid rgb(var(--color-surface-rgb) / 0.16);

        border-radius: 12px;

        background:
            rgb(var(--color-surface-rgb) / 0.09);

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
            rgb(var(--color-surface-rgb) / 0.15);

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

        background: var(--color-surface);

        box-shadow:
            0 8px 28px rgb(var(--color-text-primary-rgb) / 0.055);
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

        background: var(--color-surface);
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
        color: var(--color-text-secondary);
    }

    .form-input:hover {
        border-color: var(--color-border-strong);
    }

    .form-input:focus {
        border-color:
            var(--nexo-primary);

        box-shadow:
            0 0 0 3px rgb(var(--color-text-primary-rgb) / 0.1);
    }

    .form-input.has-error {
        border-color: var(--color-danger-border);
        background: var(--color-danger-soft);
    }

    .form-error {
        margin-top: 5px;

        color: var(--color-danger-text);

        font-size: 10px;
        line-height: 1.4;
        font-weight: 700;
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

    .form-hint { margin: 8px 0 0; color: var(--nexo-muted); font-size: 12px; line-height: 1.6; }

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

        color: var(--color-on-primary);

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
            0 6px 16px rgb(var(--color-text-primary-rgb) / 0.18);
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
        text-decoration: none;
    }

    .register-terms {
        max-width: 420px;

        margin:
            10px auto 0;

        color: var(--color-text-secondary);

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
                            Descubre lo mejor de tu comunidad
                        </h1>


                        <p class="register-intro-text">
                            Crea tu cuenta de comprador y descubre productos locales en NexoEco.
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
                                    Apoya a los negocios locales
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

                        <div class="register-seller-cta">
                            <span class="register-seller-eyebrow">PARA EMPRENDEDORES</span>
                            <h2>Vende con nosotros</h2>
                            <p>Haz crecer tu negocio en NexoEco. Crea tu cuenta de vendedor y completa tu documentación paso a paso.</p>
                            <a href="{{ route('vendedor.register') }}">Regístrate como vendedor <span aria-hidden="true">→</span></a>
                        </div>

                    </div>

                </section>


                {{-- =================================================
                    FORMULARIO
                ================================================== --}}

                <section class="register-card">

                    <div class="register-card-header">

                        <h2 class="register-card-title">
                            Crear cuenta de comprador
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
                                maxlength="150"
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
                                    minlength="8"
                                    pattern="{{ \App\Support\RegistrationPassword::PATTERN }}"
                                    title="{{ \App\Support\RegistrationPassword::DESCRIPTION }}"
                                    aria-describedby="password-requirements"
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


                            <p id="password-requirements" class="form-hint">{{ \App\Support\RegistrationPassword::DESCRIPTION }}</p>

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
                            Al crear tu cuenta podrás descubrir y comprar productos de tu comunidad.
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
