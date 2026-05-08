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
        Schema::table('usuario_tipo', function (Blueprint $table) {
            $table->foreign(['id_usuario'], 'usuario_tipo_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_tipo_usuario'], 'usuario_tipo_ibfk_2')->references(['id_tipo_usuario'])->on('tipos_usuario')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuario_tipo', function (Blueprint $table) {
            $table->dropForeign('usuario_tipo_ibfk_1');
            $table->dropForeign('usuario_tipo_ibfk_2');
        });
    }
};
