<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Auth::user()->transactions()
            ->where('status', 'completed')
            ->latest()
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }
}
