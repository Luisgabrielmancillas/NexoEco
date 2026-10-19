<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mercado_pago_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users');
            $table->string('mp_user_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at');
            $table->boolean('live_mode')->default(false);
            $table->timestamps();
        });
        Schema::table('apartados', function (Blueprint $table) {
            // Preserve any historical PayPal records; all new reservations use Mercado Pago.
            $table->string('provider', 24)->default('paypal');
            $table->string('mp_preference_id')->nullable()->unique();
            $table->string('mp_payment_id')->nullable()->unique();
            $table->boolean('live_mode')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('apartados', fn (Blueprint $table) => $table->dropColumn(['provider', 'mp_preference_id', 'mp_payment_id', 'live_mode']));
        Schema::dropIfExists('mercado_pago_accounts');
    }
};
