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
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->foreign(['id_pedido'], 'detalle_pedido_ibfk_1')->references(['id_pedido'])->on('pedidos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_producto'], 'detalle_pedido_ibfk_2')->references(['id_producto'])->on('productos')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropForeign('detalle_pedido_ibfk_1');
            $table->dropForeign('detalle_pedido_ibfk_2');
        });
    }
};
