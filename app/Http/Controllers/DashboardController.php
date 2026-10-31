<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $transactions = $user->transactions()->where('status', 'completed');

        return view('dashboard.index', [
            'account' => $user->bankAccount,
            'totalDeposited' => (clone $transactions)->where('transaction_type', 'deposit')->sum('amount'),
            'totalWithdrawn' => (clone $transactions)->where('transaction_type', 'withdrawal')->sum('amount'),
            'recentTransactions' => (clone $transactions)->latest()->limit(4)->get(),
        ]);
    }
}
