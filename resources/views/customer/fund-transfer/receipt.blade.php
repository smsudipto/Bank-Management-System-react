@extends('layouts.app')
@section('title', 'Transfer receipt | S4M Trust Bank')
@section('content')
@php
  $mask = fn (string $number): string => '•••• '.substr($number, -4);
  $sent = $accountId === $transfer->sender_account_id;
@endphp
<div class="page-content container-fluid">
  <div class="page-heading"><div><p class="eyebrow">S4M Trust Bank</p><h1>Transfer receipt</h1><p class="page-description">Transaction reference {{ $transfer->transfer_reference }}</p></div><button class="submit-button" type="button" onclick="window.print()">Print receipt</button></div>
  @include('partials.messages')
  <div class="card">
    <div class="history-header"><h2 class="card-title">Transfer {{ ucfirst($transfer->status) }}</h2><span>{{ $transfer->created_at->format('M d, Y, g:i A') }}</span></div>
    <div class="row g-3">
      <div class="col-md-6"><strong>Sender</strong><p>{{ $transfer->senderAccount->user->name }}<br>{{ $mask($transfer->senderAccount->account_number) }}</p></div>
      <div class="col-md-6"><strong>Receiver</strong><p>{{ $transfer->receiverAccount->user->name }}<br>{{ $mask($transfer->receiverAccount->account_number) }}</p></div>
      <div class="col-md-4"><strong>Transfer amount</strong><p>BDT {{ number_format((float) $transfer->amount, 2) }}</p></div>
      <div class="col-md-4"><strong>Transfer fee</strong><p>BDT {{ number_format((float) $transfer->fee, 2) }}</p></div>
      <div class="col-md-4"><strong>{{ $sent ? 'Total deducted' : 'Total received' }}</strong><p>BDT {{ number_format((float) $transfer->amount + (float) $transfer->fee, 2) }}</p></div>
      <div class="col-12"><strong>Description</strong><p>{{ $transfer->description ?: 'No description' }}</p></div>
    </div>
  </div>
</div>
@endsection
