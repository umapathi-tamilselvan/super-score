@extends('layouts.app')

@section('title', 'Select Teams — Super Score')

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h4 mb-1 text-center">Select Teams</h1>
                <p class="text-muted text-center mb-4">{{ $match->name }}</p>

                @error('team_b_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('matches.teams.update', $match) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="team_a_id" class="form-label">Team A</label>
                                <select name="team_a_id" id="team_a_id" class="form-select @error('team_a_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select Team A&hellip;</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected(old('team_a_id', $match->teamForSide(\App\Enums\MatchSide::TeamA)?->id) == $team->id)>
                                            {{ $team->name }} ({{ $team->user->name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('team_a_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="text-center text-muted fw-bold my-2">VS</div>

                            <div class="mb-3">
                                <label for="team_b_id" class="form-label">Team B</label>
                                <select name="team_b_id" id="team_b_id" class="form-select @error('team_b_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select Team B&hellip;</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}" @selected(old('team_b_id', $match->teamForSide(\App\Enums\MatchSide::TeamB)?->id) == $team->id)>
                                            {{ $team->name }} ({{ $team->user->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Next: Select Playing XI</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
