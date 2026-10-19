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
        app(\App\Services\SellerAnalytics::class)->visit(request(), $tienda);
        $storeSubscription = $tienda->user ? app(\App\Services\SellerBenefits::class)->subscription($tienda->user) : null;
        $storeDesign = $storeSubscription ? \App\Models\SellerStoreDesign::where('user_id', $tienda->id_vendedor)->first() : null;
        $following = auth()->user()?->followedStores()->where('tiendas.id_tienda', $tienda->getKey())->exists() ?? false;
        $storeUpdates = $tienda->updates()->where('status', 'published')->latest('published_at')->limit(5)->get();
        app(\App\Services\SellerPromotion::class)->record(request(), 'store', $tienda, 'visits');
        /*
        |--------------------------------------------------------------------------
        | PRODUCTOS DE LA TIENDA
        |--------------------------------------------------------------------------
        |
        | No usamos $tienda->productos directamente porque necesitamos
        | paginación y eager loading.
        |
        */

        $productos = Producto::publicados()
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
                'activeOffer',
                'tienda:id_tienda,nombre_tienda',
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
            ->selectSub(\Illuminate\Support\Facades\DB::table('product_pins')->selectRaw('COUNT(*)')->where('subscription_id', $storeSubscription?->id ?? '')->whereColumn('id_producto', 'productos.id_producto'), 'is_pinned')
            ->orderByDesc('is_pinned')
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

        $tienda->loadCount(['opiniones' => fn ($q) => $q->publicadas()])->loadAvg(['opiniones' => fn ($q) => $q->publicadas()], 'calificacion');
        $opiniones = $tienda->opiniones()->publicadas()->with('usuario:id,name,nombre_completo')->latest()->paginate(5, ['*'], 'pagina_opiniones');
        $miOpinion = auth()->user()?->opiniones()->where('id_tienda', $tienda->id_tienda)->first();

        return view(
            'marketplace.tienda',
            compact(
                'tienda',
                'productos',
                'logoTienda',
                'opiniones',
                'miOpinion',
                'storeSubscription',
                'storeDesign',
                'following',
                'storeUpdates'
            )
        );
    }

    /**
     * Convertir una ruta almacenada en BD en una URL pública.
     */
    private function resolverImagen(?string $ruta): ?string
    {
        if (! $ruta) {
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
            'storage/'.ltrim($ruta, '/')
        );
    }
}
