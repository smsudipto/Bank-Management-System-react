@extends('layouts.app')
@section('title', 'Transaction history | S4M Trust Bank')
@section('content')
<div class="page-content container-fluid"><div class="page-heading"><div><p class="eyebrow">Your activity</p><h1>Transaction history</h1><p class="page-description">Review every deposit and withdrawal from your account.</p></div></div><div class="card history-card"><div class="history-header"><h2 class="card-title">All transactions</h2><span class="history-count">{{ $transactions->total() }} transaction{{ $transactions->total() === 1 ? '' : 's' }}</span></div><div class="history-list"><div class="transaction-list">@include('partials.transaction_list', ['transactions' => $transactions])</div></div>{{ $transactions->links() }}</div></div>
@endsection
