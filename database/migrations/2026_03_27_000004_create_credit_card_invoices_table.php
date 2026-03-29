<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_card_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('credit_card_id');
            $table->date('reference_month');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->foreign('credit_card_id')->references('id')->on('credit_cards')->cascadeOnDelete();
            $table->unique(['credit_card_id', 'reference_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_invoices');
    }
};
