<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_actividad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nombre_actor');
            $table->json('roles_actor');
            $table->string('accion');
            $table->text('descripcion');
            $table->string('ruta')->nullable();
            $table->string('metodo', 10);
            $table->string('tipo_objeto')->nullable();
            $table->string('id_objeto')->nullable();
            $table->dateTime('registrada_en')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_actividad');
    }
};
