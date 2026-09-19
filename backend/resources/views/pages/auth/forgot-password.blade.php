@extends('layouts.app')

@section('title', 'Forgot Password — Super Score')

@section('content')
    <div class="d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="card shadow-sm" style="max-width: 420px; width: 100%;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-key display-6 text-primary"></i>
                    <h1 class="h4 fw-bold mt-2 mb-0">Forgot Password</h1>
                    <p class="text-muted small mb-0">We'll email you a link to reset it.</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror" required autofocus>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                </form>

                <p class="text-center text-muted mt-3 mb-0">
                    <a href="{{ route('login') }}">Back to login</a>
                </p>
            </div>
        </div>
    </div>
@endsection
