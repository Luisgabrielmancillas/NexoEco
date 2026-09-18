<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class TiendaController extends Controller
{
    /**
     * Mostrar la página pública de una tienda.
     */
    public function show(Tienda $tienda): View
    {
        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS DE LA TIENDA
        |--------------------------------------------------------------------------
        |
        | No usamos $tienda->productos directamente porque necesitamos
        | paginación y eager loading.
        |
        */

        $productos = Producto::query()
            ->select([
                'id_producto',
                'id_tienda',
                'id_categoria',
                'codigo_producto',
                'nombre_producto',
                'descripcion',
                'precio',
                'imagen_url',
                'fecha_publicacion',
            ])
            ->where(
                'id_tienda',
                $tienda->id_tienda
            )
            ->with([
                'categoria' => function ($query) {
                    $query->select([
                        'id_categoria',
                        'nombre_categoria',
                    ]);
                },

                'imagenPrincipal' => function ($query) {
                    $query->select([
                        'id_imagen',
                        'id_producto',
                        'imagen_url',
                        'es_principal',
                        'orden',
                    ]);
                },
            ])
            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_producto')
            ->paginate(24)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | RESOLVER IMÁGENES
        |--------------------------------------------------------------------------
        */

        $productos->getCollection()->transform(
            function (Producto $producto) {

                $ruta =
                    $producto->imagenPrincipal?->imagen_url
                    ?: $producto->imagen_url;

                $producto->setAttribute(
                    'imagen_resuelta',
                    $this->resolverImagen($ruta)
                );

                return $producto;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        $logoTienda = $this->resolverImagen(
            $tienda->logo_tienda
        );


        return view(
            'marketplace.tienda',
            compact(
                'tienda',
                'productos',
                'logoTienda'
            )
        );
    }


    /**
     * Convertir una ruta almacenada en BD en una URL pública.
     */
    private function resolverImagen(?string $ruta): ?string
    {
        if (!$ruta) {
            return null;
        }

        $ruta = trim($ruta);

        if ($ruta === '') {
            return null;
        }

        if (
            Str::startsWith(
                $ruta,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return $ruta;
        }

        if (Str::startsWith($ruta, '/')) {
            return asset(
                ltrim($ruta, '/')
            );
        }

        if (Str::startsWith($ruta, 'storage/')) {
            return asset($ruta);
        }

        return asset(
            'storage/' . ltrim($ruta, '/')
        );
    }
}