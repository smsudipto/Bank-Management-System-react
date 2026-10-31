@forelse ($transactions as $transaction)
  <div class="transaction-row">
    <div class="transaction-leading">
      <div class="transaction-icon {{ $transaction->transaction_type === 'deposit' ? 'deposit' : 'withdraw' }}">{{ $transaction->transaction_type === 'deposit' ? '↓' : '↑' }}</div>
      <div>
        <div class="transaction-name">{{ ucfirst($transaction->transaction_type) }} · {{ $transaction->transaction_reference }}</div>
        <div class="transaction-date">{{ $transaction->description ?: 'No description' }} · {{ $transaction->created_at->format('M d, Y, g:i A') }} · {{ ucfirst($transaction->status) }}</div>
      </div>
    </div>
    <span class="transaction-amount {{ $transaction->transaction_type === 'deposit' ? 'deposit' : 'withdraw' }}">{{ $transaction->transaction_type === 'deposit' ? '+' : '-' }}BDT {{ number_format((float) $transaction->amount, 2) }}</span>
  </div>
@empty
  <div class="empty-state"><div class="empty-icon">☷</div><p>No transactions yet. Your activity will appear here.</p></div>
@endforelse
