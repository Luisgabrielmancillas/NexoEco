<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - NexoEco</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --buyer: #2F7EE8;
            --seller: #E85D2F;
            --ink: #1E1E1E;
        }
        html, body {
            background-color: #111827 !important; /* bg-gray-900 */
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
    </style>
</head>
<body class="antialiased text-white font-sans">

    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 bg-gray-900">
        
        <div class="mb-8">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-blue-500" />
            </a>
        </div>

        <div class="w-full max-w-2xl mb-4">
            <x-auth-session-status :status="session('status')" />
        </div>

        <form method="POST" action="{{ route('login') }}" class="w-full max-w-2xl">
            @csrf

            <div class="bg-[var(--ink)] rounded-2xl shadow-2xl overflow-hidden border border-gray-700">
                <div class="p-8 border-b border-gray-700">
                    <h1 class="text-3xl font-bold">Bienvenido</h1>
                    <p class="text-gray-400 mt-1">Ingresa a tu cuenta de NexoEco</p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="email" :value="'Correo'" class="text-gray-200" />
                        <div class="md:col-span-2">
                            <x-text-input id="email" class="block w-full bg-gray-800 border-gray-700 text-white focus:border-[var(--buyer)] focus:ring-[var(--buyer)]" type="email" name="email" :value="old('email')" required autofocus />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="password" :value="'Contraseña'" class="text-gray-200" />
                        <div class="md:col-span-2">
                            <x-text-input id="password" class="block w-full bg-gray-800 border-gray-700 text-white focus:border-[var(--buyer)] focus:ring-[var(--buyer)]" type="password" name="password" required autocomplete="current-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded bg-gray-800 border-gray-700 text-[var(--buyer)] shadow-sm focus:ring-[var(--buyer)]" name="remember">
                            <span class="ms-2 text-sm text-gray-400">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-gray-400 hover:text-[var(--buyer)] transition-colors" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t border-gray-700">
                        <a href="{{ route('register') }}" class="text-[var(--buyer)] hover:underline text-sm">
                            ¿No tienes cuenta? Regístrate
                        </a>
                        <x-primary-button class="bg-[var(--seller)] hover:bg-orange-600 px-8 py-2 rounded-lg">
                            {{ __('Log in') }}
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</body>
</html>