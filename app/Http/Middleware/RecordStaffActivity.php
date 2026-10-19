<?php

namespace App\Http\Middleware;

use App\Models\RegistroActividad;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordStaffActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route()?->getName();
        if (! in_array($route, RegistroActividad::IMPORTANT_ROUTES, true) || $request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }
        $actor = $this->actor($request->user());
        $previousErrors = $request->session()->get('errors');
        $response = $next($request);
        $actor ??= $this->actor($request->user());
        $route = $request->route()?->getName();
        $newErrors = $request->session()->get('errors');

        // Only completed changes belong in the activity feed.
        if (! $actor || ! $route || $request->isMethod('HEAD') || $response->getStatusCode() >= 400 || $request->attributes->get('staff_activity_skip') || ($newErrors && $newErrors !== $previousErrors)
            || $request->routeIs('profile.photo.show', 'comprador.notificaciones.count')) {
            return $response;
        }
        $activity = $request->attributes->get('staff_activity') ?? $this->description($request, $route);
        RegistroActividad::create([
            'id_usuario' => $request->routeIs('profile.destroy') ? null : $actor['id'],
            'nombre_actor' => $actor['nombre'],
            'roles_actor' => $actor['roles'],
            'accion' => $activity['accion'],
            'descripcion' => $activity['descripcion'],
            'tipo_objeto' => $activity['tipo_objeto'] ?? null,
            'id_objeto' => isset($activity['id_objeto']) ? (string) $activity['id_objeto'] : null,
            'ruta' => $route,
            'metodo' => $request->method(),
            'registrada_en' => now(),
        ]);

        return $response;
    }

    private function actor(?User $user): ?array
    {
        if (! $user || ! $user->activo) {
            return null;
        }
        $roles = $user->tipos_usuario()->pluck('nombre_tipo')->map(fn ($role) => mb_strtolower($role))
            ->filter(fn ($role) => in_array($role, ['administrador', 'moderador'], true))->unique()->values()->all();

        return $roles ? ['id' => $user->id, 'nombre' => $user->nombre_completo ?: $user->name, 'roles' => $roles] : null;
    }

    private function description(Request $request, string $route): array
    {
        $actions = [
            'admin.users.store' => 'Creó una cuenta', 'admin.users.update' => 'Actualizó una cuenta', 'admin.users.destroy' => 'Cambió el acceso de una cuenta',
            'admin.consultas.reply' => 'Respondió una consulta', 'admin.solicitudes.review' => 'Revisó una solicitud de vendedor',
            'profile.update' => 'Actualizó sus datos personales', 'profile.destroy' => 'Eliminó su cuenta',
            'profile.photo.store' => 'Actualizó su foto de perfil', 'profile.photo.destroy' => 'Quitó su foto de perfil', 'password.update' => 'Cambió su contraseña',
            'tipos-usuario.store' => 'Creó un tipo de usuario', 'tipos-usuario.update' => 'Actualizó un tipo de usuario', 'tipos-usuario.destroy' => 'Eliminó un tipo de usuario',
        ];
        $action = $actions[$route] ?? ($request->isMethod('GET') ? 'Consultó una sección' : 'Realizó una actualización');
        $activity = ['accion' => $action, 'descripcion' => $action.'.'];
        foreach ($request->route()->parameters() as $name => $value) {
            if ($value instanceof Model) {
                $activity['tipo_objeto'] = $name;
                $activity['id_objeto'] = $value->getKey();
                $activity['descripcion'] = $action.' (registro #'.$value->getKey().').';
                break;
            }
        }

        return $activity;
    }
}
