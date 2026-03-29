<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('pending'); // pending, in_progress, done, cancelled
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->uuid('list_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('list_id')->references('id')->on('task_lists')->nullOnDelete();
            $table->index(['status']);
            $table->index(['priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
