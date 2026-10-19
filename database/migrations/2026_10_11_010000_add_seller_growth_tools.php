<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_quick_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 80);
            $table->text('body');
            $table->timestamps();
        });
        Schema::create('seller_automations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            $table->text('body');
            $table->boolean('enabled')->default(true);
            $table->json('hours')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'kind']);
        });
        Schema::create('chat_automation_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('product_chats')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('period', 20);
            $table->timestamps();
            $table->unique(['chat_id', 'kind', 'period'], 'chat_auto_delivery_unique');
        });
        Schema::table('product_chats', function (Blueprint $table) {
            $table->string('negotiation_status', 30)->default('nuevo');
            $table->text('negotiation_notes')->nullable();
            $table->decimal('reported_sale_amount', 10, 2)->nullable();
            $table->timestamp('sale_reported_at')->nullable();
        });
        Schema::create('seller_store_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('terracota');
            $table->string('tagline', 120)->nullable();
            $table->text('story')->nullable();
            $table->string('layout', 20)->default('clasico');
            $table->timestamps();
        });
        Schema::create('product_pins', function (Blueprint $table) {
            $table->id();
            $table->uuid('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('seller_subscriptions')->cascadeOnDelete();
            $table->integer('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['subscription_id', 'id_producto']);
        });
        Schema::create('commercial_offers', function (Blueprint $table) {
            $table->id();
            $table->uuid('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('seller_subscriptions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos')->cascadeOnDelete();
            $table->string('title', 100);
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('discount_percent')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->index(['enabled', 'starts_at', 'ends_at']);
        });
        Schema::create('seller_daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->unsignedBigInteger('target_id');
            $table->date('day');
            $table->unsignedInteger('visits')->default(0);
            $table->unique(['seller_id', 'kind', 'target_id', 'day'], 'seller_daily_metrics_unique');
            $table->index(['seller_id', 'day']);
        });
        Schema::table('apartados', function (Blueprint $table) {
            // Operational tracking is independent of the provider's financial status.
            $table->string('fulfillment_status', 20)->default('pendiente');
            $table->timestamp('delivery_at')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamp('reminded_at')->nullable();
            $table->text('seller_notes')->nullable();
        });
        Schema::create('store_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('id_tienda');
            $table->foreign('id_tienda')->references('id_tienda')->on('tiendas')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'id_tienda']);
        });
        Schema::create('store_updates', function (Blueprint $table) {
            $table->id();
            $table->uuid('subscription_id');
            $table->foreign('subscription_id')->references('id')->on('seller_subscriptions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('id_tienda');
            $table->foreign('id_tienda')->references('id_tienda')->on('tiendas')->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('body');
            $table->string('status', 20)->default('scheduled');
            $table->timestamp('publish_at');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'publish_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_updates');
        Schema::dropIfExists('store_follows');
        Schema::table('apartados', fn (Blueprint $table) => $table->dropColumn(['fulfillment_status', 'delivery_at', 'follow_up_at', 'reminded_at', 'seller_notes']));
        Schema::dropIfExists('seller_daily_metrics');
        Schema::dropIfExists('commercial_offers');
        Schema::dropIfExists('product_pins');
        Schema::dropIfExists('seller_store_designs');
        Schema::table('product_chats', fn (Blueprint $table) => $table->dropColumn(['negotiation_status', 'negotiation_notes', 'reported_sale_amount', 'sale_reported_at']));
        Schema::dropIfExists('chat_automation_deliveries');
        Schema::dropIfExists('seller_automations');
        Schema::dropIfExists('seller_quick_replies');
    }
};
