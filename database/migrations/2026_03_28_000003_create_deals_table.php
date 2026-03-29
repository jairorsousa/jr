<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->uuid('contact_id');
            $table->uuid('product_id')->nullable();
            $table->string('stage')->default('lead');
            $table->string('status')->default('open');
            $table->decimal('value', 12, 2)->default(0);
            $table->date('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('contact_id')->references('id')->on('contacts')->restrictOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->index('stage');
            $table->index('status');
            $table->index('contact_id');
            $table->index('expected_close_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
