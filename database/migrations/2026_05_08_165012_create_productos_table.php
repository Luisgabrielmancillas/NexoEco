<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->integer('id_producto', true);
            $table->integer('id_tienda')->index('id_tienda');
            $table->integer('id_categoria')->index('id_categoria');
            $table->string('codigo_producto', 50)->unique('codigo_producto');
            $table->string('nombre_producto', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10);
            $table->string('imagen_url')->nullable();
            $table->dateTime('fecha_publicacion')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
