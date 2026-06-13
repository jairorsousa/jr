<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crypto_transactions', function (Blueprint $table) {
            $table->uuid('related_crypto_transaction_id')->nullable()->after('bet_transaction_id');
            $table->foreign('related_crypto_transaction_id')
                ->references('id')
                ->on('crypto_transactions')
                ->nullOnDelete();
            $table->index(['related_crypto_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::table('crypto_transactions', function (Blueprint $table) {
            $table->dropForeign(['related_crypto_transaction_id']);
            $table->dropIndex(['related_crypto_transaction_id']);
            $table->dropColumn('related_crypto_transaction_id');
        });
    }
};
