@extends('layouts.app')

@section('title', 'My profile | S4M Trust Bank')

@section('content')
<div class="page-content container-fluid">
  <div class="page-heading">
    <div>
      <p class="eyebrow">Account settings</p>
      <h1>My profile</h1>
      <p class="page-description">Keep your personal information up to date and secure.</p>
    </div>
  </div>

  @if (session('success'))
    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger" role="alert">
      Please correct the highlighted fields and try again.
    </div>
  @endif

  <form method="post" action="{{ route('customer.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="card h-100">
          <div class="card-heading"><h2 class="card-title">Profile photo</h2></div>
          <div class="text-center">
            @if ($user->hasProfilePhoto())
              <img id="profile-photo-preview" src="{{ route('customer.profile.photo') }}" alt="Current profile photo" class="rounded-circle mb-3" width="128" height="128" style="object-fit:cover">
            @else
              <div id="profile-photo-preview" class="avatar mx-auto mb-3" style="font-size:30px;width:128px;height:128px;display:flex;align-items:center;justify-content:center">
                {{ strtoupper(substr($user->name, 0, 2)) }}
              </div>
            @endif
            <label for="profile_photo" class="submit-button mb-2">Choose photo</label>
            <input id="profile_photo" name="profile_photo" type="file" class="d-none" accept=".jpg,.jpeg,.png,.webp">
            <p class="form-note mb-2">JPG, PNG, or WEBP. Maximum 2 MB.</p>
            @if ($user->hasProfilePhoto())
              <div class="form-check d-inline-block text-start">
                <input class="form-check-input" type="checkbox" name="remove_profile_photo" value="1" id="remove_profile_photo">
                <label class="form-check-label" for="remove_profile_photo">Remove current photo</label>
              </div>
            @endif
            @error('profile_photo') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-heading"><h2 class="card-title">Personal information</h2></div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="name">Full name</label>
              <input id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
              @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="email">Email address</label>
              <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required>
              @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="phone">Phone number</label>
              <input id="phone" name="phone" type="tel" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="01XXXXXXXXX">
              @error('phone') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="d-flex gap-2 mt-4">
            <button type="submit" class="submit-button">Update profile</button>
            <a href="{{ route('customer.profile.index') }}" class="btn btn-light d-inline-flex align-items-center">Cancel</a>
          </div>
        </div>

        <div class="card">
          <div class="card-heading"><h2 class="card-title">Bank account information</h2></div>
          @if ($account)
            <div class="row g-3">
              <div class="col-md-6"><span class="form-note d-block mb-1">Account number</span><strong>•••• {{ substr($account->account_number, -4) }}</strong></div>
              <div class="col-md-6"><span class="form-note d-block mb-1">Account type</span><strong>{{ $account->account_type }}</strong></div>
              <div class="col-md-6"><span class="form-note d-block mb-1">Account status</span><strong>{{ ucfirst($account->status) }}</strong></div>
              <div class="col-md-6"><span class="form-note d-block mb-1">Available balance</span><strong>BDT {{ number_format((float) $account->balance, 2) }}</strong></div>
              <div class="col-md-6"><span class="form-note d-block mb-1">Registration date</span><strong>{{ $account->created_at->format('M d, Y') }}</strong></div>
            </div>
          @else
            <p class="page-description mb-0">No bank account is linked to this profile.</p>
          @endif
        </div>
      </div>
    </div>
  </form>
</div>
<script>
  document.getElementById('profile_photo')?.addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (!file) return;
    const preview = document.getElementById('profile-photo-preview');
    const image = document.createElement('img');
    image.id = 'profile-photo-preview';
    image.alt = 'Selected profile photo preview';
    image.className = 'rounded-circle mb-3';
    image.width = 128;
    image.height = 128;
    image.style.objectFit = 'cover';
    image.src = URL.createObjectURL(file);
    preview.replaceWith(image);
  });
</script>
@endsection
