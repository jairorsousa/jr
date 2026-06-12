<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_wallet_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('crypto_account_id');
            $table->uuid('crypto_asset_id')->nullable();
            $table->uuid('crypto_network_id');
            $table->string('address');
            $table->string('label')->nullable();
            $table->boolean('is_deposit_address')->default(true);
            $table->boolean('is_withdrawal_address')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('crypto_account_id')->references('id')->on('crypto_accounts')->cascadeOnDelete();
            $table->foreign('crypto_asset_id')->references('id')->on('crypto_assets')->nullOnDelete();
            $table->foreign('crypto_network_id')->references('id')->on('crypto_networks')->restrictOnDelete();
            $table->unique(['crypto_network_id', 'address']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_wallet_addresses');
    }
};
