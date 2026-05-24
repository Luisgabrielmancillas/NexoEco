<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - NexoEco</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --buyer: #2F7EE8;
            --seller: #E85D2F;
            --ink: #1E1E1E;
        }
        /* Forzamos el fondo oscuro en todo el documento */
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

        <form method="POST" action="{{ route('register') }}" class="w-full max-w-2xl">
            @csrf

            <div class="bg-[var(--ink)] rounded-2xl shadow-2xl overflow-hidden border border-gray-700">
                <div class="p-8 border-b border-gray-700">
                    <h1 class="text-3xl font-bold">Crear cuenta</h1>
                    <p class="text-gray-400 mt-1">Únete a NexoEco</p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="name" :value="'Nombre'" class="text-gray-200" />
                        <div class="md:col-span-2">
                            <x-text-input id="name" class="block w-full bg-gray-800 border-gray-700 text-white focus:border-[var(--buyer)] focus:ring-[var(--buyer)]" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="email" :value="'Correo'" class="text-gray-200" />
                        <div class="md:col-span-2">
                            <x-text-input id="email" class="block w-full bg-gray-800 border-gray-700 text-white focus:border-[var(--buyer)] focus:ring-[var(--buyer)]" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="password" :value="'Contraseña'" class="text-gray-200" />
                        <div class="md:col-span-2">
                            <x-text-input id="password" class="block w-full bg-gray-800 border-gray-700 text-white focus:border-[var(--buyer)] focus:ring-[var(--buyer)]" type="password" name="password" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="password_confirmation" :value="'Confirmar contraseña'" class="text-gray-200" />
                        <div class="md:col-span-2">
                            <x-text-input id="password_confirmation" class="block w-full bg-gray-800 border-gray-700 text-white focus:border-[var(--buyer)] focus:ring-[var(--buyer)]" type="password" name="password_confirmation" required />
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-6 border-t border-gray-700">
                        <a href="{{ route('login') }}" class="text-[var(--buyer)] hover:underline text-sm">
                            ¿Ya tienes cuenta?
                        </a>
                        <x-primary-button class="bg-[var(--seller)] hover:bg-orange-600 px-6 py-2 rounded-lg">
                            Registrarse
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</body>
</html>