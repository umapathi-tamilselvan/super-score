@extends('layouts.app')

@section('title', 'My Player Profile — Super Score')

@section('content')
    <x-app-nav active="players" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h4 mb-0">{{ $player ? 'My Player Profile' : 'Set Up My Player Profile' }}</h1>
                    <a href="{{ route('players.index') }}" class="btn btn-sm btn-outline-secondary">Back to Players</a>
                </div>

                <div class="card">
                    <div class="card-body p-4">
                        <x-player-form :action="route('player-profile.update')" method="PUT" :player="$player"
                                       :submit-label="$player ? 'Save Changes' : 'Create My Player Profile'" />
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
