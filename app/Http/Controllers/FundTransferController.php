<?php

namespace App\Http\Controllers;

use App\Http\Requests\FundTransferRequest;
use App\Models\Transfer;
use App\Services\FundTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FundTransferController extends Controller
{
    public function __construct(private readonly FundTransferService $transferService)
    {
    }

    public function index(Request $request): View
    {
        $account = $request->user()->bankAccount;
        $transfers = Transfer::with(['senderAccount.user', 'receiverAccount.user'])
            ->where(function ($query) use ($account): void {
                $query->where('sender_account_id', $account->id)
                    ->orWhere('receiver_account_id', $account->id);
            })
            ->when($request->filled('reference'), function ($query) use ($request): void {
                $query->where('transfer_reference', $request->string('reference')->toString());
            })
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('to')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('customer.fund-transfer.index', compact('account', 'transfers'));
    }

    public function verifyRecipient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recipient_account_number' => ['required', 'string', 'regex:/^4829-[A-Z0-9]{8}$/'],
        ]);
        $recipient = $this->transferService->verifyRecipient(
            $request->user()->bankAccount,
            $validated['recipient_account_number'],
        );

        return response()->json([
            'name' => $recipient->user->name,
            'account_number' => $this->maskAccountNumber($recipient->account_number),
            'status' => ucfirst($recipient->status),
        ]);
    }

    public function store(FundTransferRequest $request): RedirectResponse
    {
        $sender = $request->user()->bankAccount;
        $recipient = $this->transferService->verifyRecipient(
            $sender,
            $request->string('recipient_account_number')->toString(),
        );
        $transfer = $this->transferService->transfer(
            $sender,
            $recipient,
            $request->string('amount')->toString(),
            $request->input('description'),
            $request->string('idempotency_key')->toString(),
        );

        return redirect()->route('customer.fund-transfer.receipt', $transfer)
            ->with('success', 'Transfer completed successfully.');
    }

    public function receipt(Request $request, Transfer $transfer): View
    {
        $accountId = $request->user()->bankAccount->id;
        abort_unless(
            $transfer->sender_account_id === $accountId || $transfer->receiver_account_id === $accountId,
            403,
        );
        $transfer->load(['senderAccount.user', 'receiverAccount.user']);

        return view('customer.fund-transfer.receipt', [
            'transfer' => $transfer,
            'accountId' => $accountId,
        ]);
    }

    private function maskAccountNumber(string $accountNumber): string
    {
        return '•••• '.substr($accountNumber, -4);
    }
}
