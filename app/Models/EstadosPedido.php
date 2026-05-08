<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EstadosPedido
 * 
 * @property int $id_estado
 * @property string $nombre_estado
 * 
 * @property Collection|Pedido[] $pedidos
 *
 * @package App\Models
 */
class EstadosPedido extends Model
{
	protected $table = 'estados_pedido';
	protected $primaryKey = 'id_estado';
	public $timestamps = false;

	protected $fillable = [
		'nombre_estado'
	];

	public function pedidos()
	{
		return $this->hasMany(Pedido::class, 'id_estado');
	}
}
