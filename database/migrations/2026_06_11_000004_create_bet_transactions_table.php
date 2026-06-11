<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('bet_account_id');
            $table->uuid('finance_transaction_id')->nullable();
            $table->string('type');
            $table->string('status')->default('confirmed');
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2)->nullable();
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->string('description');
            $table->string('external_reference')->nullable();
            $table->string('event_name')->nullable();
            $table->string('market_name')->nullable();
            $table->string('selection_name')->nullable();
            $table->decimal('odd', 10, 4)->nullable();
            $table->string('strategy')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('bet_account_id')->references('id')->on('bet_accounts')->cascadeOnDelete();
            $table->foreign('finance_transaction_id')->references('id')->on('transactions')->nullOnDelete();

            $table->index(['bet_account_id', 'occurred_at']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['finance_transaction_id']);
            $table->index(['external_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bet_transactions');
    }
};
