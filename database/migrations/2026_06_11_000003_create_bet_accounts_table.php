<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bet_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('betting_house_id');
            $table->uuid('bet_user_id');
            $table->string('name');
            $table->string('username')->nullable();
            $table->string('account_code')->nullable();
            $table->string('status')->default('active');
            $table->string('verification_status')->nullable();
            $table->decimal('initial_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('bonus_balance', 12, 2)->default(0);
            $table->decimal('withdrawable_balance', 12, 2)->nullable();
            $table->decimal('daily_deposit_limit', 12, 2)->nullable();
            $table->decimal('monthly_deposit_limit', 12, 2)->nullable();
            $table->date('opened_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('betting_house_id')->references('id')->on('betting_houses')->restrictOnDelete();
            $table->foreign('bet_user_id')->references('id')->on('bet_users')->restrictOnDelete();

            $table->index(['betting_house_id']);
            $table->index(['bet_user_id']);
            $table->index(['status']);
            $table->index(['is_active']);
            $table->unique(['betting_house_id', 'bet_user_id', 'username']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bet_accounts');
    }
};
