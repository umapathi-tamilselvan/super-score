@extends('layouts.app')

@section('title', 'Create Match — Super Score')

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <h1 class="h4 mb-4">Create Match</h1>

                <div class="card">
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('matches.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">Match Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}"
                                       placeholder="Chennai Warriors vs Tamil Kings"
                                       class="form-control @error('name') is-invalid @enderror" required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label d-block">Format</label>
                                @foreach (\App\Enums\MatchFormat::cases() as $format)
                                    <div class="form-check form-check-inline">
                                        <input type="radio" name="format" id="format_{{ $format->value }}"
                                               value="{{ $format->value }}" class="form-check-input @error('format') is-invalid @enderror"
                                               @checked(old('format') === $format->value) required>
                                        <label for="format_{{ $format->value }}" class="form-check-label">
                                            {{ strtoupper($format->value) }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('format')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="overs" class="form-label">Overs</label>
                                <input type="number" name="overs" id="overs" min="1" max="50" value="{{ old('overs', 20) }}"
                                       class="form-control @error('overs') is-invalid @enderror" required>
                                @error('overs')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" name="date" id="date" value="{{ old('date') }}"
                                           class="form-control @error('date') is-invalid @enderror" required>
                                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="time" class="form-label">Time</label>
                                    <input type="time" name="time" id="time" value="{{ old('time') }}"
                                           class="form-control @error('time') is-invalid @enderror">
                                    @error('time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="venue" class="form-label">Venue</label>
                                <input type="text" name="venue" id="venue" value="{{ old('venue') }}"
                                       class="form-control @error('venue') is-invalid @enderror">
                                @error('venue')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Next: Select Teams</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
