<?php

namespace App\Services;

use App\Models\BankAccount;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BankStatementService
{
    public function statement(BankAccount $account, array $filters): array
    {
        $from = isset($filters['from']) && $filters['from']
            ? CarbonImmutable::parse($filters['from'])->startOfDay()
            : CarbonImmutable::now()->startOfMonth();
        $to = isset($filters['to']) && $filters['to']
            ? CarbonImmutable::parse($filters['to'])->endOfDay()
            : CarbonImmutable::now()->endOfDay();

        $allRangeTransactions = $account->transactions()
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
        $transactions = $allRangeTransactions
            ->when(($filters['type'] ?? 'all') !== 'all', fn (Collection $items) => $items->where('transaction_type', $filters['type']))
            ->when($filters['search'] ?? null, function (Collection $items, string $search): Collection {
                return $items->filter(fn ($transaction): bool => str_contains(strtolower((string) $transaction->transaction_reference), strtolower($search))
                    || str_contains(strtolower((string) $transaction->description), strtolower($search))
                    || str_contains(strtolower((string) $transaction->transaction_type), strtolower($search)));
            });

        $allAfterStart = $account->transactions()
            ->where('status', 'completed')
            ->where('created_at', '>=', $from)
            ->get();
        $openingBalance = $this->toCents((string) $account->balance) - $this->netCents($allAfterStart);
        $runningBalance = $openingBalance;
        $allEntries = $allRangeTransactions->map(function ($transaction) use (&$runningBalance): array {
            $movement = $this->movementCents($transaction->transaction_type, (string) $transaction->amount);
            $runningBalance += $movement;

            return [
                'transaction' => $transaction,
                'debit' => $movement < 0 ? abs($movement) / 100 : 0,
                'credit' => $movement > 0 ? $movement / 100 : 0,
                'running_balance' => $runningBalance / 100,
                'type_label' => $this->typeLabel($transaction->transaction_type),
            ];
        });
        $selectedIds = $transactions->pluck('id')->all();
        $entries = $allEntries->filter(fn (array $entry): bool => in_array($entry['transaction']->id, $selectedIds, true))->values();

        $summary = [
            'deposits' => $this->sumType($transactions, 'deposit'),
            'withdrawals' => $this->sumType($transactions, 'withdrawal'),
            'transfers_sent' => $this->sumType($transactions, 'transfer_out'),
            'transfers_received' => $this->sumType($transactions, 'transfer_in'),
        ];

        return [
            'from' => $from,
            'to' => $to,
            'opening_balance' => $openingBalance / 100,
            'closing_balance' => $runningBalance / 100,
            'summary' => $summary,
            'entries' => $entries->reverse()->values(),
            'transactions' => $this->paginate($entries->reverse()->values(), 15),
        ];
    }

    private function paginate(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => request()->query()],
        );
    }

    private function netCents(Collection $transactions): int
    {
        return $transactions->sum(fn ($transaction): int => $this->movementCents(
            $transaction->transaction_type,
            (string) $transaction->amount,
        ));
    }

    private function movementCents(string $type, string $amount): int
    {
        $cents = $this->toCents($amount);

        return in_array($type, ['withdrawal', 'transfer_out'], true) ? -$cents : $cents;
    }

    private function sumType(Collection $transactions, string $type): float
    {
        return $transactions
            ->where('transaction_type', $type)
            ->sum(fn ($transaction): float => (float) $transaction->amount);
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'transfer_out' => 'Transfer sent',
            'transfer_in' => 'Transfer received',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    private function toCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }
}
