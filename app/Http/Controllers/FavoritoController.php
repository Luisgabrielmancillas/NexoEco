<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoritoController extends Controller
{
    public function index(Request $request, string $tipo): View
    {
        $items = $tipo === 'productos'
            ? $request->user()->productosFavoritos()->publicados()->with(['tienda', 'categoria', 'imagenPrincipal'])->orderByPivot('created_at', 'desc')->paginate(12)
            : $request->user()->tiendasFavoritas()->withCount(['productos' => fn ($q) => $q->publicados()])->orderByPivot('created_at', 'desc')->paginate(12);

        return view('comprador.favoritos', compact('items', 'tipo'));
    }

    public function store(Request $request, string $tipo, int $id): RedirectResponse|JsonResponse
    {
        $model = $tipo === 'productos' ? Producto::publicados()->findOrFail($id) : Tienda::findOrFail($id);
        $relation = $tipo === 'productos' ? $request->user()->productosFavoritos() : $request->user()->tiendasFavoritas();
        // Composite primary keys make repeated saves idempotent, including concurrent requests.
        $request->user()->getConnection()->table($relation->getTable())->insertOrIgnore([
            'id_usuario' => $request->user()->id,
            $tipo === 'productos' ? 'id_producto' : 'id_tienda' => $model->getKey(),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return $this->response($request, true, 'Agregado a tus favoritos.');
    }

    public function destroy(Request $request, string $tipo, int $id): RedirectResponse|JsonResponse
    {
        $relation = $tipo === 'productos' ? $request->user()->productosFavoritos() : $request->user()->tiendasFavoritas();
        $relation->detach($id);

        return $this->response($request, false, 'Eliminado de tus favoritos.');
    }

    private function response(Request $request, bool $saved, string $message): RedirectResponse|JsonResponse
    {
        return $request->expectsJson()
            ? response()->json(['saved' => $saved, 'message' => $message])
            : back()->with('status', $message);
    }
}
