<?php

namespace App\Services;

use App\Models\Apartado;
use App\Models\MercadoPagoAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MercadoPagoConnection
{
    public function configured(): bool
    {
        return filled(config('mercadopago.client_id')) && filled(config('mercadopago.client_secret'));
    }

    public function available(?MercadoPagoAccount $account): bool
    {
        return $this->configured() && $account && $account->live_mode === ! (bool) config('mercadopago.sandbox');
    }

    public function exchange(User $user, string $code, string $verifier): MercadoPagoAccount
    {
        $data = Http::acceptJson()->timeout(20)->post('https://api.mercadopago.com/oauth/token', [
            'client_id' => config('mercadopago.client_id'), 'client_secret' => config('mercadopago.client_secret'),
            'grant_type' => 'authorization_code', 'code' => $code, 'code_verifier' => $verifier,
            'redirect_uri' => route('vendedor.mercadopago.callback'), 'test_token' => (bool) config('mercadopago.sandbox'),
        ])->throw()->json();
        $this->validateTokens($data);

        return DB::transaction(function () use ($user, $data) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $previous = $user->mercadoPagoAccount;
            if ($previous && $previous->mp_user_id !== (string) $data['user_id'] && Apartado::where('seller_id', $user->id)->where('provider', 'mercadopago')->exists()) {
                throw ValidationException::withMessages(['mercadopago' => 'Vuelve a vincular la misma cuenta: hay apartados asociados a ella.']);
            }
            if (MercadoPagoAccount::where('mp_user_id', (string) $data['user_id'])->where('user_id', '!=', $user->id)->exists()) {
                throw ValidationException::withMessages(['mercadopago' => 'Esta cuenta ya está vinculada a otro vendedor.']);
            }

            return MercadoPagoAccount::updateOrCreate(['user_id' => $user->id], [
                'mp_user_id' => (string) $data['user_id'], 'access_token' => $data['access_token'], 'refresh_token' => $data['refresh_token'],
                'expires_at' => now()->addSeconds((int) $data['expires_in']), 'live_mode' => (bool) $data['live_mode'],
            ]);
        });
    }

    private function validateTokens(array $data): void
    {
        if (empty($data['access_token']) || empty($data['refresh_token']) || empty($data['user_id']) || empty($data['expires_in']) || ! array_key_exists('live_mode', $data)) {
            throw ValidationException::withMessages(['mercadopago' => 'Mercado Pago no devolvió una conexión válida. Intenta vincular la cuenta de nuevo.']);
        }
        if ((bool) $data['live_mode'] !== ! (bool) config('mercadopago.sandbox')) {
            throw ValidationException::withMessages(['mercadopago' => 'La cuenta pertenece a otro entorno de Mercado Pago.']);
        }
    }

    public function token(MercadoPagoAccount $account): string
    {
        return DB::transaction(function () use ($account) {
            $account = MercadoPagoAccount::whereKey($account->id)->lockForUpdate()->firstOrFail();
            if ($account->expires_at->lte(now()->addMinutes(5))) {
                $data = Http::acceptJson()->timeout(20)->post('https://api.mercadopago.com/oauth/token', [
                    'grant_type' => 'refresh_token', 'client_id' => config('mercadopago.client_id'),
                    'client_secret' => config('mercadopago.client_secret'), 'refresh_token' => $account->refresh_token,
                ])->throw()->json();
                $this->validateTokens($data);
                if ((string) $data['user_id'] !== $account->mp_user_id) {
                    throw ValidationException::withMessages(['mercadopago' => 'La cuenta receptora no coincide con la conexión guardada.']);
                }
                $account->update(['access_token' => $data['access_token'], 'refresh_token' => $data['refresh_token'], 'expires_at' => now()->addSeconds((int) $data['expires_in'])]);
            }

            return $account->access_token;
        });
    }
}
