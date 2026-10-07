<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tienda
 *
 * @property int $id_tienda
 * @property int $id_vendedor
 * @property string $nombre_tienda
 * @property string|null $descripcion_tienda
 * @property string|null $logo_tienda
 * @property Carbon|null $fecha_creacion
 * @property User $user
 * @property Collection|Pedido[] $pedidos
 * @property Collection|Producto[] $productos
 */
class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $primaryKey = 'id_tienda';

    public $timestamps = false;

    public function opiniones()
    {
        return $this->hasMany(Opinion::class, 'id_tienda');
    }

    protected $casts = [
        'id_vendedor' => 'int',
        'horarios' => 'array',
        'servicios' => 'array',
        'latitud' => 'float',
        'longitud' => 'float',
        'fecha_creacion' => 'datetime',
    ];

    protected $fillable = [
        'portada_tienda', 'razon_social', 'giro', 'tipo_negocio', 'telefono', 'email_contacto', 'sitio_web',
        'direccion', 'colonia', 'ciudad', 'estado', 'codigo_postal', 'referencias', 'latitud', 'longitud', 'horarios', 'servicios',
        'id_vendedor',
        'nombre_tienda',
        'descripcion_tienda',
        'logo_tienda',
        'fecha_creacion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_vendedor');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_tienda');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_tienda');
    }
}
