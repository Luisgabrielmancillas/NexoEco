<?php

namespace App\Support;

class ManzanilloBoundary
{
    public const MESSAGE = 'NexoEco está dedicado a los microemprendimientos del municipio de Manzanillo, Colima. No se permiten tiendas fuera de esta zona. Selecciona la ubicación real de tu negocio dentro de Manzanillo.';

    public function contains(float $latitude, float $longitude): bool
    {
        $boundary = json_decode(file_get_contents(public_path('geo/manzanillo.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($boundary['features'][0]['geometry']['coordinates'] as $polygon) {
            if (! $this->inRing($longitude, $latitude, $polygon[0])) {
                continue;
            }
            foreach (array_slice($polygon, 1) as $hole) {
                if ($this->inRing($longitude, $latitude, $hole)) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    private function inRing(float $x, float $y, array $ring): bool
    {
        $inside = false;
        for ($i = 0, $j = count($ring) - 1; $i < count($ring); $j = $i++) {
            [$xi, $yi] = $ring[$i];
            [$xj, $yj] = $ring[$j];
            $cross = ($x - $xi) * ($yj - $yi) - ($y - $yi) * ($xj - $xi);
            if (abs($cross) < 1e-12 && $x >= min($xi, $xj) && $x <= max($xi, $xj) && $y >= min($yi, $yj) && $y <= max($yi, $yj)) {
                return true;
            }
            if (($yi > $y) !== ($yj > $y) && $x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }
}
