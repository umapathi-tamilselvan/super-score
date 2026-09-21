@extends('layouts.app')

@section('title', 'Start Innings — Super Score')

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h4 mb-1 text-center">{{ $battingTeam->name }}</h1>
                <p class="text-muted text-center mb-4">Opening the innings &mdash; {{ $match->name }}</p>

                @error('team_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('innings.store', $match) }}">
                            @csrf
                            <input type="hidden" name="team_id" value="{{ $battingTeam->id }}">

                            <div class="mb-3">
                                <label class="form-label">Opening Batter</label>
                                <select name="striker_player_id" class="form-select @error('striker_player_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select&hellip;</option>
                                    @foreach ($battingTeam->players as $player)
                                        <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('striker_player_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Non-Striker</label>
                                <select name="non_striker_player_id" class="form-select @error('non_striker_player_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select&hellip;</option>
                                    @foreach ($battingTeam->players as $player)
                                        <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('non_striker_player_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Opening Bowler ({{ $bowlingTeam->name }})</label>
                                <select name="opening_bowler_player_id" class="form-select @error('opening_bowler_player_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select&hellip;</option>
                                    @foreach ($bowlingTeam->players as $player)
                                        <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                                    @endforeach
                                </select>
                                @error('opening_bowler_player_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Start Innings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
