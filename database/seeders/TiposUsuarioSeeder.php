<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_usuario')->insert([
            [
                'id_tipo_usuario' => 1,
                'nombre_tipo' => 'Comprador',
            ],
            [
                'id_tipo_usuario' => 2,
                'nombre_tipo' => 'Vendedor',
            ],
            [
                'id_tipo_usuario' => 3,
                'nombre_tipo' => 'Administrador',
            ],
            [
                'id_tipo_usuario' => 4,
                'nombre_tipo' => 'Moderador',
            ],
        ]);
    }
}