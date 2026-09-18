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
 * @property Collection|ProductoImagen[] $imagenes
 * @property ProductoImagen|null $imagenPrincipal
 *
 * @package App\Models
 */
class Producto extends Model
{
    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN DEL MODELO
    |--------------------------------------------------------------------------
    */

    protected $table = 'productos';

    protected $primaryKey = 'id_producto';

    public $timestamps = false;


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'id_tienda' => 'int',
        'id_categoria' => 'int',
        'precio' => 'float',
        'fecha_publicacion' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'id_tienda',
        'id_categoria',
        'codigo_producto',
        'nombre_producto',
        'descripcion',
        'precio',
        'imagen_url',
        'fecha_publicacion',
    ];


    /*
    |--------------------------------------------------------------------------
    | TIENDA
    |--------------------------------------------------------------------------
    |
    | Cada producto pertenece a una tienda.
    |
    */

    public function tienda()
    {
        return $this->belongsTo(
            Tienda::class,
            'id_tienda',
            'id_tienda'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CATEGORÍA
    |--------------------------------------------------------------------------
    |
    | Cada producto pertenece a una categoría.
    |
    */

    public function categoria()
    {
        return $this->belongsTo(
            Categoria::class,
            'id_categoria',
            'id_categoria'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DETALLES DE PEDIDO
    |--------------------------------------------------------------------------
    |
    | Un producto puede formar parte de múltiples pedidos.
    |
    */

    public function detalle_pedidos()
    {
        return $this->hasMany(
            DetallePedido::class,
            'id_producto',
            'id_producto'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | IMÁGENES DEL PRODUCTO
    |--------------------------------------------------------------------------
    |
    | Permite que un producto tenga múltiples imágenes.
    |
    | Se ordenan primero por el campo "orden" para mantener una galería
    | consistente tanto en móvil como en escritorio.
    |
    */

    public function imagenes()
    {
        return $this->hasMany(
            ProductoImagen::class,
            'id_producto',
            'id_producto'
        )
        ->orderBy('orden', 'asc')
        ->orderBy('id_imagen', 'asc');
    }


    /*
    |--------------------------------------------------------------------------
    | IMAGEN PRINCIPAL
    |--------------------------------------------------------------------------
    |
    | Obtiene exclusivamente la imagen marcada como principal.
    |
    | Esta relación será la utilizada principalmente en el marketplace
    | para no cargar la galería completa de cada producto.
    |
    */

    public function imagenPrincipal()
    {
        return $this->hasOne(
            ProductoImagen::class,
            'id_producto',
            'id_producto'
        )
        ->where('es_principal', true)
        ->orderBy('orden', 'asc')
        ->orderBy('id_imagen', 'asc');
    }
}