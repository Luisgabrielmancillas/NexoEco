<?php

namespace App\Http\Controllers;

use App\Models\Opinion;
use App\Models\Producto;
use App\Models\Tienda;
use Illuminate\Http\Request;

class AdminCatalogController extends Controller
{
    public function index(Request $request, string $tipo)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $q = $data['q'] ?? null;
        $items = $tipo === 'productos'
            ? Producto::with(['tienda', 'categoria', 'imagenPrincipal'])->when($q, fn ($query) => $query->where(fn ($query) => $query->where('nombre_producto', 'like', '%'.$q.'%')->orWhere('codigo_producto', 'like', '%'.$q.'%')->orWhereHas('tienda', fn ($query) => $query->where('nombre_tienda', 'like', '%'.$q.'%'))))->orderByDesc('id_producto')->paginate(15)->withQueryString()
            : Tienda::with('user')->withCount('productos')->when($q, fn ($query) => $query->where('nombre_tienda', 'like', '%'.$q.'%'))->orderByDesc('id_tienda')->paginate(15)->withQueryString();

        return view('administrador.catalogo', compact('items', 'tipo'));
    }

    public function reviews(Request $request)
    {
        $data = $request->validate(['tipo' => ['nullable', 'in:productos,tiendas']]);
        $opiniones = Opinion::with(['usuario', 'producto', 'tienda'])->when($data['tipo'] ?? null, fn ($query, $tipo) => $query->whereNotNull($tipo === 'productos' ? 'id_producto' : 'id_tienda'))->latest()->paginate(15)->withQueryString();

        return view('administrador.opiniones', compact('opiniones'));
    }

    public function product(Producto $producto)
    {
        $producto->load(['tienda.user', 'categoria', 'imagenes'])->loadCount('opiniones')->loadAvg('opiniones', 'calificacion');
        $opiniones = $producto->opiniones()->with('usuario')->latest()->paginate(10, ['*'], 'pagina_opiniones');

        return view('administrador.producto', compact('producto', 'opiniones'));
    }

    public function store(Tienda $tienda)
    {
        $tienda->load('user')->loadCount(['productos', 'opiniones'])->loadAvg('opiniones', 'calificacion');
        $productos = $tienda->productos()->with(['tienda', 'categoria', 'imagenPrincipal'])->orderByDesc('id_producto')->paginate(12);
        $opiniones = $tienda->opiniones()->with('usuario')->latest()->paginate(10, ['*'], 'pagina_opiniones');

        return view('administrador.tienda', compact('tienda', 'productos', 'opiniones'));
    }
}
