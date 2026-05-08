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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->integer('id_pedido', true);
            $table->unsignedBigInteger('id_comprador')->index('id_comprador');
            $table->integer('id_tienda')->index('id_tienda');
            $table->integer('id_estado')->index('id_estado');
            $table->integer('id_metodo_pago')->index('id_metodo_pago');
            $table->dateTime('fecha_pedido')->nullable()->useCurrent();
            $table->decimal('total_pedido', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
