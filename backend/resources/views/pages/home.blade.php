@extends('layouts.app')

@section('title', 'Super Score — Cricket Scoring & Statistics')

@push('styles')
    <style>
        .ss-hero {
            background: linear-gradient(180deg, var(--bs-primary-bg-subtle) 0%, var(--bs-body-bg) 55%);
        }

        .ss-navbar {
            backdrop-filter: saturate(180%) blur(6px);
            background-color: rgba(var(--bs-body-bg-rgb), 0.85);
        }

        /* Stylized live-score mockup in the hero */
        .ss-scorecard {
            max-width: 360px;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.15);
        }

        .ss-scorecard .ss-run-btn {
            width: 2.25rem;
            height: 2.25rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .ss-feature-icon {
            width: 3rem;
            height: 3rem;
            font-size: 1.25rem;
        }

        .ss-step-number {
            width: 2.5rem;
            height: 2.5rem;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .ss-step:not(:last-child) .ss-step-connector {
            display: block;
        }

        .ss-step-connector {
            display: none;
            height: 2px;
        }

        @media (min-width: 992px) {
            .ss-step-connector {
                display: block;
            }
        }
    </style>
@endpush

@section('content')
    {{-- Navbar --}}
    <nav class="navbar navbar-expand sticky-top ss-navbar border-bottom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ route('home') }}">
                <i class="bi bi-trophy-fill text-primary"></i> Super Score
            </a>
            <div class="d-flex gap-2 ms-auto">
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Log In</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Create Account</a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="ss-hero py-5">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis px-3 py-2 mb-3">
                        <i class="bi bi-stars"></i> Built for local cricket
                    </span>
                    <h1 class="display-4 fw-bold mb-3">Score every match like a pro.</h1>
                    <p class="lead text-muted mb-4">
                        Super Score turns your phone or laptop into a full cricket scoring desk —
                        ball-by-ball scoring, live scorecards, team &amp; player management, and
                        stats that update themselves, from toss to trophy.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-play-fill"></i> Start Scoring Free
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4">
                            Log In
                        </a>
                    </div>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-check-circle text-success"></i> No credit card required
                        &nbsp;·&nbsp;
                        <i class="bi bi-check-circle text-success"></i> Free to start
                    </p>
                </div>

                <div class="col-lg-6 d-flex justify-content-center">
                    {{-- Stylized live-scoring mockup --}}
                    <div class="ss-scorecard card border-0 overflow-hidden">
                        <div class="card-body bg-primary text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold small text-uppercase">Chennai Warriors</span>
                                <span class="badge text-bg-danger"><i class="bi bi-broadcast"></i> Live</span>
                            </div>
                            <div class="fs-2 fw-bold">142 / 3 <span class="fs-6 fw-normal opacity-75">(15.3 ov)</span></div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-semibold">Arun *</span>
                                <span class="text-muted">64 (42)</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-3">
                                <span class="fw-semibold">Kumar</span>
                                <span class="text-muted">28 (25)</span>
                            </div>
                            <div class="d-flex gap-2 mb-3">
                                @foreach (['1', '4', 'W', '0', '2', '6'] as $ball)
                                    <span class="badge rounded-circle d-flex align-items-center justify-content-center ss-run-btn
                                        {{ $ball === 'W' ? 'text-bg-danger' : ($ball === '4' || $ball === '6' ? 'text-bg-success' : 'text-bg-light text-dark') }}">
                                        {{ $ball }}
                                    </span>
                                @endforeach
                            </div>
                            <div class="row g-2 text-center">
                                <div class="col-4"><button class="btn btn-outline-secondary btn-sm w-100" disabled>Extras</button></div>
                                <div class="col-4"><button class="btn btn-outline-danger btn-sm w-100" disabled>Wicket</button></div>
                                <div class="col-4"><button class="btn btn-outline-secondary btn-sm w-100" disabled>Undo</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section class="py-5 border-top">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Everything you need to run a match</h2>
                <p class="text-muted">From the toss to the trophy, Super Score handles it all.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="ss-feature-icon rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-broadcast"></i>
                            </div>
                            <h3 class="h6 fw-bold">Live Ball-by-Ball Scoring</h3>
                            <p class="text-muted small mb-0">
                                Record every run, extra, and wicket in real time, with instant
                                undo and edit for scoring mistakes.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="ss-feature-icon rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="h6 fw-bold">Teams &amp; Players</h3>
                            <p class="text-muted small mb-0">
                                Build reusable squads, assign captains and keepers, and pick your
                                Playing XI in seconds for every match.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="ss-feature-icon rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-bar-chart-line"></i>
                            </div>
                            <h3 class="h6 fw-bold">Scorecards &amp; Stats</h3>
                            <p class="text-muted small mb-0">
                                Full batting and bowling scorecards, partnerships, and career
                                statistics — calculated automatically from every delivery.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="ss-feature-icon rounded-circle bg-info-subtle text-info-emphasis d-flex align-items-center justify-content-center mb-3">
                                <i class="bi bi-share"></i>
                            </div>
                            <h3 class="h6 fw-bold">Share the Result</h3>
                            <p class="text-muted small mb-0">
                                Send a public live-score link to friends and family — no account
                                needed to follow along.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="py-5 bg-body-tertiary border-top">
        <div class="container py-4">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Up and running in three steps</h2>
            </div>
            <div class="row align-items-start text-center g-4">
                <div class="col-lg-4 ss-step">
                    <div class="d-flex flex-column align-items-center">
                        <span class="ss-step-number rounded-circle text-bg-primary d-flex align-items-center justify-content-center mb-3">1</span>
                        <h3 class="h6 fw-bold">Set up your match</h3>
                        <p class="text-muted small px-3">
                            Create teams, add players, pick the Playing XI, and call the toss.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 ss-step">
                    <div class="d-flex flex-column align-items-center">
                        <span class="ss-step-number rounded-circle text-bg-primary d-flex align-items-center justify-content-center mb-3">2</span>
                        <h3 class="h6 fw-bold">Score ball-by-ball</h3>
                        <p class="text-muted small px-3">
                            Tap runs, extras, and wickets as they happen — Super Score does the
                            maths.
                        </p>
                    </div>
                </div>
                <div class="col-lg-4 ss-step">
                    <div class="d-flex flex-column align-items-center">
                        <span class="ss-step-number rounded-circle text-bg-primary d-flex align-items-center justify-content-center mb-3">3</span>
                        <h3 class="h6 fw-bold">Share the result</h3>
                        <p class="text-muted small px-3">
                            Get the full scorecard, stats, and a link to share the moment it ends.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA band --}}
    <section class="py-5 bg-primary text-white text-center">
        <div class="container py-3">
            <h2 class="fw-bold mb-2">Ready to score your next match?</h2>
            <p class="mb-4 opacity-75">Create a free account and start your first match in minutes.</p>
            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4 fw-semibold">
                Create Free Account
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-4 border-top">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <span class="fw-semibold"><i class="bi bi-trophy-fill text-primary"></i> Super Score</span>
            <span class="text-muted small">&copy; {{ date('Y') }} Super Score. Cricket scoring &amp; statistics.</span>
        </div>
    </footer>
@endsection
