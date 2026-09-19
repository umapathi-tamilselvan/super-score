@extends('layouts.app')

@section('title', 'Create Account — Super Score')

@section('content')
    <div class="d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-trophy display-6 text-primary"></i>
                    <h1 class="h4 fw-bold mt-2 mb-0">Create Account</h1>
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="mobile_number" class="form-label">Mobile Number</label>
                        <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number') }}"
                               class="form-control @error('mobile_number') is-invalid @enderror" required>
                        @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1"
                               class="form-check-input @error('terms_accepted') is-invalid @enderror" required>
                        <label for="terms_accepted" class="form-check-label">I agree to Terms &amp; Conditions</label>
                        @error('terms_accepted')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>

                <p class="text-center text-muted mt-3 mb-0">
                    Already have an account? <a href="{{ route('login') }}">Log in</a>
                </p>
            </div>
        </div>
    </div>
@endsection
