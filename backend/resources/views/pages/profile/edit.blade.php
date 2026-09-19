@extends('layouts.app')

@section('title', ($isFirstTimeSetup ? 'Complete Your Profile' : 'Edit Profile').' — Super Score')

@section('content')
    <div class="d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="card shadow-sm" style="max-width: 480px; width: 100%;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-person-circle display-6 text-primary"></i>
                    <h1 class="h4 fw-bold mt-2 mb-0">
                        {{ $isFirstTimeSetup ? 'Complete Your Profile' : 'Edit Profile' }}
                    </h1>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 text-center">
                        @if ($user->profile_photo_path)
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}"
                                 alt="Profile photo" class="rounded-circle mb-2" width="80" height="80" style="object-fit: cover;">
                        @endif
                        <label for="photo" class="form-label d-block">Profile Photo</label>
                        <input type="file" name="photo" id="photo" accept="image/*"
                               class="form-control @error('photo') is-invalid @enderror">
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="mobile_number" class="form-label">Mobile</label>
                        <input type="text" name="mobile_number" id="mobile_number"
                               value="{{ old('mobile_number', $user->mobile_number) }}"
                               class="form-control @error('mobile_number') is-invalid @enderror" required>
                        @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Preferred Role</label>
                        @foreach (\App\Enums\PreferredRole::cases() as $role)
                            <div class="form-check form-check-inline">
                                <input type="radio" name="preferred_role" id="role_{{ $role->value }}"
                                       value="{{ $role->value }}" class="form-check-input"
                                       @checked(old('preferred_role', $user->preferred_role?->value) === $role->value)>
                                <label for="role_{{ $role->value }}" class="form-check-label">
                                    {{ ucwords(str_replace('_', ' ', $role->value)) }}
                                </label>
                            </div>
                        @endforeach
                        @error('preferred_role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        {{ $isFirstTimeSetup ? 'Continue' : 'Save Changes' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
