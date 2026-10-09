<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('apartados_activos')->default(false);
            $table->decimal('apartado_monto', 10, 2)->nullable();
            $table->string('paypal_merchant_id', 32)->nullable();
            $table->text('apartado_condiciones')->nullable();
        });
        Schema::create('product_chats', function (Blueprint $table) {
            $table->id();
            $table->integer('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->uuid('conversation_id')->unique();
            $table->foreign('conversation_id')->references('id')->on('ch_conversations')->cascadeOnDelete();
            $table->unique(['id_producto', 'buyer_id']);
            $table->timestamps();
        });
        Schema::create('apartados', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->string('producto_nombre', 150);
            $table->decimal('monto', 10, 2);
            $table->char('moneda', 3);
            $table->string('merchant_id', 32);
            $table->text('condiciones');
            $table->string('paypal_order_id')->nullable()->unique();
            $table->string('paypal_capture_id')->nullable()->unique();
            $table->text('approval_url')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->timestamp('pagado_at')->nullable();
            $table->timestamps();
            $table->index(['buyer_id', 'id_producto', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartados');
        Schema::dropIfExists('product_chats');
        Schema::table('productos', fn (Blueprint $table) => $table->dropColumn(['apartados_activos', 'apartado_monto', 'paypal_merchant_id', 'apartado_condiciones']));
    }
};
