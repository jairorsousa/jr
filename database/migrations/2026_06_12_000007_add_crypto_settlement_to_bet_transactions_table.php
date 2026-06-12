<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bet_transactions', function (Blueprint $table) {
            $table->string('settlement_method')->default('manual')->after('finance_transaction_id');
            $table->uuid('crypto_transaction_id')->nullable()->after('settlement_method');
            $table->foreign('crypto_transaction_id')->references('id')->on('crypto_transactions')->nullOnDelete();
            $table->index(['settlement_method']);
        });

        DB::table('bet_transactions')
            ->whereNotNull('finance_transaction_id')
            ->update(['settlement_method' => 'bank']);
    }

    public function down(): void
    {
        Schema::table('bet_transactions', function (Blueprint $table) {
            $table->dropForeign(['crypto_transaction_id']);
            $table->dropIndex(['settlement_method']);
            $table->dropColumn(['settlement_method', 'crypto_transaction_id']);
        });
    }
};
