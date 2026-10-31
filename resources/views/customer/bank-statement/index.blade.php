@extends('layouts.app')
@section('title', 'Bank statement | S4M Trust Bank')
@section('content')
@php($mask = fn (string $number): string => '•••• '.substr($number, -4))
<div class="page-content container-fluid">
  <div class="page-heading">
    <div><p class="eyebrow">Account records</p><h1>Bank statement</h1><p class="page-description">Review your account activity and balances for a selected period.</p></div>
    <div class="d-flex gap-2"><a class="submit-button" href="{{ route('customer.bank-statement.pdf', request()->query()) }}">Download PDF</a><a class="submit-button" href="{{ route('customer.bank-statement.print', request()->query()) }}" target="_blank">Print</a></div>
  </div>
  @include('partials.messages')
  @if ($errors->any())<div class="feedback error">{{ $errors->first() }}</div>@endif
  <div class="card mb-4">
    <div class="card-heading"><h2 class="card-title">Account information</h2><span class="history-count">Generated {{ now()->format('M d, Y') }}</span></div>
    <div class="row g-3">
      <div class="col-md-3"><span class="metric-label">Customer</span><strong>{{ auth()->user()->name }}</strong></div>
      <div class="col-md-3"><span class="metric-label">Account number</span><strong>{{ $mask($account->account_number) }}</strong></div>
      <div class="col-md-3"><span class="metric-label">Account type</span><strong>{{ $account->account_type }}</strong></div>
      <div class="col-md-3"><span class="metric-label">Available balance</span><strong>BDT {{ number_format((float) $account->balance, 2) }}</strong></div>
    </div>
  </div>
  <div class="card mb-4">
    <div class="card-heading"><h2 class="card-title">Statement filters</h2><a class="text-link" href="{{ route('customer.bank-statement.index') }}">Reset</a></div>
    <form method="get" class="row g-3">
      <div class="col-md-3"><label class="amount-label" for="from">From date</label><input class="auth-input" id="from" type="date" name="from" value="{{ $statement['from']->format('Y-m-d') }}"></div>
      <div class="col-md-3"><label class="amount-label" for="to">To date</label><input class="auth-input" id="to" type="date" name="to" value="{{ $statement['to']->format('Y-m-d') }}"></div>
      <div class="col-md-3"><label class="amount-label" for="type">Transaction type</label><select class="auth-input" id="type" name="type"><option value="all">All transactions</option><option value="deposit" @selected(request('type') === 'deposit')>Deposit</option><option value="withdrawal" @selected(request('type') === 'withdrawal')>Withdraw</option><option value="transfer_out" @selected(request('type') === 'transfer_out')>Transfer sent</option><option value="transfer_in" @selected(request('type') === 'transfer_in')>Transfer received</option></select></div>
      <div class="col-md-3"><label class="amount-label" for="search">Search</label><input class="auth-input" id="search" name="search" value="{{ request('search') }}" placeholder="Reference or description"></div>
      <div class="col-12 d-flex gap-2 flex-wrap"><button class="submit-button" type="submit">Apply filter</button><a class="text-link" href="{{ route('customer.bank-statement.index', ['from' => now()->toDateString(), 'to' => now()->toDateString()]) }}">Today</a><a class="text-link" href="{{ route('customer.bank-statement.index', ['from' => now()->subDays(6)->toDateString(), 'to' => now()->toDateString()]) }}">Last 7 days</a><a class="text-link" href="{{ route('customer.bank-statement.index', ['from' => now()->startOfMonth()->toDateString(), 'to' => now()->toDateString()]) }}">This month</a><a class="text-link" href="{{ route('customer.bank-statement.index', ['from' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'to' => now()->subMonthNoOverflow()->endOfMonth()->toDateString()]) }}">Last month</a></div>
    </form>
  </div>
  <div class="metric-grid mb-4">
    <div class="card metric-card"><div class="metric-icon deposit">↗</div><div><div class="metric-label">Total deposits</div><div class="metric-value">BDT {{ number_format($statement['summary']['deposits'], 2) }}</div></div></div>
    <div class="card metric-card"><div class="metric-icon withdraw">↘</div><div><div class="metric-label">Total withdrawals</div><div class="metric-value">BDT {{ number_format($statement['summary']['withdrawals'], 2) }}</div></div></div>
    <div class="card metric-card"><div class="metric-icon withdraw">↗</div><div><div class="metric-label">Transfers sent</div><div class="metric-value">BDT {{ number_format($statement['summary']['transfers_sent'], 2) }}</div></div></div>
    <div class="card metric-card"><div class="metric-icon deposit">↙</div><div><div class="metric-label">Transfers received</div><div class="metric-value">BDT {{ number_format($statement['summary']['transfers_received'], 2) }}</div></div></div>
  </div>
  <div class="card">
    <div class="card-heading"><h2 class="card-title">Statement activity</h2><span class="history-count">{{ $statement['from']->format('M d, Y') }} – {{ $statement['to']->format('M d, Y') }}</span></div>
    <div class="row g-3 mb-3"><div class="col-md-6"><span class="metric-label">Opening balance</span><strong>BDT {{ number_format($statement['opening_balance'], 2) }}</strong></div><div class="col-md-6"><span class="metric-label">Closing balance</span><strong>BDT {{ number_format($statement['closing_balance'], 2) }}</strong></div></div>
    @if ($statement['transactions']->count())
      <div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>Date</th><th>Reference</th><th>Description</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead><tbody>
      @foreach ($statement['transactions'] as $entry)
        <tr><td>{{ $statement['transactions']->firstItem() + $loop->index }}</td><td>{{ $entry['transaction']->created_at->format('M d, Y, g:i A') }}</td><td class="small">{{ $entry['transaction']->transaction_reference }}</td><td>{{ $entry['transaction']->description ?: 'No description' }}</td><td>{{ $entry['type_label'] }}</td><td>{{ $entry['debit'] ? 'BDT '.number_format($entry['debit'], 2) : '—' }}</td><td>{{ $entry['credit'] ? 'BDT '.number_format($entry['credit'], 2) : '—' }}</td><td>BDT {{ number_format($entry['running_balance'], 2) }}</td><td>{{ ucfirst($entry['transaction']->status) }}</td><td><a class="text-link" href="{{ route('customer.bank-statement.transaction', $entry['transaction']) }}">View</a></td></tr>
      @endforeach
      </tbody></table></div>
    @else
      <div class="empty-state"><div class="empty-icon">☷</div><p>No transactions found for this statement period.</p></div>
    @endif
    {{ $statement['transactions']->links() }}
  </div>
</div>
@endsection
