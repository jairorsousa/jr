<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('fitid')->nullable()->after('description');
            $table->index(['account_id', 'fitid']);
        });

        // Migrate existing FITIDs from description field [FITID] to new column
        DB::statement("UPDATE transactions SET fitid = SUBSTRING_INDEX(SUBSTRING_INDEX(description, '[', -1), ']', 1) WHERE description LIKE '%[%]'");

        // Clean up descriptions: remove [FITID] suffix
        DB::statement("UPDATE transactions SET description = TRIM(SUBSTRING_INDEX(description, ' [', 1)) WHERE description LIKE '%[%]'");
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['account_id', 'fitid']);
            $table->dropColumn('fitid');
        });
    }
};
