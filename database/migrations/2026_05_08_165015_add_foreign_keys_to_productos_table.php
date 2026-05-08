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
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign(['id_tienda'], 'productos_ibfk_1')->references(['id_tienda'])->on('tiendas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_categoria'], 'productos_ibfk_2')->references(['id_categoria'])->on('categorias')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('productos_ibfk_1');
            $table->dropForeign('productos_ibfk_2');
        });
    }
};
