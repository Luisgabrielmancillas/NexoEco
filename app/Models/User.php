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
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'nombre_completo',
        'fecha_registro'
    ];

    // Relación otros módulos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_comprador');
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'id_vendedor');
    }

    // 🔥 RELACIÓN CORRECTA DE ROLES (many-to-many)
    public function tipos_usuario()
    {
        return $this->belongsToMany(
            TiposUsuario::class,
            'usuario_tipo',
            'id_usuario',
            'id_tipo_usuario'
        );
    }

    public function tieneTipo(string $tipo): bool
    {
        return $this->tipos_usuario()
            ->where('nombre_tipo', $tipo)
            ->exists();
    }
}