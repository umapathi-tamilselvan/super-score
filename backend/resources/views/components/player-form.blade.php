@props(['action', 'method' => 'POST', 'player' => null, 'submitLabel' => 'Save'])

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <p class="text-muted small">
        Your name and photo come from your
        <a href="{{ route('profile.edit') }}">account profile</a> — this
        form is just your cricket details.
    </p>

    <div class="mb-3">
        <label for="date_of_birth" class="form-label">Date of Birth</label>
        <input type="date" name="date_of_birth" id="date_of_birth"
               value="{{ old('date_of_birth', $player?->date_of_birth?->toDateString()) }}"
               class="form-control @error('date_of_birth') is-invalid @enderror">
        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Role</label>
        @foreach (\App\Enums\PlayerRole::cases() as $role)
            <div class="form-check form-check-inline">
                <input type="radio" name="role" id="role_{{ $role->value }}" value="{{ $role->value }}"
                       class="form-check-input @error('role') is-invalid @enderror"
                       @checked(old('role', $player?->role?->value) === $role->value) required>
                <label for="role_{{ $role->value }}" class="form-check-label">
                    {{ ucwords(str_replace('_', ' ', $role->value)) }}
                </label>
            </div>
        @endforeach
        @error('role')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="batting_style" class="form-label">Batting Style</label>
        <input type="text" name="batting_style" id="batting_style" placeholder="e.g. Right-hand bat"
               value="{{ old('batting_style', $player?->batting_style) }}"
               class="form-control @error('batting_style') is-invalid @enderror">
        @error('batting_style')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="bowling_style" class="form-label">Bowling Style</label>
        <input type="text" name="bowling_style" id="bowling_style" placeholder="e.g. Right-arm fast"
               value="{{ old('bowling_style', $player?->bowling_style) }}"
               class="form-control @error('bowling_style') is-invalid @enderror">
        @error('bowling_style')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">{{ $submitLabel }}</button>
</form>
