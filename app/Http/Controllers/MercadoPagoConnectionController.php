<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoConnection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MercadoPagoConnectionController extends Controller
{
    public function connect(Request $request, MercadoPagoConnection $connection)
    {
        abort_unless($connection->configured(), 503, 'La conexión de Mercado Pago estará disponible en breve.');
        $state = Str::random(64);
        $verifier = Str::random(96);
        $request->session()->put('mp_oauth', ['state' => $state, 'verifier' => $verifier, 'user_id' => $request->user()->id, 'expires_at' => now()->addMinutes(10)->timestamp]);
        $query = http_build_query([
            'client_id' => config('mercadopago.client_id'), 'response_type' => 'code', 'platform_id' => 'mp',
            'redirect_uri' => route('vendedor.mercadopago.callback'), 'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='), 'code_challenge_method' => 'S256',
        ]);

        return redirect()->away('https://auth.mercadopago.com/authorization?'.$query);
    }

    public function callback(Request $request, MercadoPagoConnection $connection)
    {
        $attempt = $request->session()->pull('mp_oauth');
        abort_unless($attempt && is_string($request->query('state')) && hash_equals($attempt['state'], $request->query('state'))
            && $attempt['user_id'] === $request->user()->id && $attempt['expires_at'] >= now()->timestamp, 403);
        if ($request->query('error')) {
            return redirect()->route('vendedor.apartados.index')->withErrors(['mercadopago' => 'La vinculación se canceló. Puedes intentarlo de nuevo cuando quieras.']);
        }
        $request->validate(['code' => ['required', 'string', 'max:2048']]);
        try {
            $connection->exchange($request->user(), $request->query('code'), $attempt['verifier']);
        } catch (RequestException|ConnectionException $e) {
            return redirect()->route('vendedor.apartados.index')->withErrors(['mercadopago' => 'No pudimos vincular tu cuenta. Intenta nuevamente desde aquí.']);
        }

        return redirect()->route('vendedor.apartados.index')->with('success', 'Tu cuenta de Mercado Pago está conectada. Ya puedes activar apartados en tus productos.');
    }
}
