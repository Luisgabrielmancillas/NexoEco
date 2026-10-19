<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['productos', 'opiniones'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                // Preserve the existing catalog; new submissions explicitly enter review.
                $table->string('estado_moderacion', 20)->default('aprobada')->index();
                $table->text('motivo_moderacion')->nullable();
                $table->foreignId('id_moderador')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('fecha_moderacion')->nullable()->index();
                $table->unsignedInteger('revision_contenido')->default(1);
            });
        }
        Schema::create('reportes_contenido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20);
            $table->unsignedBigInteger('id_contenido');
            $table->string('categoria', 40);
            $table->text('motivo');
            $table->string('estado', 20)->default('abierto')->index();
            $table->text('respuesta')->nullable();
            $table->foreignId('id_moderador')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_resolucion')->nullable();
            $table->timestamps();
            $table->unique(['id_usuario', 'tipo', 'id_contenido']);
            $table->index(['tipo', 'id_contenido', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_contenido');
        foreach (['productos', 'opiniones'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('id_moderador');
                $table->dropColumn(['estado_moderacion', 'motivo_moderacion', 'fecha_moderacion', 'revision_contenido']);
            });
        }
    }
};
