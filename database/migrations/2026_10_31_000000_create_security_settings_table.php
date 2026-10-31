<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->boolean('new_device_login_alert')->default(true);
            $table->boolean('successful_login_alert')->default(false);
            $table->boolean('failed_login_alert')->default(true);
            $table->boolean('suspicious_login_alert')->default(true);
            $table->boolean('password_change_alert')->default(true);
            $table->boolean('two_factor_security_alert')->default(true);
            $table->timestamp('last_password_changed_at')->nullable();
            $table->timestamp('last_successful_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};
