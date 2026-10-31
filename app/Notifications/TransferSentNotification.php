<?php

namespace App\Notifications;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransferSentNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Transfer $transfer)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $recipient = $this->transfer->receiverAccount;

        return [
            'category' => 'transfer',
            'title' => 'Transfer sent',
            'message' => 'You sent BDT '.number_format((float) $this->transfer->amount, 2).' to '.$recipient->user->name.'.',
            'amount' => (string) $this->transfer->amount,
            'counterparty_name' => $recipient->user->name,
            'counterparty_account' => '•••• '.substr($recipient->account_number, -4),
            'transaction_reference' => $this->transfer->transfer_reference,
            'status' => $this->transfer->status,
        ];
    }
}
