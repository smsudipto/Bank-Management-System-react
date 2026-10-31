@extends('layouts.app')
@section('title', 'Security settings | S4M Trust Bank')
@section('content')
<div class="page-content container-fluid">
  <div class="page-heading">
    <div><p class="eyebrow">Protect your account</p><h1>Security settings</h1><p class="page-description">Manage your banking account protection and security preferences.</p></div>
  </div>
  @include('partials.messages')
  @if (session('recovery_codes'))
    <div class="alert alert-warning"><strong>Save these recovery codes now.</strong><div class="row mt-2">@foreach (session('recovery_codes') as $code)<code class="col-md-3 mb-1">{{ $code }}</code>@endforeach</div><small>They are shown once and cannot be recovered later.</small></div>
  @endif
  <div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card h-100"><small class="text-muted">Account security</small><h2 class="h5 mt-2">{{ $settings->two_factor_enabled ? 'Strong protection enabled' : 'Action recommended' }}</h2><span class="badge {{ $settings->two_factor_enabled ? 'bg-success' : 'bg-warning text-dark' }}">{{ $settings->two_factor_enabled ? 'Protected' : '2FA not enabled' }}</span></div></div>
    <div class="col-md-4"><div class="card h-100"><small class="text-muted">Last password changed</small><h2 class="h5 mt-2">{{ $settings->last_password_changed_at?->diffForHumans() ?? 'Not recorded' }}</h2></div></div>
    <div class="col-md-4"><div class="card h-100"><small class="text-muted">Last successful login</small><h2 class="h5 mt-2">{{ $settings->last_successful_login_at?->diffForHumans() ?? 'Not recorded' }}</h2></div></div>
  </div>
  <style>
    .security-settings-layout {
      display: grid;
      gap: 1.5rem;
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .security-settings-column {
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
      min-width: 0;
    }

    @media (max-width: 991.98px) {
      .security-settings-layout {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <div class="security-settings-layout">
    <div class="security-settings-column">
      <div class="card"><h2 class="card-title">🔑 Change password</h2><p class="page-description my-3">Use a unique password to keep your account protected.</p>
        @if (session('password_verification_pending'))
          <div class="alert alert-info">A 6-digit verification code was sent to your registered email. It expires in 5 minutes.</div>
          <form method="post" action="{{ route('customer.security.password.verify') }}" class="vstack gap-3">@csrf
            <input class="form-control" type="text" name="verification_code" inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="Email verification code" required autocomplete="one-time-code">
            @if ($errors->password->any())<div class="text-danger small">{{ $errors->password->first() }}</div>@endif
            <button class="submit-button align-self-start" type="submit">Verify and change password</button>
          </form>
          <form method="post" action="{{ route('customer.security.password.resend') }}" class="mt-2">@csrf<button class="btn btn-link btn-sm px-0" type="submit">Resend verification code</button></form>
        @else
          <form method="post" action="{{ route('customer.security.password.update') }}" class="vstack gap-3">@csrf
            <input class="form-control" type="password" name="current_password" placeholder="Current password" required autocomplete="current-password">
            <input class="form-control" type="password" name="password" placeholder="New password" required autocomplete="new-password">
            <input class="form-control" type="password" name="password_confirmation" placeholder="Confirm new password" required autocomplete="new-password">
            @if ($errors->password->any())<div class="text-danger small">{{ $errors->password->first() }}</div>@endif
            <button class="submit-button align-self-start" type="submit">Send verification code</button>
          </form>
        @endif
      </div>
      <div class="card"><h2 class="card-title">🖥️ Login history</h2><p class="page-description my-3">Recent successful and failed access attempts for this account.</p>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Status</th><th>Device</th><th>Date</th></tr></thead><tbody>@forelse ($loginHistory as $login)<tr><td><span class="badge {{ $login->login_status === 'success' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($login->login_status) }}</span></td><td>{{ $login->browser }} / {{ $login->device_type }}<br><small class="text-muted">{{ $login->ip_address }}</small></td><td>{{ $login->created_at->format('M j, Y H:i') }}</td></tr>@empty<tr><td colspan="3" class="text-muted">No login records yet.</td></tr>@endforelse</tbody></table></div>
        {{ $loginHistory->links('pagination::bootstrap-5') }}
      </div>
      <div class="card"><h2 class="card-title">🔔 Login alerts</h2><p class="page-description my-3">Choose which security events should be highlighted for you.</p>
        <form method="post" action="{{ route('customer.security.alerts.update') }}" class="vstack gap-2">@csrf
          @foreach (['new_device_login_alert'=>'New device login','successful_login_alert'=>'Successful login','failed_login_alert'=>'Failed login','suspicious_login_alert'=>'Suspicious login','password_change_alert'=>'Password changes','two_factor_security_alert'=>'2FA security changes'] as $name => $label)<label class="form-check form-switch"><input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" {{ $settings->$name ? 'checked' : '' }}><span class="form-check-label">{{ $label }}</span></label>@endforeach
          <button class="submit-button align-self-start mt-2" type="submit">Save alert preferences</button>
        </form>
      </div>
    </div>
    <div class="security-settings-column">
      <div class="card"><h2 class="card-title">🛡️ Two-factor authentication</h2><p class="page-description my-3">Add a time-based code from an authenticator app.</p>
        @if ($settings->two_factor_enabled)
          <span class="badge bg-success mb-3">Enabled</span>
          <form method="post" action="{{ route('customer.security.two-factor.recovery-codes') }}" class="mb-2">@csrf<input class="form-control mb-2" type="password" name="current_password" placeholder="Confirm password to regenerate codes" required><button class="btn btn-outline-success btn-sm">Regenerate recovery codes</button></form>
          <form method="post" action="{{ route('customer.security.two-factor.disable') }}" class="mt-3">@csrf<input class="form-control mb-2" type="password" name="current_password" placeholder="Confirm password to disable" required><button class="btn btn-outline-danger btn-sm">Disable 2FA</button></form>
        @else
          <span class="badge bg-warning text-dark mb-3">Not enabled</span>
          <form method="post" action="{{ route('customer.security.two-factor.start') }}">@csrf<button class="submit-button" type="submit">Set up authenticator</button></form>
          @if (session('two_factor_setup'))
            <div class="border rounded p-3 mt-3 text-center"><img src="{{ route('customer.security.two-factor.qr-code') }}" alt="Authenticator setup QR code" width="180" height="180" class="img-fluid mb-2"><p class="small text-muted mb-2">Manual setup key: <code>{{ session('security_2fa_secret') }}</code> <button type="button" class="btn btn-outline-secondary btn-sm" onclick="navigator.clipboard.writeText('{{ session('security_2fa_secret') }}')">Copy</button></p>
              <form method="post" action="{{ route('customer.security.two-factor.verify') }}">@csrf<input class="form-control mb-2" name="code" inputmode="numeric" pattern="\d{6}" placeholder="6-digit authenticator code" required><button class="btn btn-success">Verify and enable</button></form>
              @if ($errors->two_factor->any())<div class="text-danger small mt-2">{{ $errors->two_factor->first() }}</div>@endif
            </div>
          @endif
        @endif
      </div>
      <div class="card"><h2 class="card-title">💻 Active sessions</h2><p class="page-description my-3">Review sessions currently signed in to your account.</p>
        @if (config('session.driver') !== 'database')<div class="alert alert-info small">Active session management is unavailable with the current session driver.</div>@else
          <form method="post" action="{{ route('customer.security.sessions.destroy-others') }}" class="mb-3">@csrf<button class="btn btn-outline-danger btn-sm">Sign out all other sessions</button></form>
          @forelse ($sessions as $session)<div class="d-flex justify-content-between border-top py-2"><span>{{ $session->user_agent ? \Illuminate\Support\Str::limit($session->user_agent, 55) : 'Unknown device' }}<br><small class="text-muted">{{ $session->ip_address }} · {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</small></span>@if ($session->id === request()->session()->getId())<span class="badge bg-success h-50">Current</span>@else<form method="post" action="{{ route('customer.security.sessions.destroy', $session->id) }}">@csrf @method('DELETE')<button class="btn btn-link btn-sm text-danger">Sign out</button></form>@endif</div>@empty<p class="text-muted small">No database-backed sessions found.</p>@endforelse
        @endif
      </div>
      <div class="card"><h2 class="card-title">📋 Security activity</h2><p class="page-description my-3">A read-only record of important account security events.</p>
        @forelse ($activityLog as $activity)<div class="border-top py-2"><strong>{{ str_replace('_', ' ', ucfirst($activity->activity_type)) }}</strong><br><span class="small text-muted">{{ $activity->description }} · {{ $activity->created_at->format('M j, Y H:i') }}</span></div>@empty<p class="text-muted small">No security activity recorded yet.</p>@endforelse
        {{ $activityLog->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
