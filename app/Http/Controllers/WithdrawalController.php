<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Notifications\WithdrawalCompletedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function create(): View
    {
        return view('withdrawal.index', ['account' => Auth::user()->bankAccount]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $account = Auth::user()->bankAccount()->lockForUpdate()->firstOrFail();
            $amountInCents = (int) round((float) $validated['amount'] * 100);
            $balanceInCents = (int) round((float) $account->balance * 100);
            if ($amountInCents > $balanceInCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient balance. You can withdraw up to BDT '.number_format((float) $account->balance, 2).'.',
                ]);
            }

            $account->decrement('balance', $validated['amount']);
            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'bank_account_id' => $account->id,
                'transaction_reference' => (string) Str::uuid(),
                'transaction_type' => 'withdrawal',
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'status' => 'completed',
            ]);

            DB::afterCommit(fn (): bool => (bool) $request->user()->notify(new WithdrawalCompletedNotification($transaction)));
        });

        return redirect()->route('withdrawal')->with('success', 'Withdrawal completed successfully.');
    }
}
