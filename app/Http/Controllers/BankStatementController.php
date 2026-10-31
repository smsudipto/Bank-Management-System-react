<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankStatementRequest;
use App\Models\Transaction;
use App\Services\BankStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BankStatementController extends Controller
{
    public function __construct(private readonly BankStatementService $statementService)
    {
    }

    public function index(BankStatementRequest $request): View
    {
        $account = $request->user()->bankAccount;
        $statement = $this->statementService->statement($account, $request->validated());

        return view('customer.bank-statement.index', compact('account', 'statement'));
    }

    public function pdf(BankStatementRequest $request): Response
    {
        $account = $request->user()->bankAccount;
        $statement = $this->statementService->statement($account, $request->validated());

        return Pdf::loadView('customer.bank-statement.pdf', compact('account', 'statement'))
            ->setPaper('a4')
            ->download('s4m-trust-bank-statement.pdf');
    }

    public function print(BankStatementRequest $request): View
    {
        $account = $request->user()->bankAccount;
        $statement = $this->statementService->statement($account, $request->validated());

        return view('customer.bank-statement.print', compact('account', 'statement'));
    }

    public function show(Transaction $transaction): View
    {
        abort_unless($transaction->user_id === auth()->id(), 403);

        return view('customer.bank-statement.transaction', compact('transaction'));
    }
}
