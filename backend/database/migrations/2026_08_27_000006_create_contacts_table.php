<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 32)->nullable()->index();
            $table->string('avatar')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('source')->nullable()->index();
            $table->string('status')->default('active')->index();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'workspace_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('contacts'); }
};
