<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('category_id');
            $table->uuid('credit_card_id')->nullable();
            $table->uuid('credit_card_invoice_id')->nullable();
            $table->string('type'); // income, expense, transfer
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable(); // monthly, weekly, yearly
            $table->date('recurrence_end')->nullable();
            $table->integer('installment_number')->nullable();
            $table->integer('installment_total')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('credit_card_id')->references('id')->on('credit_cards')->nullOnDelete();
            $table->foreign('credit_card_invoice_id')->references('id')->on('credit_card_invoices')->nullOnDelete();

            $table->index(['date']);
            $table->index(['type']);
            $table->index(['is_paid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
