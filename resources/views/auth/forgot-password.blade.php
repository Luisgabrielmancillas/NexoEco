<x-guest-layout>
    <x-slot name="title">Recuperar contraseña | NexoEco</x-slot>
    <h1 class="text-xl font-bold mb-4">Recuperar contraseña</h1>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Escribe el correo de tu cuenta para recibir un enlace y elegir una nueva contraseña.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo electrónico" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" maxlength="255" :aria-invalid="$errors->has('email') ? 'true' : null" :aria-describedby="$errors->has('email') ? 'email-error' : null" />
            <x-input-error id="email-error" :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Enviar enlace de recuperación
            </x-primary-button>
        </div>
    </form>
    <a class="inline-flex items-center min-h-11 mt-4 text-primary-700 underline" href="{{ route('login') }}">Volver a iniciar sesión</a>
</x-guest-layout>
