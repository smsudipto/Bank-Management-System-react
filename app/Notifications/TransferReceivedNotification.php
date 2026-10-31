<?php

namespace App\Notifications;

use App\Models\Transfer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TransferReceivedNotification extends Notification
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
        $sender = $this->transfer->senderAccount;

        return [
            'category' => 'transfer',
            'title' => 'Transfer received',
            'message' => 'You received BDT '.number_format((float) $this->transfer->amount, 2).' from '.$sender->user->name.'.',
            'amount' => (string) $this->transfer->amount,
            'counterparty_name' => $sender->user->name,
            'counterparty_account' => '•••• '.substr($sender->account_number, -4),
            'transaction_reference' => $this->transfer->transfer_reference,
            'status' => $this->transfer->status,
        ];
    }
}
