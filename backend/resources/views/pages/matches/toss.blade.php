@extends('layouts.app')

@section('title', 'Toss — Super Score')

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h4 mb-1 text-center">Toss</h1>
                <p class="text-muted text-center mb-4">
                    {{ $match->teams->first()?->name }} vs {{ $match->teams->last()?->name }}
                </p>

                @error('winner_team_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('matches.toss.update', $match) }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label d-block">Toss Winner</label>
                                @foreach ($match->teams as $team)
                                    <div class="form-check">
                                        <input type="radio" name="winner_team_id" id="winner_{{ $team->id }}"
                                               value="{{ $team->id }}" class="form-check-input" required
                                               @checked(old('winner_team_id') == $team->id)>
                                        <label for="winner_{{ $team->id }}" class="form-check-label">{{ $team->name }}</label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Decision</label>
                                <div class="d-flex gap-2">
                                    @foreach (\App\Enums\TossDecision::cases() as $decision)
                                        <div class="form-check flex-fill">
                                            <input type="radio" name="decision" id="decision_{{ $decision->value }}"
                                                   value="{{ $decision->value }}" class="form-check-input" required
                                                   @checked(old('decision') === $decision->value)>
                                            <label for="decision_{{ $decision->value }}" class="form-check-label text-uppercase">
                                                {{ $decision->value }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Start Match</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
