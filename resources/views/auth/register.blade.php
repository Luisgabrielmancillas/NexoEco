<x-guest-layout>

<style>
    :root {
        --cream: #FAF7F2;
        --orange: #E85D2F;
        --orange-dark: #ca542c;
        --ink: #1C1917;
        --muted: #8C8279;
        --border: #E8E0D4;
    }

    body {
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden;
        background:
            radial-gradient(circle at 10% 20%, rgba(232,93,47,.22), transparent 28%),
            radial-gradient(circle at 90% 80%, rgba(232,93,47,.10), transparent 30%),
            linear-gradient(135deg,#fff 0%,#fff7f3 100%) !important;
    }

    body > div > div:first-child {
        display: none !important;
    }

    body > div {
        padding: 0 !important;
        margin: 0 !important;
        max-width: none !important;
        width: 100% !important;
        min-height: 100vh !important;
        background: transparent !important;
    }

    body > div > div {
        margin: 0 !important;
        padding: 0 !important;
        max-width: none !important;
        width: 100% !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .nexo-register {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100vh;
        display: grid;
        grid-template-columns: 1.05fr .95fr;
        overflow: hidden;
    }

    .nexo-brand {
        position: absolute;
        top: 28px;
        left: 42px;
        z-index: 50;
        display: flex;
        align-items: center;
        gap: .7rem;
        text-decoration: none;
        font-size: 34px;
        font-weight: 950;
        letter-spacing: -1px;
        color: var(--orange);
        transition: .25s ease;
    }

    .nexo-brand:hover {
        transform: translateY(-2px);
    }

    .nexo-brand .dot {
        width: 14px;
        height: 14px;
        border-radius: 999px;
        background: var(--orange);
        box-shadow: 0 0 18px rgba(232,93,47,.55);
    }

    .bubble {
        position: absolute;
        border-radius: 50%;
        z-index: 1;
        animation: float 6s ease-in-out infinite;
    }

    .bubble-1 {
        width: 240px;
        height: 240px;
        background: rgba(232,93,47,.14);
        top: -80px;
        right: 34%;
    }

    .bubble-2 {
        width: 150px;
        height: 150px;
        background: rgba(232,93,47,.10);
        bottom: 60px;
        left: 42%;
        animation-delay: 1s;
    }

    .bubble-3 {
        width: 90px;
        height: 90px;
        background: rgba(232,93,47,.18);
        top: 110px;
        right: 70px;
        animation-delay: 2s;
    }

    .nexo-left {
        padding: 120px 70px 70px;
        display: flex;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .nexo-left-content {
        max-width: 690px;
        animation: slideLeft .7s ease both;
    }

    .nexo-badge {
        display: inline-block;
        padding: 11px 18px;
        border-radius: 999px;
        background: rgba(255,255,255,.82);
        color: var(--orange);
        font-weight: 900;
        margin-bottom: 28px;
        box-shadow: 0 12px 28px rgba(232,93,47,.10);
    }

    .nexo-left h1 {
        margin: 0;
        font-size: clamp(42px, 5vw, 76px);
        line-height: 1;
        font-weight: 950;
        color: var(--ink);
        letter-spacing: -3px;
    }

    .nexo-left h1 span {
        color: var(--orange);
    }

    .nexo-left p {
        margin-top: 26px;
        max-width: 570px;
        font-size: 18px;
        line-height: 1.7;
        color: var(--muted);
    }

    .nexo-cards {
        display: flex;
        gap: 16px;
        margin-top: 36px;
        flex-wrap: wrap;
    }

    .nexo-mini-card {
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(255,255,255,.9);
        border-radius: 22px;
        padding: 18px 22px;
        min-width: 150px;
        box-shadow: 0 15px 35px rgba(232,93,47,.10);
        transition: .25s ease;
    }

    .nexo-mini-card:hover {
        transform: translateY(-6px);
    }

    .nexo-mini-card strong {
        display: block;
        color: var(--orange);
        font-size: 25px;
        font-weight: 900;
    }

    .nexo-mini-card small {
        color: var(--muted);
        font-weight: 800;
    }

    .nexo-right {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 50px;
        position: relative;
        z-index: 3;
    }

    .register-box {
        width: 100%;
        max-width: 470px;
        background: rgba(255,255,255,.90);
        backdrop-filter: blur(18px);
        border-radius: 34px;
        padding: 38px;
        box-shadow: 0 30px 70px rgba(232,93,47,.16);
        border: 1px solid rgba(255,255,255,.9);
        animation: slideUp .7s ease both;
    }

    .register-box h2 {
        margin: 0 0 8px;
        font-size: 34px;
        font-weight: 950;
        color: var(--ink);
    }

    .register-box .subtitle {
        margin: 0 0 28px;
        color: var(--muted);
        line-height: 1.5;
    }

    .field {
        margin-bottom: 16px;
    }

    .field label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 850;
        color: var(--ink);
    }

    .field input {
        width: 100%;
        border: 2px solid var(--border);
        background: var(--cream);
        border-radius: 17px;
        padding: 15px 16px;
        outline: none;
        color: var(--ink);
        transition: .25s ease;
    }

    .field input:focus {
        background: #fff;
        border-color: var(--orange);
        box-shadow: 0 0 0 5px rgba(232,93,47,.12);
    }

    .register-btn {
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: 16px;
        background: linear-gradient(135deg, var(--orange), #ff7b47);
        color: white;
        font-weight: 950;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 18px 35px rgba(232,93,47,.30);
        transition: .25s ease;
        margin-top: 10px;
    }

    .register-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 45px rgba(232,93,47,.42);
    }

    .login-text {
        margin-top: 22px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
    }

    .login-text a {
        color: var(--orange);
        font-weight: 900;
        text-decoration: none;
    }

    .login-text a:hover {
        text-decoration: underline;
    }

    @keyframes float {
        0%,100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(25px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideLeft {
        from {
            opacity: 0;
            transform: translateX(-25px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @media (max-width: 980px) {
        .nexo-register {
            position: relative;
            min-height: 100vh;
            height: auto;
            grid-template-columns: 1fr;
            overflow-y: auto;
        }

        .nexo-left {
            padding: 110px 26px 30px;
        }

        .nexo-right {
            padding: 20px 20px 45px;
        }

        .nexo-left p {
            font-size: 16px;
        }
    }

    @media (max-width: 560px) {
        .nexo-brand {
            top: 20px;
            left: 24px;
            font-size: 29px;
        }

        .nexo-left h1 {
            letter-spacing: -1.5px;
        }

        .register-box {
            padding: 28px 22px;
            border-radius: 26px;
        }

        .nexo-cards {
            display: none;
        }
    }
</style>

<div class="nexo-register">

    <a href="{{ url('/') }}" class="nexo-brand">
        <span class="dot"></span>
        NexoEco
    </a>

    <div class="bubble bubble-1"></div>
    <div class="bubble bubble-2"></div>
    <div class="bubble bubble-3"></div>

    <section class="nexo-left">
        <div class="nexo-left-content">

            <div class="nexo-badge">
                Crea tu cuenta gratis
            </div>

            <h1>
                Únete a <span>NexoEco</span>
            </h1>

            <p>
                Regístrate para comprar productos locales, vender como emprendedor
                o comenzar a administrar tu presencia dentro del marketplace.
            </p>

            <div class="nexo-cards">
                <div class="nexo-mini-card">
                    <strong>🛍️</strong>
                    <small>Compra fácil</small>
                </div>

                <div class="nexo-mini-card">
                    <strong>🏪</strong>
                    <small>Vende rápido</small>
                </div>

                <div class="nexo-mini-card">
                    <strong>📦</strong>
                    <small>Gestiona pedidos</small>
                </div>
            </div>

        </div>
    </section>

    <section class="nexo-right">
        <div class="register-box">

            <h2>Crear cuenta</h2>

            <p class="subtitle">
                Completa tus datos para empezar en NexoEco.
            </p>

            <form method="POST" action="{{ route('register') }}" autocomplete="off">
                @csrf

                <div class="field">
                    <label for="name">Nombre</label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        autocomplete="name"
                        maxlength="120"
                        placeholder="Tu nombre"
                    >

                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="field">
                    <label for="email">Correo electrónico</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="username"
                        maxlength="120"
                        spellcheck="false"
                        placeholder="ejemplo@correo.com"
                    >

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>

                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        maxlength="255"
                        placeholder="Crea una contraseña"
                    >

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirmar contraseña</label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        maxlength="255"
                        placeholder="Confirma tu contraseña"
                    >

                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button type="submit" class="register-btn">
                    Crear cuenta
                </button>

                <div class="login-text">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}">
                        Inicia sesión aquí
                    </a>
                </div>

            </form>

        </div>
    </section>

</div>

</x-guest-layout>