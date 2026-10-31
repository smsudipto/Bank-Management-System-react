<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Notifications\TransferReceivedNotification;
use App\Notifications\TransferSentNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FundTransferService
{
    public function verifyRecipient(BankAccount $sender, string $accountNumber): BankAccount
    {
        $recipient = BankAccount::with('user')
            ->where('account_number', strtoupper($accountNumber))
            ->where('status', 'active')
            ->first();

        if (! $recipient || $recipient->id === $sender->id) {
            throw ValidationException::withMessages([
                'recipient_account_number' => $recipient
                    ? 'You cannot transfer money to your own account.'
                    : 'No active account was found with that account number.',
            ]);
        }

        return $recipient;
    }

    public function transfer(
        BankAccount $sender,
        BankAccount $recipient,
        string $amount,
        ?string $description,
        string $idempotencyKey,
    ): Transfer {
        $existing = Transfer::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($sender, $recipient, $amount, $description, $idempotencyKey): Transfer {
                $accountIds = [$sender->id, $recipient->id];
                sort($accountIds, SORT_NUMERIC);
                $lockedAccounts = BankAccount::whereIn('id', $accountIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $lockedSender = $lockedAccounts->get($sender->id);
                $lockedRecipient = $lockedAccounts->get($recipient->id);
                if (! $lockedSender || ! $lockedRecipient || $lockedSender->status !== 'active' || $lockedRecipient->status !== 'active') {
                    throw ValidationException::withMessages([
                        'recipient_account_number' => 'Both accounts must be active to transfer funds.',
                    ]);
                }

                $amountInCents = $this->toCents($amount);
                $balanceInCents = $this->toCents((string) $lockedSender->balance);
                if ($amountInCents > $balanceInCents) {
                    throw ValidationException::withMessages([
                        'amount' => 'Insufficient balance for this transfer.',
                    ]);
                }

                $reference = (string) Str::uuid();
                $senderTransaction = Transaction::create([
                    'user_id' => $lockedSender->user_id,
                    'bank_account_id' => $lockedSender->id,
                    'transaction_reference' => (string) Str::uuid(),
                    'transaction_type' => 'transfer_out',
                    'amount' => $amount,
                    'description' => $description,
                    'status' => 'completed',
                ]);
                $receiverTransaction = Transaction::create([
                    'user_id' => $lockedRecipient->user_id,
                    'bank_account_id' => $lockedRecipient->id,
                    'transaction_reference' => (string) Str::uuid(),
                    'transaction_type' => 'transfer_in',
                    'amount' => $amount,
                    'description' => $description,
                    'status' => 'completed',
                ]);

                $lockedSender->decrement('balance', $amount);
                $lockedRecipient->increment('balance', $amount);

                $transfer = Transfer::create([
                    'sender_account_id' => $lockedSender->id,
                    'receiver_account_id' => $lockedRecipient->id,
                    'sender_transaction_id' => $senderTransaction->id,
                    'receiver_transaction_id' => $receiverTransaction->id,
                    'transfer_reference' => $reference,
                    'idempotency_key' => $idempotencyKey,
                    'amount' => $amount,
                    'fee' => '0.00',
                    'description' => $description,
                    'status' => 'completed',
                ]);

                $transfer->load(['senderAccount.user', 'receiverAccount.user']);
                DB::afterCommit(function () use ($transfer): void {
                    $transfer->senderAccount->user->notify(new TransferSentNotification($transfer));
                    $transfer->receiverAccount->user->notify(new TransferReceivedNotification($transfer));
                });

                return $transfer;
            });
        } catch (QueryException $exception) {
            if ($this->isIdempotencyConflict($exception)) {
                return Transfer::where('idempotency_key', $idempotencyKey)->firstOrFail();
            }

            throw $exception;
        }
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function isIdempotencyConflict(QueryException $exception): bool
    {
        return str_contains(strtolower($exception->getMessage()), 'idempotency_key');
    }
}
