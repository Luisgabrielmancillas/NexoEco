<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TiposUsuario
 * 
 * @property int $id_tipo_usuario
 * @property string $nombre_tipo
 * 
 * @property Collection|UsuarioTipo[] $usuario_tipos
 *
 * @package App\Models
 */
class TiposUsuario extends Model
{
	protected $table = 'tipos_usuario';
	protected $primaryKey = 'id_tipo_usuario';
	public $timestamps = false;

	protected $fillable = [
		'nombre_tipo'
	];

	public function usuario_tipos()
	{
		return $this->hasMany(UsuarioTipo::class, 'id_tipo_usuario');
	}
}
