<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('last_digits', 4);
            $table->string('brand'); // visa, mastercard, elo, amex, other
            $table->decimal('credit_limit', 12, 2);
            $table->integer('closing_day');
            $table->integer('due_day');
            $table->string('color')->default('#212529');
            $table->uuid('account_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};
