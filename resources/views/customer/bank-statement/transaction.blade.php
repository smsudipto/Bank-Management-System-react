@extends('layouts.app')
@section('title', 'Transaction details | S4M Trust Bank')
@section('content')
<div class="page-content container-fluid">
  <div class="page-heading"><div><p class="eyebrow">Statement transaction</p><h1>Transaction details</h1><p class="page-description">{{ $transaction->created_at->format('M d, Y, g:i A') }}</p></div><a class="submit-button" href="{{ route('customer.bank-statement.index') }}">Back to statement</a></div>
  <div class="card"><div class="row g-3"><div class="col-md-6"><span class="metric-label">Reference</span><strong>{{ $transaction->transaction_reference }}</strong></div><div class="col-md-6"><span class="metric-label">Status</span><strong>{{ ucfirst($transaction->status) }}</strong></div><div class="col-md-6"><span class="metric-label">Type</span><strong>{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</strong></div><div class="col-md-6"><span class="metric-label">Amount</span><strong>BDT {{ number_format((float) $transaction->amount, 2) }}</strong></div><div class="col-12"><span class="metric-label">Description</span><p>{{ $transaction->description ?: 'No description' }}</p></div></div></div>
</div>
@endsection
