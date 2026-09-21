@extends('layouts.app')

@section('title', $match->name.' — Super Score')

@section('content')
    <x-app-nav active="matches" />

    @php
        $teamsSelected = $match->hasBothTeamsSelected();
        $xiComplete = $teamsSelected && $match->hasBothPlayingXiComplete();
        $tossDone = $match->toss !== null;
        $currentInnings = $match->innings->firstWhere('status', \App\Enums\InningsStatus::InProgress);
        $lastInnings = $match->innings->sortByDesc('innings_number')->first();
        $matchComplete = $match->innings->count() === 2 && $match->innings->every(fn ($i) => $i->status === \App\Enums\InningsStatus::Completed);
    @endphp

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">{{ $match->name }}</h1>
                <p class="text-muted small mb-0">
                    {{ strtoupper($match->format->value) }} &middot; {{ $match->overs }} overs
                    &middot; {{ $match->date->format('d M Y') }}
                    @if ($match->venue) &middot; {{ $match->venue }} @endif
                </p>
            </div>
            <span class="badge text-bg-light text-muted">{{ ucwords(str_replace('_', ' ', $match->status->value)) }}</span>
        </div>

        {{-- Setup progress / next step --}}
        @if (! $teamsSelected)
            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                <span>Select the two teams for this match.</span>
                <a href="{{ route('matches.teams.edit', $match) }}" class="btn btn-primary btn-sm">Select Teams</a>
            </div>
        @elseif (! $xiComplete)
            @foreach ($match->teams as $team)
                @unless ($match->hasCompletePlayingXiFor($team))
                    <div class="alert alert-primary d-flex justify-content-between align-items-center">
                        <span>Select the playing XI for {{ $team->name }}.</span>
                        <a href="{{ route('matches.playing-xi.edit', [$match, $team]) }}" class="btn btn-primary btn-sm">Select Playing XI</a>
                    </div>
                @endunless
            @endforeach
        @elseif (! $tossDone)
            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                <span>Both playing XIs are set. Record the toss to finish setup.</span>
                <a href="{{ route('matches.toss.edit', $match) }}" class="btn btn-primary btn-sm">Record Toss</a>
            </div>
        @elseif ($currentInnings)
            <div class="alert alert-danger d-flex justify-content-between align-items-center">
                <span><i class="bi bi-broadcast"></i> {{ $currentInnings->team->name }} innings in progress.</span>
                <a href="{{ route('innings.live', $currentInnings) }}" class="btn btn-danger btn-sm">Continue Scoring</a>
            </div>
        @elseif ($matchComplete)
            <div class="alert alert-success">
                <i class="bi bi-trophy"></i> Match complete.
                <a href="{{ route('innings.live', $lastInnings) }}">View final scorecard</a>
            </div>
        @elseif ($lastInnings)
            <div class="alert alert-primary d-flex justify-content-between align-items-center">
                <span>{{ $lastInnings->team->name }}'s innings is over. Start the second innings.</span>
                <a href="{{ route('innings.create', $match) }}" class="btn btn-primary btn-sm">Start 2nd Innings</a>
            </div>
        @else
            <div class="alert alert-success d-flex justify-content-between align-items-center">
                <span>
                    <i class="bi bi-check-circle"></i>
                    <strong>{{ $match->toss->winner->name }}</strong> won the toss and chose to
                    <strong>{{ strtoupper($match->toss->decision->value) }}</strong>.
                    {{ $match->toss->battingTeam()->name }} will bat first.
                </span>
                <a href="{{ route('innings.create', $match) }}" class="btn btn-success btn-sm">Start Match</a>
            </div>
        @endif

        @if ($teamsSelected)
            <div class="row g-4 mt-1">
                @foreach ($match->teams as $team)
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="h6 mb-0">{{ $team->name }}</span>
                                @if ($match->hasCompletePlayingXiFor($team))
                                    <span class="badge text-bg-success">XI Set</span>
                                @else
                                    <span class="badge text-bg-light text-muted">XI Pending</span>
                                @endif
                            </div>
                            <div class="list-group list-group-flush">
                                @forelse ($match->playingXi->where('team_id', $team->id) as $entry)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $entry->player->user->name }}</span>
                                        <span>
                                            @if ($entry->is_captain)<span class="badge text-bg-primary">C</span>@endif
                                            @if ($entry->is_vice_captain)<span class="badge text-bg-secondary">VC</span>@endif
                                            @if ($entry->is_wicket_keeper)<span class="badge text-bg-info">WK</span>@endif
                                        </span>
                                    </div>
                                @empty
                                    <div class="list-group-item text-muted text-center py-3">Playing XI not selected yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
