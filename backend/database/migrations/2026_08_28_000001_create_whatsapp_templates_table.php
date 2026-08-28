<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('channel_connection_id')->constrained('channel_connections')->cascadeOnDelete();
            $table->string('external_id')->nullable()->index();
            $table->string('name');
            $table->string('language', 20)->default('en_US');
            $table->string('category', 40)->nullable();
            $table->string('status', 40)->default('pending');
            $table->json('components')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'workspace_id']);
            $table->unique(['channel_connection_id', 'name', 'language']);
        });
    }

    public function down(): void { Schema::dropIfExists('whatsapp_templates'); }
};
