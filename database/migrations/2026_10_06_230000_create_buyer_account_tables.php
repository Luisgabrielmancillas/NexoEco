<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_favoritos', function (Blueprint $table) {
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->integer('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['id_usuario', 'id_producto']);
        });
        Schema::create('tiendas_favoritas', function (Blueprint $table) {
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->integer('id_tienda');
            $table->foreign('id_tienda')->references('id_tienda')->on('tiendas')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['id_usuario', 'id_tienda']);
        });
        Schema::create('opiniones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->integer('id_producto')->nullable();
            $table->integer('id_tienda')->nullable();
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->foreign('id_tienda')->references('id_tienda')->on('tiendas')->cascadeOnDelete();
            $table->unsignedTinyInteger('calificacion');
            $table->text('comentario');
            $table->timestamps();
            $table->unique(['id_usuario', 'id_producto']);
            $table->unique(['id_usuario', 'id_tienda']);
        });
        Schema::create('solicitudes_soporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('users')->cascadeOnDelete();
            $table->string('asunto', 150);
            $table->text('mensaje');
            $table->string('estado', 30)->default('abierta');
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('solicitudes_soporte');
        Schema::dropIfExists('opiniones');
        Schema::dropIfExists('tiendas_favoritas');
        Schema::dropIfExists('productos_favoritos');
    }
};
