<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class ProductoController extends Controller
{
    /**
     * Mostrar la ficha pública de un producto.
     */
    public function show(Producto $producto): View
    {
        /*
        |--------------------------------------------------------------------------
        | RELACIONES
        |--------------------------------------------------------------------------
        |
        | Cargamos solamente las relaciones necesarias para la ficha.
        |
        */

        $producto->load([
            'tienda' => function ($query) {
                $query->select([
                    'id_tienda',
                    'id_vendedor',
                    'nombre_tienda',
                    'descripcion_tienda',
                    'logo_tienda',
                    'fecha_creacion',
                ]);
            },

            'categoria' => function ($query) {
                $query->select([
                    'id_categoria',
                    'nombre_categoria',
                    'descripcion_categoria',
                ]);
            },

            'imagenes' => function ($query) {
                $query->select([
                    'id_imagen',
                    'id_producto',
                    'imagen_url',
                    'es_principal',
                    'orden',
                    'fecha_creacion',
                ])
                ->orderByDesc('es_principal')
                ->orderBy('orden')
                ->orderBy('id_imagen');
            },
        ]);


        /*
        |--------------------------------------------------------------------------
        | GALERÍA
        |--------------------------------------------------------------------------
        |
        | producto_imagenes tiene prioridad.
        |
        | Si todavía no hay imágenes nuevas utilizamos productos.imagen_url
        | para mantener compatibilidad con los datos existentes.
        |
        */

        $imagenes = $producto->imagenes
            ->map(function ($imagen) use ($producto) {
                return [
                    'url' => $this->resolverImagen(
                        $imagen->imagen_url
                    ),

                    'alt' => $producto->nombre_producto,

                    'principal' => (bool) $imagen->es_principal,
                ];
            })
            ->filter(
                fn (array $imagen) =>
                    !empty($imagen['url'])
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | IMAGEN ANTIGUA COMO RESPALDO
        |--------------------------------------------------------------------------
        */

        if ($imagenes->isEmpty() && $producto->imagen_url) {

            $imagenAnterior = $this->resolverImagen(
                $producto->imagen_url
            );

            if ($imagenAnterior) {
                $imagenes->push([
                    'url' => $imagenAnterior,
                    'alt' => $producto->nombre_producto,
                    'principal' => true,
                ]);
            }
        }


        return view(
            'marketplace.producto',
            compact(
                'producto',
                'imagenes'
            )
        );
    }


    /**
     * Resolver una ruta almacenada en la base de datos
     * hacia una URL utilizable por el navegador.
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


        /*
        |--------------------------------------------------------------------------
        | URL EXTERNA
        |--------------------------------------------------------------------------
        |
        | Se mantiene por compatibilidad con datos anteriores.
        |
        | Las nuevas imágenes de NexoEco deberían almacenarse localmente.
        |
        */

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


        /*
        |--------------------------------------------------------------------------
        | URL ABSOLUTA LOCAL
        |--------------------------------------------------------------------------
        */

        if (Str::startsWith($ruta, '/')) {
            return asset(
                ltrim($ruta, '/')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | YA INCLUYE STORAGE/
        |--------------------------------------------------------------------------
        */

        if (Str::startsWith($ruta, 'storage/')) {
            return asset($ruta);
        }


        /*
        |--------------------------------------------------------------------------
        | RUTA RELATIVA DE STORAGE
        |--------------------------------------------------------------------------
        */

        return asset(
            'storage/' . ltrim($ruta, '/')
        );
    }
}