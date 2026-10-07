<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Services\ShopMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class SellerStoreController extends Controller
{
    public function dashboard(Request $request)
    {
        $tienda = $request->user()->tiendas()->orderBy('id_tienda')->first();
        if (! $tienda) {
            return redirect()->route('vendedor.tienda.create');
        }
        $tienda->loadCount(['productos' => fn ($q) => $q->publicados(), 'opiniones' => fn ($q) => $q->publicadas()])->loadAvg(['opiniones' => fn ($q) => $q->publicadas()], 'calificacion');
        $productIds = $tienda->productos()->select('id_producto');
        $favorites = DB::table('tiendas_favoritas')->where('id_tienda', $tienda->getKey())->count();
        $productFavorites = DB::table('productos_favoritos')->whereIn('id_producto', $productIds)->count();
        $productReviews = DB::table('opiniones')->whereNull('deleted_at')->whereIn('id_producto', $productIds)->count();
        $categorias = Categoria::query()->whereHas('productos', fn ($q) => $q->where('id_tienda', $tienda->getKey()))
            ->withCount(['productos' => fn ($q) => $q->where('id_tienda', $tienda->getKey())])->orderBy('nombre_categoria')->get();
        $populares = Producto::where('id_tienda', $tienda->getKey())->with(['categoria', 'imagenPrincipal'])
            ->select('productos.*')->selectSub(DB::table('productos_favoritos')->selectRaw('COUNT(*)')->whereColumn('productos_favoritos.id_producto', 'productos.id_producto'), 'favoritos_count')
            ->orderByDesc('favoritos_count')->orderByDesc('id_producto')->limit(4)->get();
        $solicitud = $request->user()->solicitudVendedor()->with('documentos')->first();

        return view('vendedor.dashboard', compact('tienda', 'favorites', 'productFavorites', 'productReviews', 'categorias', 'populares', 'solicitud'));
    }

    public function create(Request $request)
    {
        if ($request->user()->tiendas()->exists()) {
            return redirect()->route('vendedor.dashboard');
        }

        return view('vendedor.tienda-form', ['tienda' => null]);
    }

    public function edit(Request $request)
    {
        $tienda = $request->user()->tiendas()->orderBy('id_tienda')->first();

        return $tienda ? view('vendedor.tienda-form', compact('tienda')) : redirect()->route('vendedor.tienda.create');
    }

    public function store(StoreBusinessRequest $request, ShopMedia $media)
    {
        return $this->save($request, $media, true);
    }

    public function update(StoreBusinessRequest $request, ShopMedia $media)
    {
        return $this->save($request, $media, false);
    }

    private function save(StoreBusinessRequest $request, ShopMedia $media, bool $creating)
    {
        $uploaded = [];
        $old = [];
        $tienda = null;
        try {
            $saved = DB::transaction(function () use ($request, $media, $creating, &$uploaded, &$old, &$tienda) {
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $tienda = $request->user()->tiendas()->orderBy('id_tienda')->lockForUpdate()->first();
                if ($creating && $tienda) {
                    return false;
                }
                abort_if(! $creating && ! $tienda, 404);
                $data = $request->safe()->except(['logo', 'portada']);
                foreach ($data['horarios'] as &$day) {
                    $day['abierto'] = (bool) $day['abierto'];
                    if (! $day['abierto']) {
                        $day['inicio'] = $day['fin'] = null;
                    }
                }
                unset($day);
                $data['servicios'] = array_values(array_unique($data['servicios'] ?? []));
                if ($creating) {
                    $tienda = $request->user()->tiendas()->create($data + ['fecha_creacion' => now()]);
                } else {
                    $tienda->fill($data);
                }
                foreach (['logo' => 'logo_tienda', 'portada' => 'portada_tienda'] as $input => $column) {
                    if ($request->hasFile($input)) {
                        $old[] = $tienda->$column;
                        $tienda->$column = $uploaded[] = $media->upload($tienda, $request->file($input));
                    }
                }
                $tienda->save();

                return true;
            });
        } catch (Throwable $exception) {
            foreach ($uploaded as $url) {
                $media->remove($tienda, $url);
            }
            throw $exception;
        }
        foreach ($old as $url) {
            $media->remove($tienda, $url);
        }

        return redirect()->route('vendedor.dashboard')->with('success', $saved ? ($creating ? 'Tu tienda está lista. ¡Publica tu primer producto!' : 'Los datos de tu tienda se actualizaron.') : 'Ya tienes una tienda creada.');
    }
}
