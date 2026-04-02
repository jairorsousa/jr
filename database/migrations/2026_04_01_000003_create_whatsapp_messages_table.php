<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('whatsapp_conversations')->cascadeOnDelete();
            $table->string('message_id')->nullable();
            $table->string('type')->default('text');
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->string('media_mimetype')->nullable();
            $table->string('media_filename')->nullable();
            $table->boolean('from_me')->default(false);
            $table->string('status')->default('sent');
            $table->json('raw_data')->nullable();
            $table->timestamp('message_at');
            $table->timestamps();

            $table->index('message_id');
            $table->index('message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
