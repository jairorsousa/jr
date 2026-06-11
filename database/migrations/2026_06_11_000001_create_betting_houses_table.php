<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('betting_houses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->string('country')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('color')->default('#ff6f00');
            $table->decimal('min_deposit', 12, 2)->nullable();
            $table->decimal('min_withdrawal', 12, 2)->nullable();
            $table->decimal('deposit_fee_percent', 5, 2)->nullable();
            $table->decimal('withdrawal_fee_percent', 5, 2)->nullable();
            $table->unsignedInteger('withdrawal_time_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('betting_houses');
    }
};
