<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudVendedor extends Model
{
    public const ESTADO_PENDIENTE_DOCUMENTOS =
        'pendiente_documentos';

    public const ESTADO_EN_REVISION =
        'en_revision';

    public const ESTADO_REQUIERE_CORRECCION =
        'requiere_correccion';

    public const ESTADO_APROBADA =
        'aprobada';

    public const ESTADO_RECHAZADA =
        'rechazada';


    protected $table = 'solicitudes_vendedor';

    protected $primaryKey = 'id_solicitud';

    public $timestamps = false;


    protected $casts = [
        'id_usuario' => 'int',
        'id_moderador' => 'int',

        'fecha_solicitud' => 'datetime',
        'fecha_envio' => 'datetime',
        'fecha_revision' => 'datetime',
    ];


    protected $fillable = [
        'id_usuario',
        'tipo_persona',
        'rfc',
        'curp',
        'telefono',
        'domicilio_fiscal',
        'estado',
        'motivo_revision',
        'id_moderador',
        'fecha_solicitud',
        'fecha_envio',
        'fecha_revision',
    ];


    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'id_usuario'
        );
    }


    public function moderador()
    {
        return $this->belongsTo(
            User::class,
            'id_moderador'
        );
    }


    public function documentos()
    {
        return $this->hasMany(
            DocumentoVendedor::class,
            'id_solicitud'
        );
    }


    public function estaEnRevision(): bool
    {
        return $this->estado ===
            self::ESTADO_EN_REVISION;
    }


    public function estaAprobada(): bool
    {
        return $this->estado ===
            self::ESTADO_APROBADA;
    }
}