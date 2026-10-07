<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BuyerLocationController extends Controller
{
    public function update(Request $request)
    {
        abort_if($request->user()?->tieneTipo('administrador'), 403);
        $data = $request->validateWithBag('buyerLocation', [
            'ciudad' => ['required', 'string', 'max:100'],
            'estado' => ['required', 'string', 'max:100'],
            'colonia' => ['nullable', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:250'],
            'codigo_postal' => ['nullable', 'regex:/^[0-9]{5}$/'],
            'latitud' => ['nullable', 'required_with:longitud', 'numeric', 'between:-90,90'],
            'longitud' => ['nullable', 'required_with:latitud', 'numeric', 'between:-180,180'],
        ], [
            'ciudad.required' => 'Escribe tu ciudad o municipio.',
            'estado.required' => 'Escribe tu estado.',
            'codigo_postal.regex' => 'El código postal debe tener 5 dígitos.',
            'max' => 'El campo :attribute es demasiado largo.',
        ]);
        if ($request->user()) {
            $request->user()->forceFill(['ubicacion_comprador' => $data])->save();
        } else {
            $request->session()->put('buyer_location', $data);
        }
        $label = implode(', ', array_filter([$data['colonia'] ?? null, $data['ciudad']]));
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tu ubicación se guardó.', 'label' => $label]);
        }

        return redirect()->route($request->user() ? 'comprador.dashboard' : 'marketplace.index')->with('status', 'Tu ubicación se guardó.');
    }
}
