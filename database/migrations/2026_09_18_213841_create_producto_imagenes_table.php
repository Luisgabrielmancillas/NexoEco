<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar migración.
     */
    public function up(): void
    {
        Schema::create('producto_imagenes', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | PRIMARY KEY
            |--------------------------------------------------------------------------
            */

            $table->increments('id_imagen');


            /*
            |--------------------------------------------------------------------------
            | PRODUCTO
            |--------------------------------------------------------------------------
            |
            | productos.id_producto es:
            |
            | INT SIGNED AUTO_INCREMENT
            |
            | Por eso esta columna debe ser también INT SIGNED.
            |
            */

            $table->integer('id_producto');


            /*
            |--------------------------------------------------------------------------
            | IMAGEN
            |--------------------------------------------------------------------------
            */

            $table->string('imagen_url', 255);


            /*
            |--------------------------------------------------------------------------
            | IMAGEN PRINCIPAL
            |--------------------------------------------------------------------------
            |
            | Un producto podrá tener varias fotografías, pero una de ellas
            | será utilizada como portada dentro del marketplace.
            |
            */

            $table->boolean('es_principal')
                ->default(false);


            /*
            |--------------------------------------------------------------------------
            | ORDEN
            |--------------------------------------------------------------------------
            |
            | Determina el orden de las fotografías dentro de la futura
            | galería del producto.
            |
            */

            $table->unsignedInteger('orden')
                ->default(0);


            /*
            |--------------------------------------------------------------------------
            | FECHA
            |--------------------------------------------------------------------------
            */

            $table->timestamp('fecha_creacion')
                ->useCurrent();


            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEY
            |--------------------------------------------------------------------------
            */

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */

            $table->index(
                [
                    'id_producto',
                    'es_principal',
                ],
                'idx_producto_imagen_principal'
            );

            $table->index(
                [
                    'id_producto',
                    'orden',
                ],
                'idx_producto_imagen_orden'
            );
        });
    }


    /**
     * Revertir migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_imagenes');
    }
};