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
        Schema::create('usuario_tipo', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario');
            $table->integer('id_tipo_usuario')->index('id_tipo_usuario');

            $table->primary(['id_usuario', 'id_tipo_usuario']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_tipo');
    }
};
