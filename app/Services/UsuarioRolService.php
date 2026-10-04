<?php

namespace App\Services;

use App\Models\TiposUsuario;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UsuarioRolService
{
    private const ROL_COMPRADOR = 'comprador';

    private const ROL_VENDEDOR = 'vendedor';


    /**
     * Todos los usuarios creados mediante registro público
     * comienzan como compradores.
     *
     * Seleccionar "vendedor" únicamente expresa la intención
     * de solicitar acceso como vendedor.
     */
    public function asignarRolesRegistro(
        User $user,
        string $tipoRegistro
    ): void {
        $tipoRegistro = mb_strtolower(
            trim($tipoRegistro)
        );


        if (
            !in_array(
                $tipoRegistro,
                [
                    'comprador',
                    'vendedor',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Tipo de registro no permitido.'
            );
        }


        $comprador = TiposUsuario::query()
            ->where(
                'nombre_tipo',
                self::ROL_COMPRADOR
            )
            ->first();


        if (!$comprador) {
            throw new RuntimeException(
                'El rol comprador no está configurado.'
            );
        }


        $user
            ->tipos_usuario()
            ->syncWithoutDetaching([
                $comprador->id_tipo_usuario,
            ]);
    }


    /**
     * Esta operación NO debe llamarse desde registro público.
     *
     * Será utilizada posteriormente únicamente desde el
     * proceso protegido de aprobación del moderador.
     */
    public function habilitarComoVendedor(
        User $user
    ): void {
        DB::transaction(function () use ($user) {

            $roles = TiposUsuario::query()
                ->whereIn(
                    'nombre_tipo',
                    [
                        self::ROL_COMPRADOR,
                        self::ROL_VENDEDOR,
                    ]
                )
                ->get([
                    'id_tipo_usuario',
                    'nombre_tipo',
                ]);


            if ($roles->count() !== 2) {
                throw new RuntimeException(
                    'Los roles comprador y vendedor no están configurados correctamente.'
                );
            }


            $user
                ->tipos_usuario()
                ->syncWithoutDetaching(
                    $roles
                        ->pluck('id_tipo_usuario')
                        ->all()
                );
        });
    }
}