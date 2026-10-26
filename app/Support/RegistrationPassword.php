<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class RegistrationPassword
{
    public const DESCRIPTION = 'Usa al menos 8 caracteres, una mayúscula, una minúscula, un número y un signo.';

    public const PATTERN = '(?=.*\p{Ll})(?=.*\p{Lu})(?=.*\p{N})(?=.*[\p{P}\p{S}]).{8,}';

    public static function rules(): array
    {
        return ['string', Password::min(8)->mixedCase()->numbers()->symbols(), 'regex:/[\p{P}\p{S}]/u'];
    }

    public static function messages(): array
    {
        return [
            'password.min' => 'La contraseña debe contener al menos 8 caracteres.',
            'password.mixed' => 'La contraseña debe incluir una mayúscula y una minúscula.',
            'password.numbers' => 'La contraseña debe incluir un número.',
            'password.symbols' => 'La contraseña debe incluir un signo.',
            'password.regex' => 'La contraseña debe incluir un signo.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
