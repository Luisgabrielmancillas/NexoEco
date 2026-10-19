<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('plan', 30);
            $table->decimal('amount', 10, 2);
            $table->char('currency', 3)->default('MXN');
            $table->string('status', 30)->default('pending');
            $table->string('provider_id')->nullable()->unique();
            $table->text('checkout_url')->nullable();
            $table->timestamp('paid_until')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'paid_until']);
        });
        Schema::create('product_promotions', function (Blueprint $table) {
            $table->id();
            $table->uuid('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('seller_subscriptions')->cascadeOnDelete();
            $table->integer('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['subscription_id', 'id_producto']);
        });
        Schema::create('promotion_metrics', function (Blueprint $table) {
            $table->id();
            $table->uuid('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('seller_subscriptions')->cascadeOnDelete();
            $table->string('kind', 30);
            $table->unsignedBigInteger('target_id');
            $table->date('day');
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('visits')->default(0);
            $table->unique(['subscription_id', 'kind', 'target_id', 'day'], 'promotion_metrics_daily_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_metrics');
        Schema::dropIfExists('product_promotions');
        Schema::dropIfExists('seller_subscriptions');
    }
};
