<?php

namespace App\Support;

use Illuminate\Support\Str;

class MarketplaceDepartments
{
    public const ALL = [
        'comida' => ['label' => 'Comida', 'icon' => 'food', 'words' => ['comida', 'restaurante', 'taco', 'pizza', 'panader', 'postre', 'repost', 'cafeter', 'alimentos preparados']],
        'super' => ['label' => 'Súper', 'icon' => 'cart', 'words' => ['super', 'abarrote', 'despensa', 'fruta', 'verdura', 'carnicer', 'bebida', 'lacteo', 'alimento']],
        'farmacia' => ['label' => 'Farmacia', 'icon' => 'medical', 'words' => ['farmacia', 'medic', 'salud', 'cuidado personal', 'higiene']],
        'tiendas' => ['label' => 'Tiendas', 'icon' => 'bags', 'words' => ['tienda', 'artesani', 'regalo', 'papeler', 'negocio']],
        'mascotas' => ['label' => 'Mascotas', 'icon' => 'paw', 'words' => ['mascota', 'veterin', 'perro', 'gato', 'animal']],
        'hogar' => ['label' => 'Hogar', 'icon' => 'sofa', 'words' => ['hogar', 'mueble', 'decor', 'ceramica', 'cocina', 'limpieza', 'jardin', 'ferreter']],
        'ropa' => ['label' => 'Ropa', 'icon' => 'shirt', 'words' => ['ropa', 'moda', 'calzado', 'textil', 'vestido', 'accesorio', 'joyeria']],
        'electronicos' => ['label' => 'Electrónicos', 'icon' => 'laptop', 'words' => ['electron', 'tecnolog', 'comput', 'celular', 'telefon', 'videojuego', 'electrodomestico']],
    ];

    public static function forCategory(string $name): string
    {
        $name = Str::lower(Str::ascii($name));
        // Specific departments take precedence over general retail/aliment descriptions.
        foreach (['mascotas', 'electronicos', 'farmacia', 'ropa', 'hogar', 'comida', 'super', 'tiendas'] as $key) {
            foreach (self::ALL[$key]['words'] as $word) {
                if (str_contains($name, $word)) {
                    return $key;
                }
            }
        }

        return 'tiendas';
    }
}
