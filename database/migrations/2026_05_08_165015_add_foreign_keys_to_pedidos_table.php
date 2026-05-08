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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreign(['id_comprador'], 'pedidos_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_tienda'], 'pedidos_ibfk_2')->references(['id_tienda'])->on('tiendas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_estado'], 'pedidos_ibfk_3')->references(['id_estado'])->on('estados_pedido')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['id_metodo_pago'], 'pedidos_ibfk_4')->references(['id_metodo_pago'])->on('metodos_pago')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign('pedidos_ibfk_1');
            $table->dropForeign('pedidos_ibfk_2');
            $table->dropForeign('pedidos_ibfk_3');
            $table->dropForeign('pedidos_ibfk_4');
        });
    }
};
