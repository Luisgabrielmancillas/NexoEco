<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Pedido
 * 
 * @property int $id_pedido
 * @property int $id_comprador
 * @property int $id_tienda
 * @property int $id_estado
 * @property int $id_metodo_pago
 * @property Carbon|null $fecha_pedido
 * @property float $total_pedido
 * 
 * @property User $user
 * @property Tienda $tienda
 * @property EstadosPedido $estados_pedido
 * @property MetodosPago $metodos_pago
 * @property Collection|DetallePedido[] $detalle_pedidos
 *
 * @package App\Models
 */
class Pedido extends Model
{
	protected $table = 'pedidos';
	protected $primaryKey = 'id_pedido';
	public $timestamps = false;

	protected $casts = [
		'id_comprador' => 'int',
		'id_tienda' => 'int',
		'id_estado' => 'int',
		'id_metodo_pago' => 'int',
		'fecha_pedido' => 'datetime',
		'total_pedido' => 'float'
	];

	protected $fillable = [
		'id_comprador',
		'id_tienda',
		'id_estado',
		'id_metodo_pago',
		'fecha_pedido',
		'total_pedido'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_comprador');
	}

	public function tienda()
	{
		return $this->belongsTo(Tienda::class, 'id_tienda');
	}

	public function estados_pedido()
	{
		return $this->belongsTo(EstadosPedido::class, 'id_estado');
	}

	public function metodos_pago()
	{
		return $this->belongsTo(MetodosPago::class, 'id_metodo_pago');
	}

	public function detalle_pedidos()
	{
		return $this->hasMany(DetallePedido::class, 'id_pedido');
	}
}
