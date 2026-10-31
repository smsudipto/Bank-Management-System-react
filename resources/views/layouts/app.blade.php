<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'S4M Trust Bank')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  </head>
  <body>
    @auth
    <div class="app-shell">
      <aside class="sidebar" id="sidebar">
        <div class="brand">
          <div class="brand-mark">S4M</div>
          <div><span class="brand-name">S4M Trust Bank</span><span class="brand-subtitle">PERSONAL BANKING</span></div>
          <button class="mobile-menu" type="button" data-close-menu aria-label="Close navigation">✕</button>
        </div>
        <span class="nav-label">Menu</span>
        <nav class="nav-list" aria-label="Main navigation">
          <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">▦<span>Dashboard</span></a>
          <a href="{{ route('deposit') }}" class="nav-link {{ request()->routeIs('deposit*') ? 'active' : '' }}">▣<span>Deposit money</span></a>
          <a href="{{ route('withdrawal') }}" class="nav-link {{ request()->routeIs('withdrawal*') ? 'active' : '' }}">⌁<span>Withdraw money</span></a>
          <a href="{{ route('transactions') }}" class="nav-link {{ request()->routeIs('transactions') ? 'active' : '' }}">☷<span>Transaction history</span></a>
          <a href="{{ route('customer.fund-transfer.index') }}" class="nav-link {{ request()->routeIs('customer.fund-transfer.*') ? 'active' : '' }}">⇄<span>Fund transfer</span></a>
          <a href="{{ route('customer.bank-statement.index') }}" class="nav-link {{ request()->routeIs('customer.bank-statement.*') ? 'active' : '' }}">▤<span>Bank statement</span></a>
          <a href="{{ route('customer.profile.index') }}" class="nav-link {{ request()->routeIs('customer.profile.*') ? 'active' : '' }}">◉<span>My profile</span></a>
          <a href="{{ route('customer.notifications.index') }}" class="nav-link {{ request()->routeIs('customer.notifications.*') ? 'active' : '' }}">♢<span>Notifications</span></a>
          <a href="{{ route('customer.security.index') }}" class="nav-link {{ request()->routeIs('customer.security.*') ? 'active' : '' }}">⚿<span>Security settings</span></a>
        </nav>
        <div class="sidebar-footer">Your finances, simplified.<br>Securely managed for you.</div>
      </aside>
      <div class="main-area">
        <header class="topbar">
          <button class="mobile-menu" type="button" data-open-menu aria-label="Open navigation">☰</button>
          <span class="topbar-title">Personal banking overview</span>
          <div class="profile">
            <a href="{{ route('customer.notifications.index') }}" class="text-decoration-none me-3 position-relative" aria-label="Notifications">♢@if (auth()->user()->unreadNotifications()->count() > 0)<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">{{ auth()->user()->unreadNotifications()->count() }}</span>@endif</a>
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div><span class="profile-name">{{ auth()->user()->name }}</span><span class="profile-role">Personal account</span><form method="post" action="{{ route('logout') }}">@csrf<button type="submit" class="profile-role" style="border:0;background:none;padding:0">Sign out</button></form></div>
          </div>
        </header>
        <main>@yield('content')</main>
      </div>
    </div>
    @else
      @yield('auth_content')
    @endauth
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
  </body>
</html>
