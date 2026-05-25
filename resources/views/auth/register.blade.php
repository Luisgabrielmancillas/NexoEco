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
            --sellerhov: #ca542c;
            --ink: #1E1E1E;
            --cream: #FAF7F2;
        }

        html, body {
            background-color: var(--cream) !important;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
    </style>
</head>
<body class="antialiased text-gray-900 font-sans">

    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4">
        
        <form method="POST" action="{{ route('register') }}" class="w-full max-w-2xl">
            @csrf

            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-200">
                
                <div class="bg-[var(--seller)] p-10 flex flex-col items-center text-white">
                    <div class="mb-4 bg-white px-2 py-1 rounded-full shadow-lg flex items-center justify-center">
                        <a href="/">
                            <x-application-logo class="w-32 h-auto min-h-[3rem] fill-current text-[var(--seller)]" />
                        </a>
                    </div>
                    <h1 class="text-3xl font-bold mt-2">Crear cuenta</h1>
                    <p class="text-orange-100 mt-1">Únete a NexoEco</p>
                </div>

                <div class="p-10 space-y-6">
                    
                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="name" :value="'Nombre'" class="text-gray-700 font-medium" />
                        <div class="md:col-span-2">
                            <x-text-input id="name" class="block w-full bg-gray-50 border-gray-300 text-gray-900 focus:border-[var(--buyer)] focus:ring-[var(--buyer)] rounded-xl" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="email" :value="'Correo'" class="text-gray-700 font-medium" />
                        <div class="md:col-span-2">
                            <x-text-input id="email" class="block w-full bg-gray-50 border-gray-300 text-gray-900 focus:border-[var(--buyer)] focus:ring-[var(--buyer)] rounded-xl" type="email" name="email" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="password" :value="'Contraseña'" class="text-gray-700 font-medium" />
                        <div class="md:col-span-2">
                            <x-text-input id="password" class="block w-full bg-gray-50 border-gray-300 text-gray-900 focus:border-[var(--buyer)] focus:ring-[var(--buyer)] rounded-xl" type="password" name="password" required />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 items-center gap-4">
                        <x-input-label for="password_confirmation" :value="'Confirmar contraseña'" class="text-gray-700 font-medium" />
                        <div class="md:col-span-2">
                            <x-text-input id="password_confirmation" class="block w-full bg-gray-50 border-gray-300 text-gray-900 focus:border-[var(--buyer)] focus:ring-[var(--buyer)] rounded-xl" type="password" name="password_confirmation" required />
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-8">
                        <a href="{{ route('login') }}" class="text-[var(--buyer)] hover:underline text-sm font-semibold">
                            ¿Ya tienes cuenta?
                        </a>
                        <x-primary-button class="bg-[var(--seller)] hover:bg-[var(--sellerhov)] text-white px-8 py-3 rounded-xl transition-colors shadow-lg">
                            Registrarse
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </form>
    </div>

</body>
</html>