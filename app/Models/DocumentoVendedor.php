<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoVendedor extends Model
{
    protected $table = 'documentos_vendedor';

    protected $primaryKey = 'id_documento';

    public $timestamps = false;


    protected $casts = [
        'id_solicitud' => 'int',
        'tamano' => 'int',
        'fecha_subida' => 'datetime',
    ];


    protected $fillable = [
        'id_solicitud',
        'tipo_documento',
        'ruta_archivo',
        'nombre_original',
        'mime_type',
        'tamano',
        'hash_sha256',
        'estado_documento',
        'fecha_subida',
    ];


    public function solicitud()
    {
        return $this->belongsTo(
            SolicitudVendedor::class,
            'id_solicitud'
        );
    }
}