@extends('layouts.app')

@section('title', $team->name.' — Super Score')

@section('content')
    <x-app-nav active="teams" />

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @error('player_id')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                @if ($team->logo_path)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($team->logo_path) }}"
                         alt="{{ $team->name }}" class="rounded" width="56" height="56" style="object-fit: cover;">
                @endif
                <div>
                    <h1 class="h4 mb-0">{{ $team->name }}</h1>
                    <p class="text-muted small mb-0">
                        {{ $team->short_name }}
                        @if ($team->city) &middot; {{ $team->city }} @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-pencil"></i> Edit Team
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Squad ({{ $team->players->count() }})</h2>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($team->players as $player)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold">{{ $player->user->name }}</span>
                                    <span class="text-muted small ms-1">{{ ucwords(str_replace('_', ' ', $player->role->value)) }}</span>
                                    <div class="mt-1">
                                        @if ($player->pivot->is_captain)
                                            <span class="badge text-bg-primary">Captain</span>
                                        @endif
                                        @if ($player->pivot->is_vice_captain)
                                            <span class="badge text-bg-secondary">Vice Captain</span>
                                        @endif
                                        @if ($player->pivot->is_wicket_keeper)
                                            <span class="badge text-bg-info">Wicket Keeper</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Assign Role
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @foreach ($roles as $role)
                                                <li>
                                                    <form method="POST" action="{{ route('teams.role', $team) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="player_id" value="{{ $player->id }}">
                                                        <input type="hidden" name="role" value="{{ $role->value }}">
                                                        <button type="submit" class="dropdown-item">
                                                            {{ ucwords(str_replace('_', ' ', $role->value)) }}
                                                        </button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <form method="POST" action="{{ route('teams.players.destroy', [$team, $player]) }}"
                                          onsubmit="return confirm('Remove {{ $player->user->name }} from the squad?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                No players in the squad yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0">Add Player</h2>
                    </div>
                    <div class="card-body">
                        @if ($availablePlayers->isEmpty())
                            <p class="text-muted small">
                                Every registered player is already in this squad. New players appear here once
                                someone sets up their own player profile.
                            </p>
                        @else
                            <form method="POST" action="{{ route('teams.players.store', $team) }}">
                                @csrf
                                <div class="mb-3">
                                    <select name="player_id" class="form-select" required>
                                        <option value="" disabled selected>Select a player&hellip;</option>
                                        @foreach ($availablePlayers as $player)
                                            <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm w-100">Add to Squad</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
