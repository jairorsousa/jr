<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('deal_id');
            $table->string('type');
            $table->text('description');
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
            $table->index('deal_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_activities');
    }
};
