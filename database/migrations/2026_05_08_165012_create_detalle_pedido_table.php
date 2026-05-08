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
        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->integer('id_detalle', true);
            $table->integer('id_pedido')->index('id_pedido');
            $table->integer('id_producto')->index('id_producto');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10);
            $table->decimal('subtotal', 10);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido');
    }
};
