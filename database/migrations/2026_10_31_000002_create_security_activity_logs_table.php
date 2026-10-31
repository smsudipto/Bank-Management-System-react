<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type', 80);
            $table->string('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status', 20)->default('success');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'activity_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_activity_logs');
    }
};
