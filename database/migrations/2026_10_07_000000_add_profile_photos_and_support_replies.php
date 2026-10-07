<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable();
        });
        Schema::create('respuestas_soporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_solicitud')->constrained('solicitudes_soporte')->cascadeOnDelete();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('es_administrador');
            $table->text('mensaje');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_soporte');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('profile_photo_path'));
    }
};
