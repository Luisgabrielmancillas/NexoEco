<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MetodosPago
 * 
 * @property int $id_metodo_pago
 * @property string $nombre_metodo
 * 
 * @property Collection|Pedido[] $pedidos
 *
 * @package App\Models
 */
class MetodosPago extends Model
{
	protected $table = 'metodos_pago';
	protected $primaryKey = 'id_metodo_pago';
	public $timestamps = false;

	protected $fillable = [
		'nombre_metodo'
	];

	public function pedidos()
	{
		return $this->hasMany(Pedido::class, 'id_metodo_pago');
	}
}
