<?php

namespace App\Http\Controllers;

use App\Models\Tienda;
use App\Services\LocalDiscovery;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StoreRouteController extends Controller
{
    public function show(Request $request, Tienda $tienda, LocalDiscovery $discovery)
    {
        abort_unless($tienda->user?->activo && $tienda->user->tieneTipo('vendedor'), 404);
        $origin = $discovery->position($discovery->location($request));
        $destination = $discovery->position(['latitud' => $tienda->latitud, 'longitud' => $tienda->longitud]);
        if (! $origin || ! $destination) {
            return response()->json(['message' => 'Guarda tu punto en el mapa y comprueba que la tienda tenga una ubicación para calcular la ruta.'], 422)->header('Cache-Control', 'no-store, private');
        }
        $points = $origin[1].','.$origin[0].';'.$destination[1].','.$destination[0];
        try {
            $result = Http::acceptJson()->connectTimeout(3)->timeout(8)
                ->get(rtrim(config('discovery.routing_url'), '/').'/route/v1/driving/'.$points, [
                    'overview' => 'full', 'geometries' => 'geojson', 'steps' => 'true', 'alternatives' => 'false', 'radiuses' => '250;250',
                ])->throw()->json();
        } catch (ConnectionException|RequestException $e) {
            return $this->unavailable();
        }
        $route = $result['routes'][0] ?? null;
        if (($result['code'] ?? null) !== 'Ok' || ! is_array($route) || ($route['geometry']['type'] ?? null) !== 'LineString'
            || ! is_numeric($route['distance'] ?? null) || ! is_numeric($route['duration'] ?? null)
            || $route['distance'] < 0 || $route['duration'] < 0
            || ! is_array($route['geometry']['coordinates'] ?? null) || count($route['geometry']['coordinates']) < 2
            || collect($route['geometry']['coordinates'])->contains(fn ($point) => ! is_array($point) || count($point) < 2
                || ! $discovery->position(['latitud' => $point[1], 'longitud' => $point[0]]))) {
            return $this->unavailable();
        }
        $steps = collect($route['legs'] ?? [])->flatMap(fn ($leg) => $leg['steps'] ?? [])->map(fn ($step) => [
            'instruction' => $this->instruction($step), 'distance' => max(0, (float) ($step['distance'] ?? 0)),
        ])->values();

        return response()->json([
            'origin' => $origin, 'destination' => $destination, 'geometry' => $route['geometry'],
            'distance' => (float) $route['distance'], 'duration' => (float) $route['duration'], 'steps' => $steps,
        ])->header('Cache-Control', 'no-store, private');
    }

    private function unavailable()
    {
        return response()->json(['message' => 'No pudimos calcular la ruta en este momento. Puedes consultar cómo llegar en Google Maps.'], 503)->header('Cache-Control', 'no-store, private');
    }

    private function instruction(array $step): string
    {
        $maneuver = $step['maneuver'] ?? [];
        $direction = match ($maneuver['modifier'] ?? '') {
            'left' => 'a la izquierda', 'right' => 'a la derecha', 'slight left' => 'ligeramente a la izquierda',
            'slight right' => 'ligeramente a la derecha', 'sharp left' => 'con un giro cerrado a la izquierda',
            'sharp right' => 'con un giro cerrado a la derecha', 'uturn' => 'y da vuelta en U', default => 'recto',
        };
        $action = match ($maneuver['type'] ?? '') {
            'depart' => 'Comienza el recorrido', 'arrive' => 'Llegaste a la tienda',
            'turn', 'end of road' => 'Gira '.$direction, 'new name', 'continue' => 'Continúa '.$direction,
            'merge' => 'Incorpórate '.$direction, 'on ramp' => 'Toma la incorporación '.$direction,
            'off ramp' => 'Toma la salida '.$direction, 'fork' => 'Mantente '.$direction,
            'roundabout', 'rotary' => 'En la glorieta, toma '.(isset($maneuver['exit']) ? 'la salida '.$maneuver['exit'] : 'la salida indicada'),
            'exit roundabout', 'exit rotary' => 'Sal de la glorieta', default => 'Sigue '.$direction,
        };

        return $action.(! empty($step['name']) && ($maneuver['type'] ?? '') !== 'arrive' ? ' por '.$step['name'] : '').'.';
    }
}
