@extends('layouts.app')

@section('title', 'Ball History — '.$innings->team->name)

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4">
        <h1 class="h4 mb-4">Ball History &mdash; {{ $innings->team->name }}</h1>

        <div class="list-group">
            @forelse ($deliveries as $delivery)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge rounded-pill {{ $delivery->is_wicket ? 'text-bg-danger' : ($delivery->extra_type ? 'text-bg-warning' : 'text-bg-light text-dark') }} me-2">
                            {{ $delivery->label() }}
                        </span>
                        <span>{{ $delivery->striker->user->name }}</span>
                        <span class="text-muted small">off {{ $delivery->bowler->user->name }}</span>
                        @if ($delivery->is_wicket)
                            <span class="text-danger small">
                                &mdash; {{ $delivery->dismissedPlayer->user->name }} {{ ucwords(str_replace('_', ' ', $delivery->dismissal_type->value)) }}
                            </span>
                        @endif
                    </div>
                    <span class="text-muted small">Over {{ $delivery->over->over_number }}</span>
                </div>
            @empty
                <div class="list-group-item text-center text-muted py-4">No deliveries recorded yet.</div>
            @endforelse
        </div>
    </div>
@endsection
