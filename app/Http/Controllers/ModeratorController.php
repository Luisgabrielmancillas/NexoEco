<?php

namespace App\Http\Controllers;

use App\Models\Opinion;
use App\Models\Producto;
use App\Models\RegistroActividad;
use App\Models\ReporteContenido;
use App\Models\SolicitudVendedor;
use App\Models\User;
use App\Notifications\BuyerActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ModeratorController extends Controller
{
    private function counts(): array
    {
        return [
            'publicaciones' => Producto::count(),
            'opiniones' => Opinion::count(),
            'vendedores' => SolicitudVendedor::where('estado', 'en_revision')->count(),
            'reportes' => ReporteContenido::where('estado', 'abierto')->count(),
        ];
    }

    private function actionsFor(array $actions, $day): int
    {
        return RegistroActividad::important()->whereIn('accion', $actions)->whereDate('registrada_en', $day)->count();
    }

    private function moderationActivity()
    {
        return RegistroActividad::important()->whereIn('ruta', ['moderador.contenido.destroy', 'moderador.contenido.review', 'moderador.solicitudes.review', 'moderador.reportes.resolve', 'admin.solicitudes.review'])->orderByDesc('registrada_en')->orderByDesc('id');
    }

    public function dashboard()
    {
        $counts = $this->counts();
        $deletedToday = $this->actionsFor(['Eliminó una publicación', 'Eliminó una opinión'], today());
        $resolvedToday = ReporteContenido::where('estado', 'resuelto')->whereDate('fecha_resolucion', today())->count();
        $recentReports = ReporteContenido::with('usuario')->where('estado', 'abierto')->latest()->orderByDesc('id')->limit(5)->get();
        $pendingSellers = SolicitudVendedor::with('usuario')->withCount('documentos')->where('estado', 'en_revision')->orderBy('fecha_solicitud')->orderBy('id_solicitud')->limit(5)->get();
        $recentActivity = $this->moderationActivity()->limit(6)->get();

        return view('moderador.dashboard', compact('counts', 'deletedToday', 'resolvedToday', 'recentReports', 'pendingSellers', 'recentActivity') + ['pageTitle' => 'Resumen']);
    }

    public function publications(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:150'], 'estado' => ['nullable', 'in:publicada,eliminada,reportada'], 'seleccion' => ['nullable', 'integer', 'min:1']]);
        $counts = $this->counts();
        $productos = Producto::with(['tienda.user', 'categoria', 'imagenPrincipal'])
            ->when($data['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->where('nombre_producto', 'like', '%'.$term.'%')->orWhereHas('tienda', fn ($q) => $q->where('nombre_tienda', 'like', '%'.$term.'%'))))
            ->when(($data['estado'] ?? null) === 'eliminada', fn ($q) => $q->onlyTrashed())
            ->when(($data['estado'] ?? null) === 'reportada', fn ($q) => $q->whereIn('id_producto', ReporteContenido::where('tipo', 'productos')->where('estado', 'abierto')->select('id_contenido')))
            ->orderByDesc('fecha_publicacion')->orderByDesc('id_producto')->paginate(10)->withQueryString();
        $seleccionado = isset($data['seleccion']) ? Producto::withTrashed()->with(['tienda.user', 'categoria', 'imagenes', 'imagenPrincipal'])->findOrFail($data['seleccion']) : $productos->first();
        $pageTitle = 'Publicaciones';

        return view('moderador.publicaciones', compact('counts', 'productos', 'seleccionado', 'pageTitle'));
    }

    public function opinions(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:150'], 'estado' => ['nullable', 'in:publicada,eliminada'], 'seleccion' => ['nullable', 'integer', 'min:1']]);
        $opiniones = Opinion::with(['usuario', 'producto.tienda', 'tienda'])
            ->when($data['seleccion'] ?? null, fn ($q, $id) => $q->whereKey($id))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->where('comentario', 'like', '%'.$term.'%'))
            ->when(($data['estado'] ?? null) === 'eliminada', fn ($q) => $q->onlyTrashed())
            ->when(isset($data['seleccion']) && ! isset($data['estado']), fn ($q) => $q->withTrashed())
            ->latest()->paginate(12)->withQueryString();

        return view('moderador.opiniones', ['opiniones' => $opiniones, 'counts' => $this->counts(), 'pageTitle' => 'Opiniones y comentarios']);
    }

    public function sellers(Request $request)
    {
        $data = $request->validate(['estado' => ['nullable', 'in:en_revision,aprobada,rechazada,requiere_correccion,pendiente_documentos']]);
        $solicitudes = SolicitudVendedor::with('usuario')->withCount('documentos')->when($data['estado'] ?? null, fn ($q, $state) => $q->where('estado', $state))->orderByDesc('fecha_solicitud')->paginate(12)->withQueryString();
        $vendedores = User::whereHas('tipos_usuario', fn ($q) => $q->whereRaw('LOWER(nombre_tipo) = ?', ['vendedor']))->with('tiendas')->latest()->limit(8)->get();

        return view('moderador.vendedores', ['solicitudes' => $solicitudes, 'vendedores' => $vendedores, 'counts' => $this->counts(), 'pageTitle' => 'Vendedores']);
    }

    public function application(SolicitudVendedor $solicitud)
    {
        $solicitud->load(['usuario', 'documentos', 'moderador']);

        return view('moderador.solicitud', ['solicitud' => $solicitud, 'counts' => $this->counts(), 'pageTitle' => 'Documentos del vendedor']);
    }

    public function reports(Request $request)
    {
        $data = $request->validate(['estado' => ['nullable', 'in:abierto,resuelto']]);
        $reportes = ReporteContenido::with(['usuario', 'moderador'])->where('estado', $data['estado'] ?? 'abierto')->latest()->paginate(12)->withQueryString();
        $products = Producto::withTrashed()->whereIn('id_producto', $reportes->where('tipo', 'productos')->pluck('id_contenido'))->get()->keyBy('id_producto');
        $opinions = Opinion::withTrashed()->whereIn('id', $reportes->where('tipo', 'opiniones')->pluck('id_contenido'))->get()->keyBy('id');

        return view('moderador.reportes', ['reportes' => $reportes, 'products' => $products, 'opinions' => $opinions, 'counts' => $this->counts(), 'pageTitle' => 'Reportes']);
    }

    public function destroy(Request $request, string $tipo, int $id)
    {
        $data = $request->validate(['motivo' => ['required', 'string', 'min:10', 'max:2000'], 'version' => ['required', 'integer', 'min:1']]);
        DB::transaction(function () use ($request, $tipo, $id, $data) {
            $model = ($tipo === 'productos' ? Producto::query() : Opinion::query())->lockForUpdate()->findOrFail($id);
            if ((int) $model->revision_contenido !== (int) $data['version']) {
                throw ValidationException::withMessages(['version' => 'Este contenido cambió desde que lo abriste. Recarga y consulta la versión actual antes de eliminarlo.']);
            }
            $model->forceFill(['motivo_moderacion' => $data['motivo'], 'id_moderador' => $request->user()->id, 'fecha_moderacion' => now(), 'revision_contenido' => $model->revision_contenido + 1])->save();
            $owner = $tipo === 'productos' ? $model->tienda?->user : $model->usuario;
            $title = $tipo === 'productos' ? 'Tu producto fue eliminado por moderación' : 'Tu opinión fue eliminada por moderación';
            $model->delete();
            $owner?->notify(new BuyerActivityNotification($title, $data['motivo'], route($tipo === 'productos' ? 'vendedor.productos.index' : 'comprador.opiniones')));
            $action = 'Eliminó '.($tipo === 'productos' ? 'una publicación' : 'una opinión');
            $request->attributes->set('staff_activity', ['accion' => $action, 'descripcion' => $action.' #'.$id.($tipo === 'productos' ? ': '.$model->nombre_producto : ' de '.($owner?->name ?? 'cuenta eliminada')).'.', 'tipo_objeto' => $tipo, 'id_objeto' => $id]);
        });

        return redirect()->route($tipo === 'productos' ? 'moderador.publicaciones' : 'moderador.opiniones')->with('success', 'Contenido eliminado. El usuario recibió una notificación con el motivo.');
    }

    public function resolve(Request $request, ReporteContenido $reporte)
    {
        $data = $request->validate(['respuesta' => ['required', 'string', 'min:10', 'max:2000']]);
        DB::transaction(function () use ($request, $reporte, $data) {
            $item = ReporteContenido::lockForUpdate()->findOrFail($reporte->id);
            if ($item->estado !== 'abierto') {
                throw ValidationException::withMessages(['respuesta' => 'Este reporte ya fue resuelto.']);
            }
            $item->forceFill(['estado' => 'resuelto', 'respuesta' => $data['respuesta'], 'id_moderador' => $request->user()->id, 'fecha_resolucion' => now()])->save();
            $item->usuario?->notify(new BuyerActivityNotification('Revisamos tu reporte', $data['respuesta'], route('comprador.notificaciones')));
            $request->attributes->set('staff_activity', ['accion' => 'Resolvió un reporte', 'descripcion' => 'Resolvió el reporte #'.$item->id.' sobre '.$item->tipo.' #'.$item->id_contenido.'.', 'tipo_objeto' => 'reporte', 'id_objeto' => $item->id]);
        });

        return back()->with('success', 'Reporte resuelto. Se notificó a quien lo envió.');
    }

    public function analytics()
    {
        $days = collect(range(6, 0))->map(function ($offset) {
            $day = today()->subDays($offset);

            return ['label' => $day->format('d/m'), 'eliminaciones' => $this->actionsFor(['Eliminó una publicación', 'Eliminó una opinión'], $day), 'vendedores' => $this->actionsFor(['Aprobó una solicitud de vendedor'], $day)];
        });
        $activity = $this->moderationActivity()->paginate(15);

        return view('moderador.analiticas', ['days' => $days, 'activity' => $activity, 'counts' => $this->counts(), 'pageTitle' => 'Analíticas']);
    }

    public function settings(Request $request)
    {
        return view('moderador.configuracion', ['user' => $request->user(), 'counts' => $this->counts(), 'pageTitle' => 'Configuración']);
    }

    public function help()
    {
        return view('moderador.ayuda', ['counts' => $this->counts(), 'pageTitle' => 'Centro de ayuda']);
    }
}
