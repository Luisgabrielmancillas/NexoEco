<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudSoporte extends Model
{
    protected $table = 'solicitudes_soporte';

    protected $fillable = ['id_usuario', 'asunto', 'mensaje', 'estado'];

    protected $casts = ['id_usuario' => 'integer'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function respuestas()
    {
        return $this->hasMany(RespuestaSoporte::class, 'id_solicitud')->orderBy('id');
    }
}
