<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Collection;

class User extends Authenticatable
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

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_comprador');
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class, 'id_vendedor');
    }

    public function usuario_tipos()
    {
        return $this->hasMany(UsuarioTipo::class, 'id_usuario');
    }
}