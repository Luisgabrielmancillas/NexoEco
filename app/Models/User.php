<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;

    protected $table = 'users';


    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_registro' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'nombre_completo',
        'fecha_registro',
        'activo',
    ];


    public function pedidos()
    {
        return $this->hasMany(
            Pedido::class,
            'id_comprador'
        );
    }


    public function tiendas()
    {
        return $this->hasMany(
            Tienda::class,
            'id_vendedor'
        );
    }


    public function tipos_usuario()
    {
        return $this->belongsToMany(
            TiposUsuario::class,
            'usuario_tipo',
            'id_usuario',
            'id_tipo_usuario'
        );
    }


    public function solicitudVendedor()
    {
        return $this->hasOne(
            SolicitudVendedor::class,
            'id_usuario'
        );
    }


    public function solicitudesModeradas()
    {
        return $this->hasMany(
            SolicitudVendedor::class,
            'id_moderador'
        );
    }


    public function tieneTipo(string $tipo): bool
    {
        return $this
            ->tipos_usuario()
            ->where(
                'nombre_tipo',
                mb_strtolower(trim($tipo))
            )
            ->exists();
    }


    public function tieneAlgunTipo(array $tipos): bool
    {
        $tipos = collect($tipos)
            ->map(
                fn ($tipo) =>
                    mb_strtolower(
                        trim((string) $tipo)
                    )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();


        if (empty($tipos)) {
            return false;
        }


        return $this
            ->tipos_usuario()
            ->whereIn(
                'nombre_tipo',
                $tipos
            )
            ->exists();
    }
}