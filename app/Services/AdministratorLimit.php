<?php

namespace App\Services;

use App\Models\TiposUsuario;
use App\Models\User;
use App\Support\AccountRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdministratorLimit
{
    public const MAX_ACCOUNTS = 3;

    public const MESSAGE = 'Solo pueden existir 3 cuentas de administrador. Las cuentas desactivadas también cuentan para este límite.';

    public function count(): int
    {
        return User::whereHas('tipos_usuario', fn ($query) => $query->whereRaw('LOWER(nombre_tipo) = ?', ['administrador']))->count();
    }

    // Must run inside the same transaction as creating the account and assigning its role.
    public function reserveRole(): int
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('Administrator assignment requires a transaction.');
        }
        $roleId = AccountRoles::id('administrador');
        // The shared role row serializes competing requests, including when there are no admins yet.
        $roles = TiposUsuario::whereRaw('LOWER(nombre_tipo) = ?', ['administrador'])->orderBy('id_tipo_usuario')->lockForUpdate()->get();
        // Locking reads see the latest committed assignments even under MySQL repeatable read.
        $accounts = DB::table('usuario_tipo')->whereIn('id_tipo_usuario', $roles->modelKeys())->orderBy('id_usuario')->lockForUpdate()->get(['id_usuario'])->pluck('id_usuario')->unique();
        if ($accounts->count() >= self::MAX_ACCOUNTS) {
            throw ValidationException::withMessages(['rol' => self::MESSAGE]);
        }

        return $roleId;
    }
}
