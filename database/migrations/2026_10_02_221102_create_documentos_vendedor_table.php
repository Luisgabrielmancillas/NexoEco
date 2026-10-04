<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_vendedor', function (Blueprint $table) {
            $table->id('id_documento');

            $table->unsignedBigInteger('id_solicitud');

            $table->string('tipo_documento', 50);

            /*
            |--------------------------------------------------------------------------
            | RUTA PRIVADA
            |--------------------------------------------------------------------------
            |
            | Nunca almacenar URL pública.
            |
            */

            $table->string('ruta_archivo', 500);

            $table->string('nombre_original', 255);

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('tamano');

            $table->char('hash_sha256', 64)
                ->nullable();

            $table->string('estado_documento', 20)
                ->default('pendiente');

            $table->timestamp('fecha_subida')
                ->useCurrent();

            $table->foreign('id_solicitud')
                ->references('id_solicitud')
                ->on('solicitudes_vendedor')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | UN DOCUMENTO ACTUAL POR TIPO
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'id_solicitud',
                'tipo_documento',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_vendedor');
    }
};