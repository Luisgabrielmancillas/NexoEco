<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opinion extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'opiniones';

    protected $fillable = ['id_usuario', 'id_producto', 'id_tienda', 'calificacion', 'comentario'];

    protected $casts = ['calificacion' => 'integer', 'fecha_moderacion' => 'datetime', 'revision_contenido' => 'integer'];

    public function scopePublicadas($query)
    {
        return $query;
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto')->withTrashed();
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'id_tienda');
    }
}
