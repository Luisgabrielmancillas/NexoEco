<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiposUsuario extends Model
{
    protected $table = 'tipos_usuario';
    protected $primaryKey = 'id_tipo_usuario';
    public $timestamps = false;

    protected $fillable = [
        'nombre_tipo'
    ];

    // 🔥 relación correcta inversa
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'usuario_tipo',
            'id_tipo_usuario',
            'id_usuario'
        );
    }
}