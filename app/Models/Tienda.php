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
 * 
 * @property User $user
 * @property Collection|Pedido[] $pedidos
 * @property Collection|Producto[] $productos
 *
 * @package App\Models
 */
class Tienda extends Model
{
	protected $table = 'tiendas';
	protected $primaryKey = 'id_tienda';
	public $timestamps = false;

	protected $casts = [
		'id_vendedor' => 'int',
		'fecha_creacion' => 'datetime'
	];

	protected $fillable = [
		'id_vendedor',
		'nombre_tienda',
		'descripcion_tienda',
		'logo_tienda',
		'fecha_creacion'
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
