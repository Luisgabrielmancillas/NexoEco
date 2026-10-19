<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteContenido extends Model
{
    protected $table = 'reportes_contenido';

    protected $fillable = ['id_usuario', 'tipo', 'id_contenido', 'categoria', 'motivo'];

    protected $casts = ['fecha_resolucion' => 'datetime'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function moderador()
    {
        return $this->belongsTo(User::class, 'id_moderador');
    }
}
