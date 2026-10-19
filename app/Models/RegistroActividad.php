<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RegistroActividad extends Model
{
    public const IMPORTANT_ROUTES = [
        'admin.users.store', 'admin.users.update', 'admin.users.destroy',
        'admin.consultas.reply', 'admin.solicitudes.review',
        'moderador.solicitudes.review', 'moderador.contenido.review', 'moderador.contenido.destroy', 'moderador.reportes.resolve',
        'profile.update', 'profile.destroy', 'profile.photo.store', 'profile.photo.destroy', 'password.update',
        'tipos-usuario.store', 'tipos-usuario.update', 'tipos-usuario.destroy',
    ];

    protected $table = 'registros_actividad';

    public $timestamps = false;

    protected $fillable = ['id_usuario', 'nombre_actor', 'roles_actor', 'accion', 'descripcion', 'ruta', 'metodo', 'tipo_objeto', 'id_objeto', 'registrada_en'];

    protected $casts = ['roles_actor' => 'array', 'registrada_en' => 'datetime'];

    public function scopeImportant(Builder $query): Builder
    {
        return $query->whereIn('ruta', self::IMPORTANT_ROUTES);
    }
}
