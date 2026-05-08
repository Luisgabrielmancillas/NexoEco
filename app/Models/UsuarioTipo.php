<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UsuarioTipo
 * 
 * @property int $id_usuario
 * @property int $id_tipo_usuario
 * 
 * @property User $user
 * @property TiposUsuario $tipos_usuario
 *
 * @package App\Models
 */
class UsuarioTipo extends Model
{
	protected $table = 'usuario_tipo';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'id_usuario' => 'int',
		'id_tipo_usuario' => 'int'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'id_usuario');
	}

	public function tipos_usuario()
	{
		return $this->belongsTo(TiposUsuario::class, 'id_tipo_usuario');
	}
}
