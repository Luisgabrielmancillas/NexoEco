<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioTipo extends Model
{
    protected $table = 'usuario_tipo';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'id_tipo_usuario'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function tipos_usuario()
    {
        return $this->belongsTo(TiposUsuario::class, 'id_tipo_usuario');
    }
}