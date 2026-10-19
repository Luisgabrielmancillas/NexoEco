<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RespuestaSoporte extends Model
{
    protected $table = 'respuestas_soporte';

    protected $fillable = ['id_solicitud', 'id_usuario', 'es_administrador', 'mensaje'];

    protected $casts = ['es_administrador' => 'boolean'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function solicitud()
    {
        return $this->belongsTo(SolicitudSoporte::class, 'id_solicitud');
    }
}
