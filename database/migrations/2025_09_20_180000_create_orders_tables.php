<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending'); // pending, submitted, production, fulfilled, cancelled
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_total', 10, 2)->default(0);
            $table->decimal('shipping_total', 10, 2)->default(0);
            $table->decimal('discount_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->string('external_id')->nullable(); // Prodigi/Pwinty order id
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->json('shipping_address')->nullable();
            $table->json('data')->nullable(); // provider response, metadata
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained('photos')->nullOnDelete();
            $table->string('product_code')->nullable(); // SKU / Prodigi product
            $table->string('variant')->nullable(); // size/finish option
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('markup_percent', 8, 2)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('number')->nullable();
            $table->string('status')->default('unpaid'); // unpaid, paid, refunded
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount_total', 10, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('photo_id')->nullable()->constrained('photos')->nullOnDelete();
            $table->string('product_code')->nullable();
            $table->string('variant')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price_base', 10, 2)->default(0);
            $table->decimal('unit_price_final', 10, 2)->default(0);
            $table->decimal('markup_percent', 8, 2)->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

