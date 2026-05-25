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

    .nexo-login {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100vh;
        display: grid;
        grid-template-columns: 1.15fr .85fr;
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
        font-size: clamp(42px, 5vw, 78px);
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

    .login-box {
        width: 100%;
        max-width: 440px;
        background: rgba(255,255,255,.90);
        backdrop-filter: blur(18px);
        border-radius: 34px;
        padding: 38px;
        box-shadow: 0 30px 70px rgba(232,93,47,.16);
        border: 1px solid rgba(255,255,255,.9);
        animation: slideUp .7s ease both;
    }

    .login-box h2 {
        margin: 0 0 8px;
        font-size: 34px;
        font-weight: 950;
        color: var(--ink);
    }

    .login-box .subtitle {
        margin: 0 0 28px;
        color: var(--muted);
        line-height: 1.5;
    }

    .field {
        margin-bottom: 18px;
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

    .login-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin: 8px 0 26px;
    }

    .remember {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
    }

    .remember input {
        accent-color: var(--orange);
    }

    .forgot {
        color: var(--orange);
        font-size: 14px;
        font-weight: 850;
        text-decoration: none;
    }

    .forgot:hover {
        text-decoration: underline;
    }

    .login-btn {
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
    }

    .login-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 45px rgba(232,93,47,.42);
    }

    .register-text {
        margin-top: 22px;
        text-align: center;
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
    }

    .register-text a {
        color: var(--orange);
        font-weight: 900;
        text-decoration: none;
    }

    .register-text a:hover {
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
        .nexo-login {
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

        .login-box {
            padding: 28px 22px;
            border-radius: 26px;
        }

        .nexo-cards {
            display: none;
        }
    }
</style>

<div class="nexo-login">

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
                Marketplace para microemprendedores
            </div>

            <h1>
                Entra a <span>NexoEco</span>
            </h1>

            <p>
                Compra, vende y administra productos locales desde una plataforma moderna,
                segura y diseñada para emprendedores reales.
            </p>

            <div class="nexo-cards">
                <div class="nexo-mini-card">
                    <strong>🛍️</strong>
                    <small>Compradores</small>
                </div>

                <div class="nexo-mini-card">
                    <strong>🏪</strong>
                    <small>Vendedores</small>
                </div>

                <div class="nexo-mini-card">
                    <strong>📦</strong>
                    <small>Pedidos</small>
                </div>
            </div>

        </div>
    </section>

    <section class="nexo-right">
        <div class="login-box">

            <h2>Iniciar sesión</h2>

            <p class="subtitle">
                Accede a tu cuenta para continuar en NexoEco.
            </p>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" autocomplete="off">
                @csrf

                <div class="field">
                    <label for="email">Correo electrónico</label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
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
                        autocomplete="current-password"
                        maxlength="255"
                        placeholder="Tu contraseña"
                    >

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="login-options">

                    <label for="remember_me" class="remember">
                        <input
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                        >
                        <span>Recordarme</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="forgot" href="{{ route('password.request') }}">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif

                </div>

                <button type="submit" class="login-btn">
                    Entrar a NexoEco
                </button>

                <div class="register-text">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}">
                        Regístrate aquí
                    </a>
                </div>

            </form>

        </div>
    </section>

</div>

</x-guest-layout>