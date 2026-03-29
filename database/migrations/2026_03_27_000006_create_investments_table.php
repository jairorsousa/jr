<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // crypto, fixed_income, stocks, funds, other
            $table->string('broker')->nullable();
            $table->decimal('invested_amount', 14, 2)->default(0);
            $table->decimal('current_amount', 14, 2)->default(0);
            $table->decimal('quantity', 18, 8)->nullable();
            $table->date('purchase_date');
            $table->date('maturity_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
