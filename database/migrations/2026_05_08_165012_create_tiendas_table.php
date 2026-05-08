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
        Schema::create('tiendas', function (Blueprint $table) {
            $table->integer('id_tienda', true);
            $table->unsignedBigInteger('id_vendedor')->index('id_vendedor');
            $table->string('nombre_tienda', 120);
            $table->text('descripcion_tienda')->nullable();
            $table->string('logo_tienda')->nullable();
            $table->dateTime('fecha_creacion')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiendas');
    }
};
