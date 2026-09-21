@extends('layouts.app')

@section('title', $innings->team->name.' — Live Scoring')

@push('styles')
    <style>
        .ss-run-btn { width: 3.5rem; height: 3.5rem; font-size: 1.25rem; font-weight: 700; }
        .ss-ball-chip { width: 2rem; height: 2rem; font-size: 0.75rem; font-weight: 600; }
        .ss-modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1050; }
        .ss-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1060; max-width: 480px; width: 92%; max-height: 90vh; overflow-y: auto; }
    </style>
@endpush

@section('content')
    <x-app-nav active="matches" />

    <div class="container py-4" x-data="liveScoring(@js($initialState))" x-init="init()">
        <template x-if="innings.status === 'completed'">
            <div class="alert alert-success d-flex justify-content-between align-items-center mb-3">
                <span><i class="bi bi-flag"></i> This innings is complete.</span>
                <a href="{{ route('matches.show', $innings->match) }}" class="btn btn-sm btn-success">Back to Match</a>
            </div>
        </template>

        <template x-if="error">
            <div class="alert alert-danger" x-text="error"></div>
        </template>

        {{-- Score header --}}
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-uppercase small opacity-75" x-text="innings.team.name"></div>
                        <div class="display-6 fw-bold">
                            <span x-text="innings.score.runs"></span> / <span x-text="innings.score.wickets"></span>
                        </div>
                        <div class="small">
                            <span x-text="innings.score.overs"></span> overs &middot; RR <span x-text="innings.score.run_rate"></span>
                        </div>
                    </div>
                    <template x-if="innings.required">
                        <div class="text-end small">
                            <div>Target: <strong x-text="innings.target"></strong></div>
                            <div>Need <strong x-text="innings.required.runs"></strong> off <strong x-text="innings.required.balls_left"></strong> balls</div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Batters & bowler --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <template x-if="innings.current_striker">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold" x-text="innings.current_striker.name + ' *'"></span>
                                <span x-text="batterLine(innings.current_striker.id)"></span>
                            </div>
                        </template>
                        <template x-if="innings.current_non_striker">
                            <div class="d-flex justify-content-between text-muted">
                                <span x-text="innings.current_non_striker.name"></span>
                                <span x-text="batterLine(innings.current_non_striker.id)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <template x-if="innings.current_bowler">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold" x-text="innings.current_bowler.name"></span>
                                <span x-text="bowlerLine(innings.current_bowler.id)"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- This over --}}
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <span class="text-muted small text-uppercase">This Over</span>
            <template x-for="ball in thisOver" :key="ball.sequence_in_innings">
                <span class="badge rounded-circle d-flex align-items-center justify-content-center ss-ball-chip"
                      :class="ball.is_wicket ? 'text-bg-danger' : (ball.extra_type ? 'text-bg-warning' : (ball.runs_off_bat >= 4 ? 'text-bg-success' : 'text-bg-light text-dark'))"
                      x-text="ball.label"></span>
            </template>
        </div>

        {{-- Scoring controls --}}
        <div class="card mb-3" x-show="canScore">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap justify-content-center mb-3">
                    <template x-for="run in [0,1,2,3,4,5,6]" :key="run">
                        <button type="button" class="btn btn-outline-primary rounded-circle ss-run-btn"
                                @click="recordRuns(run)" :disabled="loading">
                            <span x-text="run"></span>
                        </button>
                    </template>
                </div>
                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button type="button" class="btn btn-outline-secondary" @click="openExtras()" :disabled="loading">Extras</button>
                    <button type="button" class="btn btn-outline-danger" @click="openWicket()" :disabled="loading">Wicket</button>
                    <button type="button" class="btn btn-outline-secondary" @click="undo()" :disabled="loading || thisOver.length === 0 && allBalls.length === 0">Undo</button>
                    <a href="{{ route('innings.deliveries.index', $innings) }}" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-list-ol"></i> Ball History
                    </a>
                </div>
            </div>
        </div>

        <template x-if="innings.status !== 'completed' && !canScore">
            <div class="alert alert-warning">
                <template x-if="!innings.current_striker || !innings.current_non_striker">
                    <span>Select the new batter to continue.</span>
                </template>
                <template x-if="innings.current_striker && innings.current_non_striker">
                    <span>Select the next bowler to continue.</span>
                </template>
            </div>
        </template>

        {{-- New batter modal --}}
        <template x-if="showNewBatterModal">
            <div>
                <div class="ss-modal-backdrop"></div>
                <div class="card ss-modal">
                    <div class="card-body">
                        <h2 class="h5">Select New Batter</h2>
                        <select class="form-select mb-3" x-model="newBatterId">
                            <option value="" disabled selected>Select&hellip;</option>
                            @foreach ($battingTeamXi as $player)
                                <template x-if="!isAlreadyBatted({{ $player->id }})">
                                    <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                                </template>
                            @endforeach
                        </select>
                        <button class="btn btn-primary w-100" @click="submitNewBatter()" :disabled="!newBatterId || loading">Confirm</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Next over modal --}}
        <template x-if="showNextOverModal">
            <div>
                <div class="ss-modal-backdrop"></div>
                <div class="card ss-modal">
                    <div class="card-body">
                        <h2 class="h5">Select Next Bowler</h2>
                        <select class="form-select mb-3" x-model="nextBowlerId">
                            <option value="" disabled selected>Select&hellip;</option>
                            @foreach ($bowlingTeamXi as $player)
                                <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary w-100" @click="submitNextOver()" :disabled="!nextBowlerId || loading">Confirm</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Extras modal --}}
        <template x-if="showExtrasModal">
            <div>
                <div class="ss-modal-backdrop" @click="showExtrasModal = false"></div>
                <div class="card ss-modal">
                    <div class="card-body">
                        <h2 class="h5">Extras</h2>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" x-model="extraForm.type">
                                <option value="wide">Wide</option>
                                <option value="no_ball">No Ball</option>
                                <option value="bye">Bye</option>
                                <option value="leg_bye">Leg Bye</option>
                                <option value="penalty">Penalty</option>
                            </select>
                        </div>
                        <template x-if="extraForm.type === 'no_ball'">
                            <div class="mb-3">
                                <label class="form-label">Runs off the bat</label>
                                <input type="number" min="0" max="6" class="form-control" x-model.number="extraForm.runsOffBat">
                            </div>
                        </template>
                        <div class="mb-3">
                            <label class="form-label" x-text="extraForm.type === 'no_ball' ? 'Additional runs' : 'Runs'"></label>
                            <input type="number" min="0" max="6" class="form-control" x-model.number="extraForm.extraRuns">
                        </div>
                        <button class="btn btn-primary w-100" @click="submitExtra()" :disabled="loading">Confirm</button>
                    </div>
                </div>
            </div>
        </template>

        {{-- Wicket modal --}}
        <template x-if="showWicketModal">
            <div>
                <div class="ss-modal-backdrop" @click="showWicketModal = false"></div>
                <div class="card ss-modal">
                    <div class="card-body">
                        <h2 class="h5">Wicket</h2>
                        <div class="mb-3">
                            <label class="form-label">Dismissal Type</label>
                            <select class="form-select" x-model="wicketForm.dismissalType">
                                <option value="bowled">Bowled</option>
                                <option value="caught">Caught</option>
                                <option value="lbw">LBW</option>
                                <option value="run_out">Run Out</option>
                                <option value="stumped">Stumped</option>
                                <option value="hit_wicket">Hit Wicket</option>
                                <option value="retired_hurt">Retired Hurt</option>
                                <option value="retired_out">Retired Out</option>
                                <option value="obstructing_field">Obstructing the Field</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Batter Out</label>
                            <select class="form-select" x-model="wicketForm.dismissedPlayerId">
                                <option :value="innings.current_striker.id" x-text="innings.current_striker?.name"></option>
                                <option :value="innings.current_non_striker.id" x-text="innings.current_non_striker?.name"></option>
                            </select>
                        </div>
                        <template x-if="wicketForm.dismissalType === 'caught'">
                            <div class="mb-3">
                                <label class="form-label">Fielder</label>
                                <select class="form-select" x-model="wicketForm.fielderId">
                                    <option value="" disabled selected>Select&hellip;</option>
                                    @foreach ($bowlingTeamXi as $player)
                                        <option value="{{ $player->id }}">{{ $player->user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </template>
                        <button class="btn btn-danger w-100" @click="submitWicket()" :disabled="loading">Confirm Wicket</button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection

@push('scripts')
    <script>
        function liveScoring(initial) {
            return {
                innings: initial,
                allBalls: [],
                loading: false,
                error: null,
                showExtrasModal: false,
                showWicketModal: false,
                showNewBatterModal: false,
                showNextOverModal: false,
                extraForm: { type: 'wide', runsOffBat: 0, extraRuns: 1 },
                wicketForm: { dismissalType: 'bowled', dismissedPlayerId: null, fielderId: null },
                newBatterId: '',
                nextBowlerId: '',

                init() {
                    this.syncModals();
                    this.loadHistory();
                },

                get canScore() {
                    return this.innings.status !== 'completed'
                        && this.innings.current_striker && this.innings.current_non_striker
                        && !this.showNextOverModal;
                },

                get thisOver() {
                    if (this.allBalls.length === 0) return [];
                    const lastOverNumber = this.allBalls[0].over_number;
                    return this.allBalls.filter(b => b.over_number === lastOverNumber).slice().reverse();
                },

                batterLine(playerId) {
                    const stat = this.innings.batter_stats.find(s => s.player.id === playerId);
                    return stat ? `${stat.runs} (${stat.balls})` : '0 (0)';
                },

                bowlerLine(playerId) {
                    const stat = this.innings.bowler_stats.find(s => s.player.id === playerId);
                    return stat ? `${stat.overs}-${stat.runs_conceded}-${stat.wickets}` : '0.0-0-0';
                },

                isAlreadyBatted(playerId) {
                    return this.allBalls.some(b => b.is_wicket && b.dismissed_player === playerId)
                        || this.innings.current_striker?.id === playerId
                        || this.innings.current_non_striker?.id === playerId;
                },

                syncModals() {
                    this.showNewBatterModal = this.innings.status !== 'completed'
                        && (!this.innings.current_striker || !this.innings.current_non_striker);
                    this.showNextOverModal = false;
                },

                async loadHistory() {
                    const res = await fetch(`/innings/${this.innings.id}/deliveries`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const body = await res.json();
                    this.allBalls = body.data.deliveries;
                },

                async call(url, method, payload) {
                    this.loading = true;
                    this.error = null;
                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: payload ? JSON.stringify(payload) : undefined,
                        });
                        const body = await res.json();
                        if (!res.ok) {
                            this.error = body.message || 'Something went wrong.';
                            return false;
                        }
                        this.innings = body.data.innings;
                        this.syncModals();
                        await this.loadHistory();
                        return true;
                    } catch (e) {
                        this.error = 'Network error — please try again.';
                        return false;
                    } finally {
                        this.loading = false;
                    }
                },

                recordRuns(runs) {
                    this.call(`/innings/${this.innings.id}/deliveries`, 'POST', { runs_off_bat: runs })
                        .then(ok => ok && this.maybeOpenNextOver());
                },

                openExtras() {
                    this.extraForm = { type: 'wide', runsOffBat: 0, extraRuns: 1 };
                    this.showExtrasModal = true;
                },

                submitExtra() {
                    const payload = {
                        runs_off_bat: this.extraForm.type === 'no_ball' ? this.extraForm.runsOffBat : 0,
                        extra_type: this.extraForm.type,
                        extra_runs: this.extraForm.extraRuns,
                    };
                    this.call(`/innings/${this.innings.id}/deliveries`, 'POST', payload).then(ok => {
                        if (ok) {
                            this.showExtrasModal = false;
                            this.maybeOpenNextOver();
                        }
                    });
                },

                openWicket() {
                    this.wicketForm = {
                        dismissalType: 'bowled',
                        dismissedPlayerId: this.innings.current_striker?.id,
                        fielderId: null,
                    };
                    this.showWicketModal = true;
                },

                submitWicket() {
                    const payload = {
                        runs_off_bat: 0,
                        is_wicket: true,
                        dismissal_type: this.wicketForm.dismissalType,
                        dismissed_player_id: this.wicketForm.dismissedPlayerId,
                        fielder_id: this.wicketForm.fielderId || null,
                    };
                    this.call(`/innings/${this.innings.id}/deliveries`, 'POST', payload).then(ok => {
                        if (ok) {
                            this.showWicketModal = false;
                            this.maybeOpenNextOver();
                        }
                    });
                },

                submitNewBatter() {
                    this.call(`/innings/${this.innings.id}/new-batter`, 'POST', { player_id: this.newBatterId })
                        .then(ok => {
                            if (ok) {
                                this.newBatterId = '';
                                this.maybeOpenNextOver();
                            }
                        });
                },

                submitNextOver() {
                    this.call(`/innings/${this.innings.id}/next-over`, 'POST', { bowler_id: this.nextBowlerId })
                        .then(ok => { if (ok) this.nextBowlerId = ''; });
                },

                maybeOpenNextOver() {
                    if (this.innings.status === 'completed') return;
                    if (!this.innings.current_striker || !this.innings.current_non_striker) return;
                    const lastBall = this.allBalls[0];
                    if (lastBall && lastBall.is_legal_delivery) {
                        const legalInOver = this.allBalls.filter(b => b.over_number === lastBall.over_number && b.is_legal_delivery).length;
                        this.showNextOverModal = legalInOver >= 6;
                    }
                },

                undo() {
                    this.call(`/innings/${this.innings.id}/deliveries/undo`, 'POST').then(() => this.syncModals());
                },
            };
        }
    </script>
@endpush
