<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('crypto_account_id');
            $table->uuid('finance_transaction_id')->nullable();
            $table->uuid('bet_transaction_id')->nullable();
            $table->uuid('crypto_asset_id')->nullable();
            $table->uuid('crypto_network_id')->nullable();
            $table->string('type');
            $table->string('status')->default('confirmed');
            $table->decimal('amount_brl', 12, 2);
            $table->decimal('balance_before_brl', 12, 2)->nullable();
            $table->decimal('balance_after_brl', 12, 2)->nullable();
            $table->decimal('crypto_amount', 28, 10)->nullable();
            $table->decimal('exchange_rate_brl', 18, 8)->nullable();
            $table->decimal('fee_brl', 12, 2)->default(0);
            $table->decimal('fee_crypto_amount', 28, 10)->nullable();
            $table->string('tx_hash')->nullable();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->string('description');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('crypto_account_id')->references('id')->on('crypto_accounts')->cascadeOnDelete();
            $table->foreign('finance_transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->foreign('bet_transaction_id')->references('id')->on('bet_transactions')->nullOnDelete();
            $table->foreign('crypto_asset_id')->references('id')->on('crypto_assets')->nullOnDelete();
            $table->foreign('crypto_network_id')->references('id')->on('crypto_networks')->nullOnDelete();
            $table->index(['crypto_account_id', 'occurred_at']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['tx_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_transactions');
    }
};
