@extends('layouts.app')

@section('title', 'Players — Super Score')

@section('content')
    <x-app-nav active="players" />

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Players</h1>
                <p class="text-muted small mb-0">Every registered player, available to add to your teams.</p>
            </div>
            <a href="{{ route('player-profile.edit') }}" class="btn btn-primary">
                <i class="bi bi-person-badge"></i>
                {{ auth()->user()->isPlayer() ? 'Edit My Player Profile' : 'Set Up My Player Profile' }}
            </a>
        </div>

        @if ($players->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="bi bi-person-badge display-6 d-block mb-2"></i>
                    No players yet. Set up your own player profile to get started.
                </div>
            </div>
        @else
            <div class="list-group">
                @foreach ($players as $player)
                    <div class="list-group-item d-flex align-items-center gap-3">
                        @if ($player->user->profile_photo_path)
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($player->user->profile_photo_path) }}"
                                 alt="{{ $player->user->name }}" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-person"></i>
                            </div>
                        @endif
                        <div>
                            <span class="fw-semibold">{{ $player->user->name }}</span>
                            <span class="text-muted small ms-2">{{ ucwords(str_replace('_', ' ', $player->role->value)) }}</span>
                            @if ($player->user_id === auth()->id())
                                <span class="badge text-bg-light text-muted ms-1">You</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
