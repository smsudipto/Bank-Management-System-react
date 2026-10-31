<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Notifications\DepositCompletedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function create(): View
    {
        return view('deposit.index', ['account' => Auth::user()->bankAccount]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $account = Auth::user()->bankAccount()->lockForUpdate()->firstOrFail();
            $account->increment('balance', $validated['amount']);

            $transaction = Transaction::create([
                'user_id' => $request->user()->id,
                'bank_account_id' => $account->id,
                'transaction_reference' => (string) Str::uuid(),
                'transaction_type' => 'deposit',
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'status' => 'completed',
            ]);

            DB::afterCommit(fn (): bool => (bool) $request->user()->notify(new DepositCompletedNotification($transaction)));
        });

        return redirect()->route('deposit')->with('success', 'Deposit completed successfully.');
    }
}
