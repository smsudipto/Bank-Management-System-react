<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'sender_account_id',
        'receiver_account_id',
        'sender_transaction_id',
        'receiver_transaction_id',
        'transfer_reference',
        'idempotency_key',
        'amount',
        'fee',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
        ];
    }

    public function senderAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'sender_account_id');
    }

    public function receiverAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'receiver_account_id');
    }

    public function senderTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'sender_transaction_id');
    }

    public function receiverTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'receiver_transaction_id');
    }
}
