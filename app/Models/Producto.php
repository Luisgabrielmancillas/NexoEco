<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Producto
 * 
 * @property int $id_producto
 * @property int $id_tienda
 * @property int $id_categoria
 * @property string $codigo_producto
 * @property string $nombre_producto
 * @property string|null $descripcion
 * @property float $precio
 * @property string|null $imagen_url
 * @property Carbon|null $fecha_publicacion
 * 
 * @property Tienda $tienda
 * @property Categoria $categoria
 * @property Collection|DetallePedido[] $detalle_pedidos
 *
 * @package App\Models
 */
class Producto extends Model
{
	protected $table = 'productos';
	protected $primaryKey = 'id_producto';
	public $timestamps = false;

	protected $casts = [
		'id_tienda' => 'int',
		'id_categoria' => 'int',
		'precio' => 'float',
		'fecha_publicacion' => 'datetime'
	];

	protected $fillable = [
		'id_tienda',
		'id_categoria',
		'codigo_producto',
		'nombre_producto',
		'descripcion',
		'precio',
		'imagen_url',
		'fecha_publicacion'
	];

	public function tienda()
	{
		return $this->belongsTo(Tienda::class, 'id_tienda');
	}

	public function categoria()
	{
		return $this->belongsTo(Categoria::class, 'id_categoria');
	}

	public function detalle_pedidos()
	{
		return $this->hasMany(DetallePedido::class, 'id_producto');
	}
}
