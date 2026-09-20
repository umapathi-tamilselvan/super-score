@extends('layouts.app')

@section('title', 'Teams — Super Score')

@section('content')
    <x-app-nav active="teams" />

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Teams</h1>
            <a href="{{ route('teams.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Create Team
            </a>
        </div>

        @if ($teams->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-people display-6 d-block mb-2"></i>
                    No teams yet. Create your first team to get started.
                </div>
            </div>
        @else
            <div class="row g-3">
                @foreach ($teams as $team)
                    <div class="col-md-4">
                        <a href="{{ route('teams.show', $team) }}" class="text-decoration-none text-reset">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    @if ($team->logo_path)
                                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($team->logo_path) }}"
                                             alt="{{ $team->name }}" class="rounded" width="48" height="48" style="object-fit: cover;">
                                    @else
                                        <div class="rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <i class="bi bi-shield"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h2 class="h6 mb-0">{{ $team->name }}</h2>
                                        <p class="text-muted small mb-0">
                                            {{ $team->short_name }}
                                            @if ($team->city) &middot; {{ $team->city }} @endif
                                        </p>
                                        <p class="text-muted small mb-0">{{ $team->players_count }} players</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
