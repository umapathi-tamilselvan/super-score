@extends('layouts.app')

@section('title', 'Dashboard — Super Score')

@section('content')
    <div class="container py-4">
        <nav class="d-flex justify-content-between align-items-center mb-4">
            <span class="fw-bold"><i class="bi bi-trophy text-primary"></i> Super Score</span>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                    <i class="bi bi-person-circle"></i> {{ $user->name }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
                </form>
            </div>
        </nav>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <h1 class="h4 mb-4">Hi, {{ $user->name }} 👋</h1>

        <div class="d-grid mb-4">
            <button class="btn btn-primary btn-lg" disabled>
                <i class="bi bi-plus-circle"></i> Start New Match
                <span class="badge text-bg-light text-muted ms-2">Coming in Phase 3</span>
            </button>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><i class="bi bi-broadcast text-danger"></i> Live Matches</h2>
                        <p class="text-muted small mb-0">No live matches yet.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><i class="bi bi-calendar-event text-primary"></i> Upcoming Matches</h2>
                        <p class="text-muted small mb-0">No upcoming matches yet.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><i class="bi bi-clock-history"></i> Recent Matches</h2>
                        <p class="text-muted small mb-0">No recent matches yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex gap-4 text-muted">
            <span><i class="bi bi-people"></i> Teams <span class="badge text-bg-light">Phase 2</span></span>
            <span><i class="bi bi-person-badge"></i> Players <span class="badge text-bg-light">Phase 2</span></span>
            <span><i class="bi bi-bar-chart"></i> Statistics <span class="badge text-bg-light">Phase 6</span></span>
        </div>
    </div>
@endsection
