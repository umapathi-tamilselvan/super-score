@extends('layouts.app')

@section('title', 'Verify Account — Super Score')

@section('content')
    <div class="d-flex align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="card shadow-sm text-center" style="max-width: 420px; width: 100%;">
            <div class="card-body p-4">
                <i class="bi bi-shield-check display-6 text-primary"></i>
                <h1 class="h4 fw-bold mt-2">Verify Your Account</h1>
                <p class="text-muted">Enter the verification code sent to your email.</p>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('otp.verify') }}">
                    @csrf

                    <div class="mb-3">
                        <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                               maxlength="{{ config('otp.length') }}"
                               class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                               style="letter-spacing: 6px;" required autofocus>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Verify</button>
                </form>

                <form method="POST" action="{{ route('otp.resend') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-link p-0">Didn't receive a code? Resend OTP</button>
                </form>
            </div>
        </div>
    </div>
@endsection
