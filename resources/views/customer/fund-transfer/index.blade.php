@extends('layouts.app')
@section('title', 'Fund transfer | S4M Trust Bank')
@section('content')
@php
  $mask = fn (string $number): string => '•••• '.substr($number, -4);
@endphp
<div class="page-content container-fluid">
  <div class="page-heading">
    <div><p class="eyebrow">Move money securely</p><h1>Fund transfer</h1><p class="page-description">Transfer funds to another active S4M Trust Bank account.</p></div>
  </div>
  @include('partials.messages')
  <div class="form-layout">
    <div class="card form-card">
      <div class="card-heading"><h2 class="card-title">Transfer money</h2><span class="history-count">Available BDT {{ number_format((float) $account->balance, 2) }}</span></div>
      <p class="form-note">From {{ $mask($account->account_number) }} · {{ $account->status === 'active' ? 'Active account' : 'Account unavailable' }}</p>
      <form method="post" action="{{ route('customer.fund-transfer.store') }}" id="transfer-form" novalidate>
        @csrf
        <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', (string) \Illuminate\Support\Str::uuid()) }}">
        <label class="amount-label" for="recipient_account_number">Recipient account number</label>
        <div class="amount-input-wrap">
          <input class="amount-input" id="recipient_account_number" name="recipient_account_number" value="{{ old('recipient_account_number') }}" placeholder="4829-XXXXXXXX" maxlength="13" required>
          <button class="submit-button" type="button" id="verify-recipient">Verify</button>
        </div>
        <div class="feedback success d-none" id="recipient-result" role="status"></div>
        <div class="feedback error d-none" id="recipient-error" role="alert"></div>
        @error('recipient_account_number')<div class="feedback error">{{ $message }}</div>@enderror
        <label class="amount-label" for="amount">Transfer amount</label>
        <div class="amount-input-wrap"><span class="currency-prefix">৳</span><input class="amount-input" id="amount" name="amount" value="{{ old('amount') }}" inputmode="decimal" placeholder="0.00" required></div>
        @error('amount')<div class="feedback error">{{ $message }}</div>@enderror
        <label class="amount-label" for="description">Description <span class="form-note">(optional)</span></label>
        <input class="auth-input" id="description" name="description" value="{{ old('description') }}" maxlength="255">
        @error('current_password')<div class="feedback error">{{ $message }}</div>@enderror
        <label class="amount-label" for="current_password">Secure verification password</label>
        <input class="auth-input" id="current_password" name="current_password" type="password" required>
        <label class="amount-label" for="confirmed"><input id="confirmed" name="confirmed" type="checkbox" value="1" required> I confirm these transfer details.</label>
        @error('confirmed')<div class="feedback error">{{ $message }}</div>@enderror
        <button class="submit-button" type="submit" id="submit-transfer">Confirm transfer</button>
      </form>
    </div>
    <div class="card info-card">
      <h3>Transfer review</h3>
      <p class="page-description">Verify the recipient before confirming. The server rechecks ownership, account status, balance, and amount before processing.</p>
      <div class="feedback success d-none" id="review-card"></div>
      <dl class="row small">
        <dt class="col-7">Transfer amount</dt><dd class="col-5 text-end" id="review-amount">BDT 0.00</dd>
        <dt class="col-7">Transfer fee</dt><dd class="col-5 text-end">BDT 0.00</dd>
        <dt class="col-7">Total deduction</dt><dd class="col-5 text-end" id="review-total">BDT 0.00</dd>
        <dt class="col-7">Description</dt><dd class="col-5 text-end" id="review-description">None</dd>
      </dl>
    </div>
  </div>
  <div class="card" style="margin-top:20px">
    <div class="history-header"><h2 class="card-title">Transfer history</h2><span class="history-count">{{ $transfers->total() }} transfer{{ $transfers->total() === 1 ? '' : 's' }}</span></div>
    <form method="get" class="row g-2 mb-3">
      <div class="col-md-4"><input class="auth-input" name="reference" value="{{ request('reference') }}" placeholder="Search transfer reference"></div>
      <div class="col-md-3"><input class="auth-input" type="date" name="from" value="{{ request('from') }}" aria-label="From date"></div>
      <div class="col-md-3"><input class="auth-input" type="date" name="to" value="{{ request('to') }}" aria-label="To date"></div>
      <div class="col-md-2"><button class="submit-button" type="submit">Filter</button></div>
    </form>
    @if ($transfers->count())
      <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Reference</th><th>Date</th><th>Type</th><th>Party</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
      @foreach ($transfers as $transfer)
        @php $sent = $transfer->sender_account_id === $account->id; @endphp
        <tr><td class="small">{{ $transfer->transfer_reference }}</td><td>{{ $transfer->created_at->format('M d, Y') }}</td><td>{{ $sent ? 'Sent' : 'Received' }}</td><td>{{ $sent ? $transfer->receiverAccount->user->name : $transfer->senderAccount->user->name }}</td><td class="{{ $sent ? 'text-danger' : 'text-success' }}">{{ $sent ? '-' : '+' }}BDT {{ number_format((float) $transfer->amount, 2) }}</td><td>{{ ucfirst($transfer->status) }}</td><td><a class="text-link" href="{{ route('customer.fund-transfer.receipt', $transfer) }}">Details</a></td></tr>
      @endforeach
      </tbody></table></div>
    @else
      <div class="empty-state"><div class="empty-icon">⇄</div><p>No transfers found.</p></div>
    @endif
    {{ $transfers->links() }}
  </div>
</div>
@endsection
@section('extra_js')
<script>
const verifyButton = document.querySelector('#verify-recipient')
const recipientInput = document.querySelector('#recipient_account_number')
const recipientResult = document.querySelector('#recipient-result')
const recipientError = document.querySelector('#recipient-error')
const reviewCard = document.querySelector('#review-card')
const amountInput = document.querySelector('#amount')
const descriptionInput = document.querySelector('#description')
const reviewAmount = document.querySelector('#review-amount')
const reviewTotal = document.querySelector('#review-total')
const reviewDescription = document.querySelector('#review-description')

const updateReview = () => {
  const amount = Number.parseFloat(amountInput.value) || 0
  const formatted = `BDT ${amount.toFixed(2)}`
  reviewAmount.textContent = formatted
  reviewTotal.textContent = formatted
  reviewDescription.textContent = descriptionInput.value || 'None'
}

amountInput?.addEventListener('input', updateReview)
descriptionInput?.addEventListener('input', updateReview)

verifyButton?.addEventListener('click', async () => {
  recipientResult.classList.add('d-none')
  recipientError.classList.add('d-none')
  verifyButton.disabled = true
  try {
    const response = await fetch('{{ route('customer.fund-transfer.verify') }}', {
      method: 'POST',
      headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
      body: JSON.stringify({recipient_account_number: recipientInput.value.trim().toUpperCase()})
    })
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || data.errors?.recipient_account_number?.[0] || 'Recipient verification failed.')
    recipientResult.textContent = `${data.name} · ${data.account_number} · ${data.status}`
    recipientResult.classList.remove('d-none')
    reviewCard.textContent = `Recipient: ${data.name} · ${data.account_number}`
    reviewCard.classList.remove('d-none')
  } catch (error) {
    recipientError.textContent = error.message
    recipientError.classList.remove('d-none')
  } finally {
    verifyButton.disabled = false
  }
})

document.querySelector('#transfer-form')?.addEventListener('submit', (event) => {
  const button = document.querySelector('#submit-transfer')
  if (!document.querySelector('#recipient-result')?.classList.contains('d-none')) {
    button.disabled = true
    button.textContent = 'Processing...'
  } else {
    event.preventDefault()
    recipientError.textContent = 'Verify the recipient before confirming the transfer.'
    recipientError.classList.remove('d-none')
  }
})
</script>
@endsection
