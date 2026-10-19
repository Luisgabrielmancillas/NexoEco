<?php

/**
 * Created for NexoEco.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductoImagen
 *
 * @property int $id_imagen
 * @property int $id_producto
 * @property string $imagen_url
 * @property bool $es_principal
 * @property int $orden
 * @property Carbon|null $fecha_creacion
 *
 * @property Producto $producto
 *
 * @package App\Models
 */
class ProductoImagen extends Model
{
    protected $table = 'producto_imagenes';

    protected $primaryKey = 'id_imagen';

    public $timestamps = false;

    protected $casts = [
        'id_producto' => 'int',
        'es_principal' => 'bool',
        'orden' => 'int',
        'fecha_creacion' => 'datetime',
    ];

    protected $fillable = [
        'id_producto',
        'imagen_url',
        'es_principal',
        'orden',
        'fecha_creacion',
    ];

    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'id_producto'
        );
    }
}