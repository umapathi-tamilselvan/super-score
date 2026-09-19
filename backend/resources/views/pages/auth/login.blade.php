@extends('layouts.app')

@section('title', 'Log In — Super Score')

@section('content')
    <div class="d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-trophy display-6 text-primary"></i>
                    <h1 class="h4 fw-bold mt-2 mb-0">Welcome Back</h1>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" id="remember" value="1" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Log In</button>
                </form>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('password.request') }}" class="text-muted small">Forgot password?</a>
                    <a href="{{ route('register') }}" class="small">Create account</a>
                </div>
            </div>
        </div>
    </div>
@endsection
