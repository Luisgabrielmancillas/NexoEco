<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Tienda;
use App\Services\ShopMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class SellerProductController extends Controller
{
    private function storeFor(Request $request): ?Tienda
    {
        return $request->user()->tiendas()->orderBy('id_tienda')->first();
    }

    public function index(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:150'], 'categoria' => ['nullable', 'integer', Rule::exists('categorias', 'id_categoria')]]);
        $tienda = $this->storeFor($request);
        if (! $tienda) {
            return redirect()->route('vendedor.tienda.create');
        }
        $search = trim((string) $request->query('q', ''));
        $productos = $tienda->productos()->with(['categoria', 'imagenPrincipal'])
            ->when($search !== '', fn ($q) => $q->where('nombre_producto', 'like', '%'.$search.'%'))
            ->when($request->filled('categoria'), fn ($q) => $q->where('id_categoria', $request->query('categoria')))
            ->orderByDesc('id_producto')->paginate(12)->withQueryString();
        $categorias = Categoria::whereHas('productos', fn ($q) => $q->where('id_tienda', $tienda->getKey()))->orderBy('nombre_categoria')->get();

        return view('vendedor.productos.index', compact('tienda', 'productos', 'categorias', 'search'));
    }

    public function create(Request $request)
    {
        if (! $tienda = $this->storeFor($request)) {
            return redirect()->route('vendedor.tienda.create');
        }

        if (! array_key_exists($tienda->giro ?? '', config('store-categories'))) {
            return redirect()->route('vendedor.tienda.edit')->with('success', 'Selecciona el giro de tu tienda antes de publicar productos.');
        }

        return view('vendedor.productos.form', ['tienda' => $tienda, 'producto' => null, 'categorias' => $this->categories($tienda)]);
    }

    public function edit(Request $request, Producto $producto)
    {
        abort_unless($producto->tienda->id_vendedor === $request->user()->id, 403);

        if (! array_key_exists($producto->tienda->giro ?? '', config('store-categories'))) {
            return redirect()->route('vendedor.tienda.edit')->with('success', 'Selecciona el giro de tu tienda antes de editar productos.');
        }

        return view('vendedor.productos.form', ['tienda' => $producto->tienda, 'producto' => $producto, 'categorias' => $this->categories($producto->tienda)]);
    }

    private function categories(Tienda $tienda)
    {
        return Categoria::whereIn('nombre_categoria', config('store-categories')[$tienda->giro] ?? [])->orderBy('nombre_categoria')->get();
    }

    public function store(Request $request, ShopMedia $media)
    {
        if (! $tienda = $this->storeFor($request)) {
            return redirect()->route('vendedor.tienda.create');
        }

        return $this->save($request, $media, $tienda, null);
    }

    public function update(Request $request, Producto $producto, ShopMedia $media)
    {
        abort_unless($producto->tienda->id_vendedor === $request->user()->id, 403);

        return $this->save($request, $media, $producto->tienda, $producto);
    }

    private function save(Request $request, ShopMedia $media, Tienda $tienda, ?Producto $producto)
    {
        $creating = ! $producto;
        $request->validate(['creation_token' => [$creating ? 'required' : 'nullable', 'uuid']], [
            'creation_token.required' => 'Abre de nuevo el formulario de producto para continuar.',
            'creation_token.uuid' => 'El formulario no es válido. Abre de nuevo la página de creación.',
        ]);
        $creationCode = 'NE-'.$tienda->getKey().'-'.$request->input('creation_token');
        if ($creating && $tienda->productos()->withTrashed()->where('codigo_producto', $creationCode)->exists()) {
            return redirect()->route('vendedor.productos.index')->with('success', 'Este producto ya se guardó.');
        }
        $data = $request->validate([
            'nombre_producto' => ['required', 'string', 'max:150'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'precio' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'apartados_activos' => ['sometimes', 'boolean'],
            'apartado_monto' => ['exclude_unless:apartados_activos,1', 'required', 'numeric', 'min:0.01', 'lt:precio', 'decimal:0,2'],
            'apartado_condiciones' => ['exclude_unless:apartados_activos,1', 'required', 'string', 'max:3000'],
            'id_categoria' => ['required', 'integer', Rule::in($this->categories($tienda)->modelKeys())],
            'imagen' => [$producto ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=64,min_height=64,max_width=6000,max_height=6000'],
        ], [
            'id_categoria.required' => 'Selecciona una subcategoría para el producto.',
            'id_categoria.in' => 'Selecciona una subcategoría que corresponda al giro de tu tienda.',
            'apartado_monto.required' => 'Indica el anticipo para permitir apartados.',
            'apartado_monto.lt' => 'El anticipo debe ser menor que el precio del producto.',
            'apartado_condiciones.required' => 'Explica las condiciones antes de activar los apartados.',
        ]);
        $uploaded = null;
        if ($request->boolean('apartados_activos') && ! app(\App\Services\MercadoPagoConnection::class)->available($request->user()->mercadoPagoAccount()->first())) {
            throw \Illuminate\Validation\ValidationException::withMessages(['apartados_activos' => 'Conecta tu cuenta de Mercado Pago desde Apartados recibidos antes de activar esta opción.']);
        }
        try {
            DB::transaction(function () use ($request, $media, $tienda, &$producto, &$uploaded, $data, $creating, $creationCode) {
                Tienda::whereKey($tienda->getKey())->lockForUpdate()->firstOrFail();
                if ($creating && $tienda->productos()->withTrashed()->where('codigo_producto', $creationCode)->exists()) {
                    return;
                }
                // Scope every field to the authenticated seller's store; never accept ownership or SKU from the form.
                $category = $data['id_categoria'] ?? null;
                $fields = ['nombre_producto' => $data['nombre_producto'], 'descripcion' => $data['descripcion'], 'precio' => $data['precio'], 'id_categoria' => $category];
                $fields += [
                    'apartados_activos' => $request->boolean('apartados_activos'),
                    'apartado_monto' => $data['apartado_monto'] ?? null,
                    'apartado_condiciones' => $data['apartado_condiciones'] ?? null,
                ];
                if ($request->hasFile('imagen')) {
                    $fields['imagen_url'] = $uploaded = $media->upload($tienda, $request->file('imagen'));
                }
                if ($producto) {
                    $producto = Producto::lockForUpdate()->findOrFail($producto->getKey());
                    $producto->update($fields);
                    if ($uploaded) {
                        // Replacing the main image must also take precedence over legacy gallery images.
                        $producto->imagenes()->update(['es_principal' => false]);
                        $producto->imagenes()->create(['imagen_url' => $uploaded, 'es_principal' => true, 'orden' => 0, 'fecha_creacion' => now()]);
                    }
                } else {
                    $producto = $tienda->productos()->create($fields + ['codigo_producto' => $creationCode, 'fecha_publicacion' => now()]);
                }
                $producto->forceFill(['estado_moderacion' => 'aprobada', 'motivo_moderacion' => null, 'id_moderador' => null, 'fecha_moderacion' => null, 'revision_contenido' => ($producto->revision_contenido ?? 1) + 1])->save();
                if ($creating) {
                    app(\App\Services\StoreAudience::class)->productPublished($producto);
                }
            });
        } catch (Throwable $exception) {
            $media->remove($tienda, $uploaded);
            throw $exception;
        }
        // Gallery files remain available while referenced by historical galleries.

        return redirect()->route('vendedor.productos.index')->with('success', 'El producto se guardó y ya aparece en el marketplace.');
    }
}
