<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opiniones', function (Blueprint $table) {
            $table->text('respuesta_vendedor')->nullable();
            $table->timestamp('respuesta_fecha')->nullable();
        });
        foreach (config('store-categories') as $categories) {
            foreach ($categories as $name) {
                DB::table('categorias')->insertOrIgnore(['nombre_categoria' => $name]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('opiniones', fn (Blueprint $table) => $table->dropColumn(['respuesta_vendedor', 'respuesta_fecha']));
        // Categories may be referenced by products; preserve them on rollback.
    }
};
