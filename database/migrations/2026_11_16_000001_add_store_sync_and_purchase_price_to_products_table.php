<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('website_id')->nullable()->index()->after('code');
            $table->decimal('purchase_price', 15, 2)->default(0)->after('website_id');
            $table->decimal('website_price', 15, 2)->nullable()->after('selling_price');
            $table->double('website_stock')->nullable()->after('website_price');
            $table->timestamp('website_synced_at')->nullable()->after('website_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['website_id', 'purchase_price', 'website_price', 'website_stock', 'website_synced_at']);
        });
    }
};
