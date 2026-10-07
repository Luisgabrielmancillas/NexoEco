<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\StoreSolicitudVendedorRequest;
use Illuminate\Validation\Rules;

class StoreSellerRegistrationRequest extends StoreSolicitudVendedorRequest
{
    public function authorize(): bool
    {
        return ! auth()->check();
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'nombre_completo' => trim((string) $this->input('nombre_completo')),
        ]);
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo ya tiene una cuenta. Inicia sesión para continuar tu registro como vendedor.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe contener al menos :min caracteres.',
        ]);
    }
}
