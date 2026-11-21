<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profit_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->date('week_start');
            $table->date('week_end');
            $table->decimal('sales_revenue', 15, 2)->default(0);
            $table->decimal('sales_cost', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('team_percent', 5, 2)->default(50);
            $table->decimal('team_share', 15, 2)->default(0);
            $table->decimal('investor_share', 15, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('team_vat', 15, 2)->default(0);
            $table->decimal('investor_vat', 15, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_settlements');
    }
};
