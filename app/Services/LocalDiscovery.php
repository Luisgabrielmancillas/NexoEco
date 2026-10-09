<?php

namespace App\Services;

use App\Models\Tienda;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LocalDiscovery
{
    public function location(Request $request): ?array
    {
        return $request->user() ? $request->user()->ubicacion_comprador : $request->session()->get('buyer_location');
    }

    public function position(?array $location): ?array
    {
        if (! is_numeric($location['latitud'] ?? null) || ! is_numeric($location['longitud'] ?? null)) {
            return null;
        }
        $lat = (float) $location['latitud'];
        $lng = (float) $location['longitud'];

        return is_finite($lat) && is_finite($lng) && abs($lat) <= 90 && abs($lng) <= 180 ? [$lat, $lng] : null;
    }

    public function distance(array $origin, array $destination): float
    {
        $a = sin(deg2rad($destination[0] - $origin[0]) / 2) ** 2
            + cos(deg2rad($origin[0])) * cos(deg2rad($destination[0])) * sin(deg2rad($destination[1] - $origin[1]) / 2) ** 2;

        return 6371.0088 * 2 * asin(sqrt(min(1, max(0, $a))));
    }

    public function nearby(?array $location): Collection
    {
        $origin = $this->position($location);
        if (! $origin) {
            return collect();
        }
        $radius = (float) config('discovery.nearby_radius_km');
        $latDelta = rad2deg($radius / 6371.0088);
        $lngDelta = abs($origin[0]) + $latDelta >= 90 ? 180
            : rad2deg(asin(min(1, sin($radius / 6371.0088) / cos(deg2rad($origin[0])))));
        $stores = Tienda::whereNotNull('latitud')->whereNotNull('longitud')
            ->whereBetween('latitud', [$origin[0] - $latDelta, $origin[0] + $latDelta])
            ->whereBetween('longitud', [$origin[1] - $lngDelta, $origin[1] + $lngDelta])
            ->whereHas('user', fn ($q) => $q->where('activo', true)->whereHas('tipos_usuario', fn ($q) => $q->where('nombre_tipo', 'vendedor')))
            ->get();

        return $stores->map(function ($store) use ($origin) {
            $store->setAttribute('distance_km', $this->distance($origin, [$store->latitud, $store->longitud]));

            return $store;
        })->filter(fn ($store) => $store->distance_km <= $radius)->sortBy('distance_km')->take(6)->values();
    }

    public function directionsUrl(Tienda $store, ?array $location): ?string
    {
        $destination = $this->position(['latitud' => $store->latitud, 'longitud' => $store->longitud]);
        $address = $store->direccion && $store->ciudad ? implode(', ', array_filter([$store->nombre_tienda, $store->direccion, $store->colonia, $store->ciudad, $store->estado, 'México'])) : null;
        if (! $destination && ! $address) {
            return null;
        }
        $params = ['api' => 1, 'destination' => $destination ? implode(',', $destination) : $address, 'travelmode' => 'driving'];
        if ($origin = $this->position($location)) {
            $params['origin'] = implode(',', $origin);
        }

        return 'https://www.google.com/maps/dir/?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}
