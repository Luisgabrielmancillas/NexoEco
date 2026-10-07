<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\VerifyNexoEcoEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailVerificationCodeService
{
    public const EXPIRES_MINUTES = 10;

    public function send(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $sendKey = 'email-verification-send:'.$user->id;
        if (RateLimiter::tooManyAttempts($sendKey, 5)) {
            throw ValidationException::withMessages([
                'codigo' => 'Has solicitado varios códigos. Espera unos minutos antes de pedir otro.',
            ]);
        }

        $previous = Cache::get($this->key($user));
        do {
            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while ($previous && Hash::check($code, $previous['hash']));

        Cache::put($this->key($user), [
            'email' => $user->email,
            'hash' => Hash::make($code),
        ], now()->addMinutes(self::EXPIRES_MINUTES));

        $user->notify(new VerifyNexoEcoEmail($code));
        RateLimiter::hit($sendKey, 600);
    }

    public function verify(User $user, string $code): void
    {
        $attemptKey = 'email-verification-attempts:'.$user->id;
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            throw ValidationException::withMessages([
                'codigo' => 'Superaste el límite de intentos. Espera 10 minutos y solicita un nuevo código.',
            ]);
        }

        $record = Cache::get($this->key($user));
        if (! $record || $record['email'] !== $user->email) {
            throw ValidationException::withMessages([
                'codigo' => 'El código venció o ya no es válido. Solicita uno nuevo.',
            ]);
        }

        if (! Hash::check($code, $record['hash'])) {
            RateLimiter::hit($attemptKey, 600);
            if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
                Cache::forget($this->key($user));
            }
            throw ValidationException::withMessages([
                'codigo' => 'El código es incorrecto. Revisa los 4 dígitos del correo.',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        Cache::forget($this->key($user));
        RateLimiter::clear($attemptKey);
    }

    private function key(User $user): string
    {
        return 'email-verification-code:'.$user->id;
    }
}
