<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id')->unique();
            $table->string('name', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('city', 100)->nullable();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent', 18, 2)->default(0);
            $table->dateTime('date_created')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('website_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('website_id')->unique();
            $table->string('number', 30)->nullable();
            $table->string('status', 30)->index();
            $table->string('currency', 10)->default('IRT');
            $table->decimal('total', 18, 2)->default(0);
            $table->decimal('discount_total', 18, 2)->default(0);
            $table->decimal('shipping_total', 18, 2)->default(0);
            $table->decimal('total_tax', 18, 2)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_method_title', 120)->nullable();
            $table->unsignedBigInteger('website_customer_id')->nullable()->index();
            $table->string('billing_name', 150)->nullable();
            $table->string('billing_phone', 30)->nullable();
            $table->string('billing_email', 150)->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->dateTime('date_created')->nullable()->index();
            $table->dateTime('date_paid')->nullable();
            $table->unsignedInteger('item_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('website_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_order_id')->constrained('website_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('website_product_id')->nullable()->index();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name', 200);
            $table->string('sku', 80)->nullable();
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_order_items');
        Schema::dropIfExists('website_orders');
        Schema::dropIfExists('website_customers');
    }
};
