<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('channel_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel');
            $table->string('name');
            $table->string('external_id')->nullable();
            $table->text('access_token')->nullable();
            $table->string('verify_token')->nullable();
            $table->json('settings')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->index(['tenant_id', 'channel']);
            $table->unique(['tenant_id', 'channel', 'external_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('channel_connections'); }
};
