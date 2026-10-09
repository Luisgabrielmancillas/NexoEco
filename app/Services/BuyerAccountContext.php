<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class BuyerAccountContext
{
    private array $accounts = [];

    private ?\Illuminate\Http\Request $request = null;

    public function data(): array
    {
        if ($this->request !== request()) {
            $this->request = request();
            $this->accounts = [];
        }
        $user = Auth::user();
        if (! $user || $user->tieneTipo('administrador')) {
            return ['favoritosProductos' => [], 'favoritosTiendas' => [], 'notificacionesSinLeer' => 0, 'notificacionReciente' => null, 'ubicacionComprador' => $user ? null : session('buyer_location')];
        }

        return $this->accounts[$user->id] ??= [
            'ubicacionComprador' => $user->ubicacion_comprador,
            'favoritosProductos' => $user->productosFavoritos()->pluck('productos.id_producto')->all(),
            'favoritosTiendas' => $user->tiendasFavoritas()->pluck('tiendas.id_tienda')->all(),
            'notificacionesSinLeer' => $user->unreadNotifications()->count(),
            'notificacionReciente' => $user->notifications()->latest()->first()?->id,
        ];
    }
}
