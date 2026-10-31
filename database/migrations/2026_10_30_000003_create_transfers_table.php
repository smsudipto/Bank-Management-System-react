<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sender_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('receiver_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('sender_transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->foreignId('receiver_transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->uuid('transfer_reference')->unique();
            $table->uuid('idempotency_key')->unique();
            $table->decimal('amount', 14, 2);
            $table->decimal('fee', 14, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->index(['sender_account_id', 'created_at']);
            $table->index(['receiver_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
