@extends('layouts.app')

@section('title', 'Dashboard — Super Score')

@section('content')
    <x-app-nav active="dashboard" />

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <h1 class="h4 mb-4">Hi, {{ $user->name }} 👋</h1>

        <div class="d-grid mb-4">
            <a href="{{ route('matches.create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle"></i> Start New Match
            </a>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><i class="bi bi-broadcast text-danger"></i> Live Matches</h2>
                        @forelse ($liveMatches as $match)
                            <a href="{{ route('matches.show', $match) }}" class="d-block text-decoration-none text-reset small mb-1">{{ $match->name }}</a>
                        @empty
                            <p class="text-muted small mb-0">No live matches yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><i class="bi bi-calendar-event text-primary"></i> Upcoming Matches</h2>
                        @forelse ($upcomingMatches as $match)
                            <a href="{{ route('matches.show', $match) }}" class="d-block text-decoration-none text-reset small mb-1">{{ $match->name }}</a>
                        @empty
                            <p class="text-muted small mb-0">No upcoming matches yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h6"><i class="bi bi-clock-history"></i> Recent Matches</h2>
                        @forelse ($recentMatches as $match)
                            <a href="{{ route('matches.show', $match) }}" class="d-block text-decoration-none text-reset small mb-1">{{ $match->name }}</a>
                        @empty
                            <p class="text-muted small mb-0">No recent matches yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex gap-4 text-muted">
            <a href="{{ route('teams.index') }}" class="text-decoration-none text-reset">
                <i class="bi bi-people"></i> Teams
            </a>
            <a href="{{ route('players.index') }}" class="text-decoration-none text-reset">
                <i class="bi bi-person-badge"></i> Players
            </a>
            <span><i class="bi bi-bar-chart"></i> Statistics <span class="badge text-bg-light">Phase 6</span></span>
        </div>
    </div>
@endsection
