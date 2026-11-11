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
    public function sellerIndex(Request $request): View
    {
        $opiniones = \App\Models\Opinion::with(['usuario', 'producto.tienda', 'tienda'])
            ->where(function ($query) use ($request) {
                $query->whereHas('tienda', fn ($q) => $q->where('id_vendedor', $request->user()->id))
                    ->orWhereHas('producto.tienda', fn ($q) => $q->where('id_vendedor', $request->user()->id));
            })->latest()->paginate(12);

        return view('vendedor.opiniones', compact('opiniones'));
    }

    public function reply(Request $request, \App\Models\Opinion $opinion): RedirectResponse
    {
        $store = $opinion->tienda ?? $opinion->producto?->tienda;
        abort_unless($store && $store->id_vendedor === $request->user()->id, 403);
        abort_if($opinion->id_usuario === $request->user()->id, 403);
        $data = $request->validate(['respuesta_vendedor' => ['required', 'string', 'min:5', 'max:2000']]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($opinion, $data, $store) {
            $opinion->forceFill($data + ['respuesta_fecha' => now()])->save();
            $opinion->usuario->notify(new BuyerActivityNotification('El vendedor respondió tu opinión', $store->nombre_tienda.' respondió a tu reseña.', route('comprador.opiniones')));
        });

        return back()->with('success', 'Tu respuesta se publicó.');
    }

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
        $store = $tipo === 'productos' ? $model->tienda : $model;
        if ($store->id_vendedor !== $request->user()->id) {
            $store->user?->notify(new BuyerActivityNotification('Nueva opinión en tu tienda', 'Un comprador publicó o actualizó una reseña. Puedes responder desde tu historial.', route('vendedor.opiniones')));
        }
        $request->user()->notify(new BuyerActivityNotification('Tu opinión fue publicada', 'Puedes consultar y administrar tu reseña en Mis opiniones.', route('comprador.opiniones')));

        return redirect($url)->with('status', 'Tu opinión se publicó correctamente.');
    }

    public function destroy(Request $request, int $opinion): RedirectResponse
    {
        $request->user()->opiniones()->findOrFail($opinion)->delete();

        return back()->with('status', 'Tu opinión fue eliminada.');
    }
}
