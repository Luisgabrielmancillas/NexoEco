<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Tienda;
use App\Notifications\BuyerActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpinionController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['tipo' => ['nullable', 'in:productos,tiendas']]);
        $tipo = $data['tipo'] ?? null;
        $opiniones = $request->user()->opiniones()->with(['producto.tienda', 'tienda'])
            ->when($tipo, fn ($query) => $query->whereNotNull($tipo === 'productos' ? 'id_producto' : 'id_tienda'))
            ->latest()->paginate(12)->withQueryString();

        return view('comprador.opiniones', compact('opiniones', 'tipo'));
    }

    public function store(Request $request, string $tipo, int $id): RedirectResponse
    {
        $model = $tipo === 'productos' ? Producto::publicados()->findOrFail($id) : Tienda::findOrFail($id);
        $data = $request->validate([
            'calificacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'calificacion.between' => 'Selecciona una calificación entre 1 y 5 estrellas.',
            'calificacion.required' => 'Selecciona tu calificación.',
            'comentario.required' => 'Escribe tu opinión.',
            'comentario.min' => 'Tu opinión debe contener al menos 5 caracteres.',
            'comentario.max' => 'Tu opinión debe contener como máximo 2000 caracteres.',
        ]);
        $opinion = $request->user()->opiniones()->withTrashed()->firstOrNew([
            'id_producto' => $tipo === 'productos' ? $id : null,
            'id_tienda' => $tipo === 'tiendas' ? $id : null,
        ]);
        $opinion->fill($data)->forceFill(['estado_moderacion' => 'aprobada', 'deleted_at' => null, 'motivo_moderacion' => null, 'id_moderador' => null, 'fecha_moderacion' => null, 'revision_contenido' => ($opinion->revision_contenido ?? 1) + 1])->save();
        $url = route($tipo === 'productos' ? 'productos.show' : 'tiendas.show', $model).'#opiniones';
        $request->user()->notify(new BuyerActivityNotification('Tu opinión fue publicada', 'Puedes consultar y administrar tu reseña en Mis opiniones.', route('comprador.opiniones')));

        return redirect($url)->with('status', 'Tu opinión se publicó correctamente.');
    }

    public function destroy(Request $request, int $opinion): RedirectResponse
    {
        $request->user()->opiniones()->findOrFail($opinion)->delete();

        return back()->with('status', 'Tu opinión fue eliminada.');
    }
}
