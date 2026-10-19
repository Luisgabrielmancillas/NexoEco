<?php

namespace App\Support;

use App\Models\TiposUsuario;

class AccountRoles
{
    public const LABELS = ['comprador' => 'Compradores', 'vendedor' => 'Vendedores', 'moderador' => 'Moderadores', 'administrador' => 'Administradores'];

    public static function id(string $role): int
    {
        return (int) (TiposUsuario::whereRaw('LOWER(nombre_tipo) = ?', [$role])->first() ?? TiposUsuario::firstOrCreate(['nombre_tipo' => $role]))->getKey();
    }
}
