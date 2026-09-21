@extends('layouts.app')

@section('title', 'Matches — Super Score')

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h4 mb-0">Matches</h1>
            <a href="{{ route('matches.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Start New Match
            </a>
        </div>

        @if ($matches->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-calendar-plus display-6 d-block mb-2"></i>
                    No matches yet. Start a new match to get going.
                </div>
            </div>
        @else
            <div class="list-group">
                @foreach ($matches as $match)
                    <a href="{{ route('matches.show', $match) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold">{{ $match->name }}</span>
                                <span class="text-muted small ms-2">{{ strtoupper($match->format->value) }} &middot; {{ $match->overs }} overs</span>
                                <div class="text-muted small">
                                    @forelse ($match->teams as $team)
                                        {{ $team->name }}@if (! $loop->last) vs @endif
                                    @empty
                                        Teams not selected yet
                                    @endforelse
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge text-bg-light text-muted">{{ ucwords(str_replace('_', ' ', $match->status->value)) }}</span>
                                <div class="text-muted small">{{ $match->date->format('d M Y') }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
