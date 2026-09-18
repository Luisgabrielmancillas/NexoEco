<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarketplaceIndexRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceController extends Controller
{
    /**
     * Marketplace público principal de NexoEco.
     */
    public function index(
        MarketplaceIndexRequest $request
    ): View {
        /*
        |--------------------------------------------------------------------------
        | FILTROS VALIDADOS
        |--------------------------------------------------------------------------
        */

        $datos = $request->validated();

        $busqueda = $datos['q'] ?? null;

        $categoriaSeleccionada = isset($datos['categoria'])
            ? (int) $datos['categoria']
            : null;


        /*
        |--------------------------------------------------------------------------
        | CATEGORÍAS
        |--------------------------------------------------------------------------
        */

        $categorias = Categoria::query()
            ->select([
                'id_categoria',
                'nombre_categoria',
                'descripcion_categoria',
            ])
            ->orderBy('nombre_categoria')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS
        |--------------------------------------------------------------------------
        */

        $productos = Producto::query()

            /*
            |--------------------------------------------------------------------------
            | COLUMNAS NECESARIAS
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | RELACIONES
            |--------------------------------------------------------------------------
            |
            | Evitamos consultas N+1.
            |
            */

            ->with([
                'tienda' => function ($query) {
                    $query->select([
                        'id_tienda',
                        'nombre_tienda',
                        'logo_tienda',
                    ]);
                },

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


            /*
            |--------------------------------------------------------------------------
            | BÚSQUEDA
            |--------------------------------------------------------------------------
            */

            ->when(
                $busqueda,
                function (
                    Builder $query,
                    string $busqueda
                ) {
                    $query->where(
                        function (Builder $subQuery) use ($busqueda) {

                            $subQuery
                                ->where(
                                    'nombre_producto',
                                    'like',
                                    '%' . $busqueda . '%'
                                )

                                ->orWhere(
                                    'codigo_producto',
                                    'like',
                                    '%' . $busqueda . '%'
                                )

                                ->orWhere(
                                    'descripcion',
                                    'like',
                                    '%' . $busqueda . '%'
                                )

                                ->orWhereHas(
                                    'tienda',
                                    function (Builder $tiendaQuery) use ($busqueda) {

                                        $tiendaQuery->where(
                                            'nombre_tienda',
                                            'like',
                                            '%' . $busqueda . '%'
                                        );
                                    }
                                )

                                ->orWhereHas(
                                    'categoria',
                                    function (Builder $categoriaQuery) use ($busqueda) {

                                        $categoriaQuery->where(
                                            'nombre_categoria',
                                            'like',
                                            '%' . $busqueda . '%'
                                        );
                                    }
                                );
                        }
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | CATEGORÍA
            |--------------------------------------------------------------------------
            */

            ->when(
                $categoriaSeleccionada,
                function (
                    Builder $query,
                    int $categoriaId
                ) {
                    $query->where(
                        'id_categoria',
                        $categoriaId
                    );
                }
            )


            /*
            |--------------------------------------------------------------------------
            | ORDEN
            |--------------------------------------------------------------------------
            */

            ->orderByDesc('fecha_publicacion')
            ->orderByDesc('id_producto')


            /*
            |--------------------------------------------------------------------------
            | PAGINACIÓN
            |--------------------------------------------------------------------------
            */

            ->paginate(24)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | VISTA
        |--------------------------------------------------------------------------
        */

        return view(
            'marketplace.index',
            compact(
                'categorias',
                'productos',
                'busqueda',
                'categoriaSeleccionada'
            )
        );
    }
}