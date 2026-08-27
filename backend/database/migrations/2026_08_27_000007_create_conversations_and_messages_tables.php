<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 40);
            $table->string('external_id')->nullable();
            $table->string('status')->default('open')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
            $table->index(['tenant_id', 'channel', 'status']);
            $table->unique(['tenant_id', 'channel', 'external_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('direction', 20);
            $table->string('sender_type', 40)->nullable();
            $table->foreignId('sender_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40)->default('text');
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('status', 30)->default('sent');
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();
            $table->index(['tenant_id', 'conversation_id', 'created_at']);
            $table->unique(['tenant_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
