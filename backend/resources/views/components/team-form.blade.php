@props(['action', 'method' => 'POST', 'team' => null, 'submitLabel' => 'Save'])

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="mb-3 text-center">
        @if ($team?->logo_path)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($team->logo_path) }}"
                 alt="Team logo" class="rounded mb-2" width="72" height="72" style="object-fit: cover;">
        @endif
        <label for="logo" class="form-label d-block">Team Logo</label>
        <input type="file" name="logo" id="logo" accept="image/*"
               class="form-control @error('logo') is-invalid @enderror">
        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Team Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $team?->name) }}"
               class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="short_name" class="form-label">Short Name</label>
        <input type="text" name="short_name" id="short_name" maxlength="10"
               value="{{ old('short_name', $team?->short_name) }}"
               class="form-control @error('short_name') is-invalid @enderror" required>
        @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
        <label for="city" class="form-label">City / Location</label>
        <input type="text" name="city" id="city" value="{{ old('city', $team?->city) }}"
               class="form-control @error('city') is-invalid @enderror">
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">{{ $submitLabel }}</button>
</form>
