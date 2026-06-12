<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_networks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('native_asset_id')->nullable();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('native_asset_id')->references('id')->on('crypto_assets')->nullOnDelete();
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_networks');
    }
};
