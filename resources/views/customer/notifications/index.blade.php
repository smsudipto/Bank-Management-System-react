@extends('layouts.app')

@section('title', 'Notifications | S4M Trust Bank')

@section('content')
<div class="page-content container-fluid">
  <div class="page-heading">
    <div>
      <p class="eyebrow">Account updates</p>
      <h1>Notifications</h1>
      <p class="page-description">Stay informed about your S4M Trust Bank activity.</p>
    </div>
    @if ($unreadCount > 0)
      <form method="post" action="{{ route('customer.notifications.read-all') }}">
        @csrf
        <button class="submit-button" type="submit">Mark all as read</button>
      </form>
    @endif
  </div>

  @if (session('success'))
    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
  @endif

  <div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card"><span class="form-note d-block mb-1">Total notifications</span><strong class="fs-4">{{ $totalCount }}</strong></div></div>
    <div class="col-md-4"><div class="card"><span class="form-note d-block mb-1">Unread</span><strong class="fs-4 text-success">{{ $unreadCount }}</strong></div></div>
    <div class="col-md-4"><div class="card"><span class="form-note d-block mb-1">Read</span><strong class="fs-4">{{ $readCount }}</strong></div></div>
  </div>

  <div class="card">
    <form method="get" class="row g-3 align-items-end mb-4">
      <div class="col-md-4">
        <label class="form-label" for="search">Search notifications</label>
        <input id="search" name="search" value="{{ $search }}" class="form-control" maxlength="100" placeholder="Title, message, or reference">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="filter">Filter</label>
        <select id="filter" name="filter" class="form-select">
          <option value="">All notifications</option>
          <option value="unread" @selected($filter === 'unread')>Unread</option>
          <option value="read" @selected($filter === 'read')>Read</option>
          <option value="deposit" @selected($filter === 'deposit')>Deposits</option>
          <option value="withdrawal" @selected($filter === 'withdrawal')>Withdrawals</option>
          <option value="transfer" @selected($filter === 'transfer')>Transfers</option>
        </select>
      </div>
      <div class="col-md-auto"><button class="submit-button" type="submit">Apply filters</button></div>
      <div class="col-md-auto"><a href="{{ route('customer.notifications.index') }}" class="btn btn-light">Clear</a></div>
    </form>

    @forelse ($notifications as $notification)
      @php($data = $notification->data)
      <div class="border rounded-3 p-3 mb-3 {{ $notification->read_at ? 'bg-light' : 'border-success' }}">
        <div class="d-flex justify-content-between gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span aria-hidden="true">{{ $data['category'] === 'transfer' ? '⇄' : ($data['category'] === 'deposit' ? '↓' : '↑') }}</span>
              <h2 class="h6 mb-0">{{ $data['title'] ?? 'Account notification' }}</h2>
              @unless ($notification->read_at)<span class="badge text-bg-success">Unread</span>@endunless
            </div>
            <p class="mb-1">{{ $data['message'] ?? '' }}</p>
            @if (!empty($data['transaction_reference']))
              <small class="text-muted">Reference: {{ $data['transaction_reference'] }}</small>
            @endif
            <small class="text-muted d-block">{{ $notification->created_at->format('M d, Y, g:i A') }}</small>
          </div>
          <div class="d-flex gap-2 align-items-start">
            @unless ($notification->read_at)
              <form method="post" action="{{ route('customer.notifications.read', $notification->id) }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-sm btn-outline-success" type="submit">Mark read</button>
              </form>
            @endunless
            <form method="post" action="{{ route('customer.notifications.destroy', $notification->id) }}" onsubmit="return confirm('Delete this notification?')">
              @csrf
              @method('DELETE')
              <button class="btn btn-sm btn-outline-secondary" type="submit">Delete</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center py-5">
        <h2 class="h5">No notifications found</h2>
        <p class="page-description mb-0">New account activity will appear here.</p>
      </div>
    @endforelse

    {{ $notifications->links() }}
  </div>
</div>
@endsection
