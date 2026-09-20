@props(['active' => null])

<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="bi bi-trophy-fill text-primary"></i> Super Score
        </a>
        <div class="navbar-nav flex-row gap-3 me-auto ms-4">
            <a href="{{ route('dashboard') }}" class="nav-link {{ $active === 'dashboard' ? 'fw-semibold text-primary' : '' }}">Dashboard</a>
            <a href="{{ route('teams.index') }}" class="nav-link {{ $active === 'teams' ? 'fw-semibold text-primary' : '' }}">Teams</a>
            <a href="{{ route('players.index') }}" class="nav-link {{ $active === 'players' ? 'fw-semibold text-primary' : '' }}">Players</a>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
            </form>
        </div>
    </div>
</nav>
