<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->string('portada_tienda')->nullable();
            $table->string('razon_social', 150)->nullable();
            $table->string('giro', 100)->nullable();
            $table->string('tipo_negocio', 40)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email_contacto')->nullable();
            $table->string('sitio_web', 500)->nullable();
            $table->string('direccion', 250)->nullable();
            $table->string('colonia', 100)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('codigo_postal', 5)->nullable();
            $table->string('referencias', 500)->nullable();
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->json('horarios')->nullable();
            $table->json('servicios')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->dropColumn(['portada_tienda', 'razon_social', 'giro', 'tipo_negocio', 'telefono', 'email_contacto', 'sitio_web', 'direccion', 'colonia', 'ciudad', 'estado', 'codigo_postal', 'referencias', 'latitud', 'longitud', 'horarios', 'servicios']);
        });
    }
};
