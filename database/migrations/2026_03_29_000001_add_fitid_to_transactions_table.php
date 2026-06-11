<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('fitid')->nullable()->after('description');
            $table->index(['account_id', 'fitid']);
        });

        DB::table('transactions')
            ->where('description', 'like', '%[%]')
            ->orderBy('id')
            ->get(['id', 'description'])
            ->each(function ($transaction) {
                preg_match('/\[([^\]]+)\]/', $transaction->description, $matches);

                DB::table('transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'fitid' => $matches[1] ?? null,
                        'description' => trim(preg_replace('/\s*\[[^\]]+\]\s*$/', '', $transaction->description)),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['account_id', 'fitid']);
            $table->dropColumn('fitid');
        });
    }
};
