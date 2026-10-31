<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawalCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Transaction $transaction)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'category' => 'withdrawal',
            'title' => 'Withdrawal completed',
            'message' => 'Your withdrawal of BDT '.number_format((float) $this->transaction->amount, 2).' was completed successfully.',
            'amount' => (string) $this->transaction->amount,
            'transaction_reference' => $this->transaction->transaction_reference,
            'status' => $this->transaction->status,
        ];
    }
}
