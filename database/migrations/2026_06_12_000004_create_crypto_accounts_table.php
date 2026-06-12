<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('crypto_institution_id');
            $table->uuid('bet_user_id')->nullable();
            $table->string('name');
            $table->string('account_identifier')->nullable();
            $table->string('custody_type')->default('exchange');
            $table->decimal('initial_balance_brl', 12, 2)->default(0);
            $table->decimal('current_balance_brl', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('crypto_institution_id')->references('id')->on('crypto_institutions')->restrictOnDelete();
            $table->foreign('bet_user_id')->references('id')->on('bet_users')->nullOnDelete();
            $table->index(['is_active']);
            $table->index(['custody_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_accounts');
    }
};
