<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_vendedor', function (Blueprint $table) {
            $table->id('id_solicitud');

            $table->foreignId('id_usuario')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('tipo_persona', 20)
                ->default('fisica');

            $table->string('rfc', 13)
                ->nullable();

            $table->string('curp', 18)
                ->nullable();

            $table->string('telefono', 20)
                ->nullable();

            $table->text('domicilio_fiscal')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            |
            | pendiente_documentos
            | en_revision
            | requiere_correccion
            | aprobada
            | rechazada
            |
            */

            $table->string('estado', 30)
                ->default('pendiente_documentos')
                ->index();

            $table->text('motivo_revision')
                ->nullable();

            $table->foreignId('id_moderador')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('fecha_solicitud')
                ->useCurrent();

            $table->timestamp('fecha_envio')
                ->nullable();

            $table->timestamp('fecha_revision')
                ->nullable();

            $table->index([
                'estado',
                'fecha_envio',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_vendedor');
    }
};