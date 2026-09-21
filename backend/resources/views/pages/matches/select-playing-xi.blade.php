@extends('layouts.app')

@section('title', 'Select Playing XI — Super Score')

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4"
         x-data="{
            selected: [],
            players: @js($team->players->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->user->name])->values()),
            size: {{ config('cricket.playing_xi_size') }},
            get chosen() { return this.players.filter(p => this.selected.includes(p.id)); }
         }">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <h1 class="h4 mb-1 text-center">Select Playing XI</h1>
                <p class="text-muted text-center mb-4">{{ $team->name }} &mdash; {{ $match->name }}</p>

                @error('player_ids')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                @error('team')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="card mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <span class="h6 mb-0">Squad</span>
                        <span class="text-muted small">
                            <span x-text="selected.length"></span> / {{ config('cricket.playing_xi_size') }} selected
                        </span>
                    </div>
                    <div class="list-group list-group-flush">
                        @forelse ($team->players as $player)
                            <label class="list-group-item d-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input" name="player_ids[]" value="{{ $player->id }}"
                                       x-model="selected"
                                       :disabled="!selected.includes('{{ $player->id }}') && selected.length >= size">
                                <span>{{ $player->user->name }}</span>
                                <span class="text-muted small">{{ ucwords(str_replace('_', ' ', $player->role->value)) }}</span>
                            </label>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                This team's squad is empty — add players to it first.
                            </div>
                        @endforelse
                    </div>
                </div>

                <form method="POST" action="{{ route('matches.playing-xi.update', [$match, $team]) }}">
                    @csrf
                    @method('PUT')

                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="player_ids[]" :value="id">
                    </template>

                    <div class="card">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label">Captain</label>
                                <select name="captain_player_id" class="form-select @error('captain_player_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select captain&hellip;</option>
                                    <template x-for="player in chosen" :key="player.id">
                                        <option :value="player.id" x-text="player.name"></option>
                                    </template>
                                </select>
                                @error('captain_player_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Wicket Keeper</label>
                                <select name="wicket_keeper_player_id" class="form-select @error('wicket_keeper_player_id') is-invalid @enderror" required>
                                    <option value="" disabled selected>Select wicket keeper&hellip;</option>
                                    <template x-for="player in chosen" :key="player.id">
                                        <option :value="player.id" x-text="player.name"></option>
                                    </template>
                                </select>
                                @error('wicket_keeper_player_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Vice Captain <span class="text-muted">(optional)</span></label>
                                <select name="vice_captain_player_id" class="form-select @error('vice_captain_player_id') is-invalid @enderror">
                                    <option value="">None</option>
                                    <template x-for="player in chosen" :key="player.id">
                                        <option :value="player.id" x-text="player.name"></option>
                                    </template>
                                </select>
                                @error('vice_captain_player_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100" :disabled="selected.length !== size">
                                Save Playing XI
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
