# Super Score — Implementation Plan (Web first, then Mobile)

This plan turns [super-score-application-flow.md](super-score-application-flow.md)
into buildable phases for the existing codebase: a Laravel backend serving
both the REST API (`/api/v1`, consumed by mobile) and the Blade web app,
plus the React Native mobile app. See [architecture.md](architecture.md),
[backend.md](backend.md), and [mobile.md](mobile.md) for the foundation
already in place.

**Sequencing:** the web app (backend + Blade) is built end-to-end through
all six phases first and validated. Mobile is then built as a second
track, **reusing the same backend services and API** built during the web
track unchanged — mobile phases are therefore mostly screens, navigation,
and a mobile services layer that calls an API that already exists and is
already proven correct by the web app. This is the payoff of the service
pattern in §1.2: business logic lives once in `app/Services`, so mobile
never re-implements scoring/validation rules, it only consumes them.

No phase should require restructuring a previous one — that's the point
of applying SOLID and the service pattern from Phase 1 onward, rather than
retrofitting it once the scoring engine (Phase 4) gets complex.

---

## 1. Guiding Principles

### 1.1 SOLID, applied to this codebase specifically

| Principle | Backend (Laravel) — built in the web track | Mobile (React Native) — built in the mobile track |
|---|---|---|
| **S**ingle Responsibility | Controllers only translate HTTP ↔ Service calls. All business logic lives in one `app/Services/*Service`. A service that starts handling two unrelated concerns (e.g. `MatchService` doing scoring *and* tournament points) gets split. | Screens only render + call hooks. Hooks only manage state + call services. Services only do business/API orchestration. `src/api` only does HTTP. |
| **O**pen/Closed | New extra types, dismissal types, or result outcomes are added as new classes implementing an existing interface (`ExtraHandler`, `DismissalHandler`, `ResultStrategy`), never as new `if/switch` branches in `DeliveryProcessingService`. | New scoring input types (e.g. a new extras button) implement `ScoringAction`, registered in a lookup table — `ScoringService` doesn't grow a new `if`. |
| **L**iskov Substitution | Any `ExtraHandler`/`DismissalHandler`/`ResultStrategy` implementation must be fully substitutable — same method signature, same side-effect contract (returns a `DeliveryOutcome`, never throws for a valid business case). | Any `OfflineQueue` implementation (AsyncStorage today, SQLite later) must be substitutable behind the same interface without `ScoringService` changing. |
| **I**nterface Segregation | `BroadcastsLiveScore` (used by live-scoring listeners) is a separate interface from `RecalculatesScore` (used by the ball-edit flow) — a class doesn't implement methods it doesn't need. | `Persistable` (save/load draft match state) is separate from `Syncable` (push to server) — a hook that only reads local state doesn't depend on sync methods. |
| **D**ependency Inversion | Controllers and services depend on interfaces bound in a `ServiceProvider` (e.g. `DeliveryProcessorInterface`), not concrete classes — enables swapping/mocking in tests without touching callers. Because mobile depends only on the **API contract**, not these interfaces directly, the backend is free to change its internals during the web track without ever breaking mobile later. | Hooks depend on a service's exported interface/type, not on `apiClient` directly — enables swapping the HTTP layer or mocking in tests. |

### 1.2 The Service Pattern used throughout

**Backend request flow (built once, during the web track, used forever after):**

```text
Route → Controller → FormRequest (validation) → Service (interface + impl)
                                                      │
                                          ┌───────────┼───────────┐
                                          ▼           ▼           ▼
                                       Model(s)    Event(s)   other Services
                                          │
                                     API Resource (response shaping)
```

- **Controllers** (`app/Http/Controllers/Api/V1`, `app/Http/Controllers/Web`): thin, no business logic, no direct model queries beyond simple lookups.
- **Form Requests** (`app/Http/Requests`): all input validation.
- **Services** (`app/Services`): one class per bounded responsibility, bound to an interface in `app/Services/Contracts`, registered in `AppServiceProvider` (or a dedicated `DomainServiceProvider`).
- **Actions** (`app/Actions`): single-purpose, single-method classes for cross-service orchestration that doesn't belong permanently in one service (e.g. `CompleteMatchAction` calls `InningsService`, `ResultService`, `StatisticsService` in sequence).
- **Events/Listeners** (`app/Events`, `app/Listeners`): side effects (broadcasting, statistics recompute, notifications) are decoupled from the service that triggers them.
- **Resources** (`app/Http/Resources`): all API responses are shaped through Resources, never raw models. Because the mobile app is deferred, it's tempting to skip Resources and let `Web` controllers pass models straight to Blade — **don't**; every endpoint under `Api/V1` (even ones the Blade app also happens to call) goes through a Resource from day one, since it is the contract the mobile track will consume unchanged.

**Mobile request flow (added in the mobile track, on top of the finished API):**

```text
Screen → Hook (state) → Service (business/API orchestration) → api/client.ts (Axios)
```

- **Screens** (`src/screens`): render only, no business logic (already the rule in [development-guidelines.md](development-guidelines.md)).
- **Hooks** (`src/hooks`): local/derived state, call one or more services.
- **Services** (`src/services`): the mobile equivalent of backend services — one per domain (`AuthService`, `MatchService`, `ScoringService`, ...), each behind a TypeScript interface so it's mockable in tests.
- **api** (`src/api`): HTTP only, already centralized via `apiClient`.

### 1.3 Delivery-centric scoring engine (carried over from the flow doc)

Per [super-score-application-flow.md §36/§38](super-score-application-flow.md#36-core-match-data-flow),
the **Delivery** is the single source of truth. Team score, batter/bowler
stats, partnerships, fall of wickets, and the scorecard are all **derived**
from the ordered list of deliveries in an innings — never stored as
independently-mutated counters. This is what makes undo/edit-ball
(§22) tractable: editing a delivery just means recomputing derived state
from that point forward, not patching N different counters by hand. This
principle drives the service design in Web Phase 4, and is the reason
mobile scoring (Mobile Phase 4) can stay a thin client: it renders
whatever `ScoreProjectionService` last returned, it doesn't recompute
cricket rules itself.

---

## 2. Phase 0 — Recap (already done)

Already in place, not part of this plan: Laravel app skeleton, Docker
Compose stack, Sanctum/Reverb installed, health check endpoint, React
Native skeleton with navigation + Axios client. See
[development-guidelines.md](development-guidelines.md) for current scope
boundaries.

---

# PART A — Web Track (Backend + Blade)

Build and ship these six phases completely before starting the mobile
track. The backend built here — services, contracts, migrations, API
endpoints, events — is not revisited for mobile; mobile only adds a
consuming layer on top of it (Part B).

## A.1 Phase 1 — Foundation (Auth, Profile, Dashboard)

Maps to flow doc §4–§7, §37 Phase 1.

**Goal:** a user can register, verify, log in, complete their profile, and land on a dashboard shell.

- Migrations: `users` (extend default: mobile number, preferred role, profile photo path), `otp_codes` (or reuse a package), `personal_access_tokens` (already via Sanctum).
- Contracts: `app/Services/Contracts/AuthServiceInterface`, `OtpServiceInterface`, `ProfileServiceInterface`.
- Services: `AuthService` (register, login, logout, forgotPassword/resetPassword), `OtpService` (generate, send, verify, resend — behind an interface so the SMS/email provider is swappable), `ProfileService` (update profile, upload photo).
- Form Requests: `RegisterRequest`, `LoginRequest`, `VerifyOtpRequest`, `ForgotPasswordRequest`, `ResetPasswordRequest`, `UpdateProfileRequest`.
- Resources: `UserResource`.
- Controllers: `Api/V1/AuthController`, `Api/V1/ProfileController` (build the API endpoints now even though mobile isn't consuming them yet — they cost nothing extra once the service layer exists, and it means Mobile Phase 1 is pure UI work), `Web/AuthController`, `Web/DashboardController` — both the web and API controllers call the same `AuthServiceInterface`/`ProfileServiceInterface`, so login rules never diverge later when mobile arrives.
- Views: `pages/auth/login.blade.php`, `register.blade.php`, `verify-otp.blade.php`, `forgot-password.blade.php`, `pages/profile/edit.blade.php`, `pages/dashboard.blade.php` (shell with placeholder sections for Teams/Players/Matches).
- Tests: `tests/Feature/Api/AuthTest`, `OtpVerificationTest`, `ProfileTest`, `tests/Feature/Web/AuthTest`.

**Definition of done:** a user can register → verify OTP → set up profile → see an empty dashboard on the web app, and the same rules are already exposed (and tested) under `/api/v1` for mobile to consume later without backend changes.

## A.2 Phase 2 — Teams & Players

Maps to flow doc §8–§9, §37 Phase 2.

**Correction from the original plan:** a `Player` is a registered
user's own cricket profile, not a free-form record a team owner types
in for someone else — "each player creates themselves via
registration [and profile setup]; a team owner can only add an
*existing* player to their team." `players.user_id` is unique
(one-to-one with `users`), and there is no "create a player for
someone else" operation anywhere in the app.

- Migrations: `teams` (user_id, name, short_name, logo_path, city), `players` (user_id unique, date_of_birth, role, batting_style, bowling_style — name/photo are the user's own account fields, not duplicated), `team_player` pivot (team_id, player_id, is_captain, is_vice_captain, is_wicket_keeper).
- Contracts + Services: `TeamServiceInterface`/`TeamService` (create/update team, assign captain/VC/WK), `PlayerServiceInterface`/`PlayerService` (`saveOwnProfile` — an upsert that only ever targets the calling user; `attachToTeam`/`detachFromTeam` — any registered player can be added to any team's squad, reusable across teams per §9.2).
- Form Requests: `CreateTeamRequest`, `UpdateTeamRequest`, `SavePlayerProfileRequest` (single self-service form — create the first time, update every time after), `AssignPlayerRequest`, `AssignTeamRoleRequest`.
- Resources: `TeamResource`, `PlayerResource` (derives name/photo from the related `User`), `TeamSquadResource` (team + its players with roles).
- Policies: `TeamPolicy` (only the owning user manages their team), `PlayerPolicy` (a user may create their player profile only if they don't already have one; only that user may update it — never anyone else's).
- Controllers: `Api/V1/TeamController`, `Api/V1/PlayerController` (`index` = directory of all players; `showOwn`/`updateOwn` = the caller's own profile); `Web/TeamController`, `Web/PlayerController` (`index` = directory; `editOwn`/`updateOwn` = self-service profile form).
- Views: `pages/teams/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php` (squad management — add any existing player, assign roles), `pages/players/index.blade.php` (directory), `profile.blade.php` (create-or-edit own profile).
- Tests: `TeamTest`, `PlayerTest` (self-service profile, one-per-user enforcement), `SquadAssignmentTest` (any registered player addable, only the team owner can add/remove/assign roles).

**Definition of done:** a user can set up their own player profile once (and only once), a team owner can create a team and add any registered player (including other users' own profiles) to its squad with a role, on the web app — via the same services the API resources already expose.

## A.3 Phase 3 — Match Setup

Maps to flow doc §10–§13, §37 Phase 3.

**Note on naming:** the model is `App\Models\CricketMatch`, not `Match`
— `Match` is a reserved word in PHP 8. The table is still `matches`.

**Note on team selection:** any registered team can be picked as Team A
or Team B (a directory-style pick, like the Phase 2 player pool), not
just teams the organizer owns — an organizer/scorer role may be setting
up a match between two other people's teams. `CricketMatchPolicy`
still restricts *editing the match itself* (team/XI/toss steps) to
whoever created it.

- Migrations: `matches` (user_id, name, format, overs, date, time, venue, status — `tournament_id` deferred to Phase 6, no `Tournament` model yet to reference), `match_teams` (match_id, team_id, side), `playing_xi` (match_id, team_id, player_id, is_captain, is_vice_captain, is_wicket_keeper, is_substitute), `tosses` (match_id, winner_team_id, decision).
- Contracts + Services:
  - `MatchSetupServiceInterface`/`MatchSetupService` — create match, validate team selection (Team A ≠ Team B, both have enough registered players for the format — §11 validation rules, `config('cricket.playing_xi_size')`).
  - `PlayingXiServiceInterface`/`PlayingXiService` — select XI per team (captain + wicket keeper required, vice-captain optional, substitutes reserved for a later phase's UI).
  - `TossServiceInterface`/`TossService` — record toss winner + decision; `batting_first`/`bowling_first` (§13) are **derived** (`Toss::battingTeam()`/`bowlingTeam()`), not stored as separate columns, consistent with the "derive, don't duplicate" principle used throughout.
- Form Requests: `CreateMatchRequest`, `SelectTeamsRequest`, `SelectPlayingXiRequest` (also validates both teams are selected first), `RecordTossRequest` (also validates both playing XIs are complete first) — each step's dependency on the previous one is enforced here, not in the service.
- Resources: `MatchResource`, `PlayingXiResource`, `TossResource`.
- Controllers: `Api/V1/MatchController`, `Api/V1/PlayingXiController`, `Api/V1/TossController`; equivalent `Web/*` controllers.
- Views: `pages/matches/{index,create,show,select-teams,select-playing-xi,toss}.blade.php` — the playing-XI picker uses Alpine.js (already loaded) for a live-filtered captain/WK/VC dropdown as squad checkboxes are ticked.
- Tests: `MatchSetupTest`, `PlayingXiValidationTest`, `TossTest`.

**A real bug this phase caught:** `PlayingXiService` originally compared submitted player IDs against squad IDs with strict (`===`/`in_array(..., true)`) checks. Laravel's `$this->put()` test helper preserves native PHP array types, so tests passed — but a real HTML form submits every field as a string, and a live smoke test against the running app failed with "All selected players must be part of the team's squad" even for valid selections. Fixed by normalizing (`array_map('intval', ...)`) at the service boundary; the regression test now submits string IDs to match real form behavior. **Lesson: `$this->post()/put()` test helpers do not reproduce real form type-coercion — cast IDs to strings in tests that exercise Blade forms, or verify against the running app before calling a web flow done.**

**Definition of done:** a full match can be configured end-to-end on the web app — created, teams selected, playing XI + captain/WK chosen per side, toss recorded — verified against the actual running app, not just the test suite.

## A.4 Phase 4 — Scoring Engine (core, highest risk)

Maps to flow doc §14–§22, §37 Phase 4. This is where the SOLID/service
design matters most, because every future feature (statistics, sharing,
tournaments, and the entire mobile track) reads from what this phase
produces.

### Domain services and their contracts

```text
app/Services/Scoring/
├── Contracts/
│   ├── DeliveryProcessorInterface.php
│   ├── ExtraHandlerInterface.php
│   ├── DismissalHandlerInterface.php
│   ├── ScoreProjectorInterface.php
│   └── StrikeRotationInterface.php
├── DeliveryProcessingService.php      (orchestrator — implements DeliveryProcessorInterface)
├── Extras/
│   ├── WideExtraHandler.php
│   ├── NoBallExtraHandler.php
│   ├── ByeExtraHandler.php
│   ├── LegByeExtraHandler.php
│   ├── PenaltyExtraHandler.php
│   └── ExtraHandlerFactory.php        (resolves handler by extra type — Open/Closed: add a type, add a class)
├── Dismissals/
│   ├── BowledDismissalHandler.php
│   ├── CaughtDismissalHandler.php
│   ├── LbwDismissalHandler.php
│   ├── RunOutDismissalHandler.php
│   ├── StumpedDismissalHandler.php
│   ├── HitWicketDismissalHandler.php
│   ├── RetiredHurtDismissalHandler.php
│   ├── RetiredOutDismissalHandler.php
│   ├── ObstructingFieldDismissalHandler.php
│   └── DismissalHandlerFactory.php
├── ScoreProjectionService.php         (derives score/stats FROM the delivery list — implements ScoreProjectorInterface)
├── StrikeRotationService.php
├── OverManagerService.php             (legal ball counting, over completion, bowler-change validation)
├── PartnershipTrackerService.php
└── BallRecalculationService.php       (edit/undo — replays deliveries from the edit point via ScoreProjectionService)
```

**Why this shape:**

- `DeliveryProcessingService` (Single Responsibility) only *orchestrates* — it identifies striker/non-striker/bowler, delegates run/extra/wicket handling to the right collaborator, persists the `Delivery` record, and dispatches events. It never contains a `switch` on extra type or dismissal type itself — that's what the factories are for (Open/Closed).
- Every `ExtraHandler`/`DismissalHandler` implementation takes the same input (`DeliveryInput` DTO) and returns the same output (`DeliveryOutcome` DTO: runs, legal-ball flag, wicket flag, free-hit flag) — Liskov Substitution. Adding "Penalty" or a new dismissal type never touches existing handlers.
- `ScoreProjectionService` is the one place that turns "list of deliveries" into "team score / batter stats / bowler stats / extras / fall of wickets / partnerships." Both the live scoring screen (web now, mobile later) and `BallRecalculationService` (undo/edit) call the same projector — so there is exactly one code path that can get score math wrong, and fixing it fixes it everywhere (this directly implements flow doc §38's "recalculate all affected values" requirement). This is also precisely what lets mobile skip re-implementing cricket rules: it consumes the same projected output over the API.
- `BallRecalculationService` depends on `ScoreProjectorInterface` and `DeliveryProcessorInterface`, not concrete classes (Dependency Inversion) — it re-runs processing for deliveries after an edit point without knowing their internals.

### Data model

- Migrations: `innings` (match_id, team_id, innings_number, target nullable, status), `overs` (innings_id, over_number, bowler_id), `deliveries` (over_id, striker_id, non_striker_id, bowler_id, runs_off_bat, extra_type nullable, extra_runs, is_wicket, dismissal_type nullable, dismissed_player_id nullable, fielder_id nullable, is_legal_delivery, is_free_hit, sequence_in_innings, superseded_by nullable — for edit history).
- `deliveries.superseded_by` supports edit-in-place without losing history (§22 requires ball history + correction).

### Events (decouple side effects from the scoring path)

- `BallRecorded`, `WicketFallen`, `OverCompleted`, `InningsCompleted` — dispatched by `DeliveryProcessingService`/`OverManagerService`.
- Listeners: `BroadcastLiveScore` (Reverb, Interface Segregation — implements only `BroadcastsLiveScore`), `RecalculatePartnership`, `CheckInningsEndConditions` (§23: all out / overs done / manual end / abandoned).

### API surface (build the full contract now — mobile will consume it as-is later)

- `POST /matches/{match}/innings/{innings}/start`
- `POST /matches/{match}/innings/{innings}/deliveries` (record a ball)
- `PATCH /deliveries/{delivery}` (edit — §22)
- `POST /deliveries/{delivery}/undo` or `DELETE` last delivery
- `POST /matches/{match}/innings/{innings}/overs/next` (select next bowler — §20)
- `GET /matches/{match}/live` (current state for resume-on-reopen — §21; this same endpoint is what the mobile app polls/loads on launch in Mobile Phase 4)

### Web (Blade) — the only live scoring UI for now

- Views: `pages/matches/start-innings.blade.php`, `pages/matches/live.blade.php` (§15 layout: score header, batter/bowler lines, run buttons, extras, wicket, over history, undo — via Alpine.js + the API endpoints above, so the scoring interactions are already exercising the real REST contract mobile will reuse), `partials/extras-modal.blade.php`, `wicket-modal.blade.php`, `new-batter-modal.blade.php`, `end-of-over-modal.blade.php`, `ball-history.blade.php`, `edit-ball.blade.php`.
- Tests: `LiveScoringTest`, `ExtraHandlingTest` (one per extra type), `DismissalHandlingTest` (one per dismissal type), `BallEditRecalculationTest`, `UndoTest`, `InningsResumeTest`.

**Definition of done:** a full innings can be scored ball-by-ball from the web app (runs, all extra types, all dismissal types), overs roll over correctly with bowler selection, a ball can be edited/undone with all derived stats recalculating correctly, and the match survives a browser refresh mid-innings. Because scoring is exercised live through `/api/v1`, this phase is the point at which the mobile app's future scoring screen is effectively already backend-complete.

## A.5 Phase 5 — Match Completion & Scorecard

Maps to flow doc §23–§32, §37 Phase 5.

- Contracts + Services:
  - `InningsCompletionServiceInterface` — evaluates §23 end conditions, computes target (§25) for the second innings.
  - `ResultDeterminationServiceInterface`, with a `ResultStrategyInterface` and strategies: `TargetChaseResultStrategy`, `OversCompletedResultStrategy`, `TieResultStrategy`, `SuperOverResultStrategy` (Open/Closed — new formats/tie-breakers are new strategies, chosen by a `ResultStrategyResolver` based on match format/config, not a growing `if` chain in one service).
  - `ScorecardServiceInterface`/`ScorecardService` — composes the final scorecard purely from `ScoreProjectionService` output (batting/bowling tables, extras, fall of wickets, partnerships, over summary — §30).
  - `PlayerOfTheMatchServiceInterface` — suggests candidates from match performance (§28), organizer/scorer confirms.
  - `SharingServiceInterface`/`SharingService` — generates a public match token/URL (§31), enforced read-only via a `PublicMatchPolicy` (no mutation endpoints reachable without auth).
- Migrations: `match_results` (match_id, result_type, winner_team_id, margin, margin_type), `player_of_the_match` (match_id, player_id), `public_match_links` (match_id, token, is_active).
- Events: `MatchCompleted` → listeners `GenerateScorecard`, `QueueStatisticsRecompute` (Phase 6), `NotifyParticipants` (Phase 6 if notifications are built).
- Controllers: `Api/V1/MatchResultController`, `ScorecardController`, `PlayerOfTheMatchController`, `ShareController`; a public, unauthenticated `Api/V1/PublicMatchController` for the shared URL; equivalent `Web/*` controllers plus `pages/public/match.blade.php` (the public share view — §31, no auth required, read-only).
- Views: `pages/matches/result.blade.php`, `scorecard.blade.php`, `player-of-the-match.blade.php`.
- Tests: `ResultDeterminationTest` (one per strategy), `ScorecardCompositionTest`, `PublicSharingAccessTest` (asserts no mutation is possible without auth).

**Definition of done:** a completed match produces a correct result for all four outcome types (target achieved, overs completed, tie, super over), a full scorecard, a player-of-the-match flow, and a public read-only share link — all on the web app.

## A.6 Phase 6 — Advanced Features

Maps to flow doc §33–§34, §37 Phase 6. Only start this phase once web
Phases 1–5 are stable in production use.

- `StatisticsService`/`StatisticsServiceInterface` — aggregates career stats (§33) from completed matches; runs as a queued `RecomputePlayerStatisticsJob` listening on `MatchCompleted`, not synchronously, so match completion stays fast.
- Tournament module (§34): `TournamentService`, `FixtureGeneratorInterface` (round-robin/knockout implementations — Open/Closed for new formats), `PointsTableService`, `NrrCalculatorService`.
- `NotificationService` wrapping Laravel notifications (match reminders, result notifications) — channel-agnostic behind an interface so SMS/push/email can be added independently.
- Live public scoring: wire `BroadcastLiveScore` (stubbed in Phase 4) fully through Reverb channels for public viewers (§31), consumed on the web via Laravel Echo + Alpine.js.
- Match History filters (§32): query service with composable filter objects (date/team/tournament/format/result/venue) rather than one giant conditional query builder method.
- Web views: `pages/statistics/player.blade.php`, tournament admin views (`pages/tournaments/*`), `pages/matches/history.blade.php` (with filters), public live-score pages.

**Definition of done:** career statistics stay correct as matches complete, a tournament can run fixtures through to a champion with a live points table, and public viewers can watch a match live on the web without polling.

---

# PART B — Mobile Track (React Native)

Start this track only after Part A ships. Every phase here targets an API
that already exists, is already tested, and is already proven against a
real UI (the Blade app) — so no phase in this part should require a
backend change. If one turns out to be needed, treat it as a sign that
Part A's contract was incomplete, fix it in the backend service layer
(never work around it in mobile), and keep the same phase numbering so
web and mobile phases stay easy to cross-reference.

## B.1 Phase 1 — Foundation (Auth, Profile, Dashboard)

- Screens: `LoginScreen`, `RegisterScreen`, `OtpVerificationScreen`, `ForgotPasswordScreen`, `ProfileSetupScreen`, `DashboardScreen` (shell).
- Navigation: add an `Auth` stack (unauthenticated) and a `Main` stack (authenticated) to `RootNavigator`, switched based on token presence.
- `src/services/AuthService.ts` (interface `AuthServiceInterface`: register/login/verifyOtp/resendOtp/logout/forgotPassword), `src/services/ProfileService.ts` — both call the `Api/V1/AuthController`/`ProfileController` endpoints from A.1 unchanged.
- `src/storage`: persist the auth token (e.g. via a `TokenStorage` interface, implemented with `AsyncStorage` initially — keeps it swappable for Keychain/Keystore later, per Dependency Inversion).
- `src/hooks/useAuth.ts` wraps `AuthService` + token storage, exposes `{ user, login, logout, register }` to screens.
- Wire `apiClient`'s placeholder auth-token interceptor (mentioned in [mobile.md](mobile.md)) to read from `TokenStorage`.

**Definition of done:** a user can register → verify OTP → set up profile → see an empty dashboard on mobile, enforcing identical rules to the web app because both call the same backend services.

## B.2 Phase 2 — Teams & Players

- Screens: `TeamListScreen`, `CreateTeamScreen`, `TeamDetailScreen` (squad — add any existing player, assign roles), `PlayerDirectoryScreen` (browse all registered players), `MyPlayerProfileScreen` (create-or-edit the current user's own profile — no "add player for someone else" screen exists).
- Navigation routes added to `RootStackParamList`: `Teams`, `TeamDetail`, `Players`, `MyPlayerProfile`.
- `src/services/TeamService.ts`, `PlayerService.ts` (`getDirectory`, `getOwnProfile`, `saveOwnProfile`) — consume `Api/V1/TeamController`/`PlayerController` from A.2.
- `src/hooks/useTeams.ts`, `usePlayerProfile.ts`.

**Definition of done:** a user can set up their own player profile, a team can be created, and any registered player can be added to a squad with a role — from mobile, matching web behavior exactly.

## B.3 Phase 3 — Match Setup

- Screens: `CreateMatchScreen`, `SelectTeamsScreen`, `SelectPlayingXiScreen`, `TossScreen`.
- `src/services/MatchService.ts` (createMatch, selectTeams, selectPlayingXi, recordToss) — consumes `Api/V1/MatchController`/`PlayingXiController`/`TossController` from A.3.
- `src/hooks/useMatchSetup.ts`.

**Definition of done:** a full match can be configured end-to-end from a phone — created, teams selected, playing XI + captain/WK chosen, toss recorded.

## B.4 Phase 4 — Scoring Engine (mobile as the primary scoring surface going forward)

Once mobile ships this phase, it becomes the realistic scoring surface at
the ground (per the flow doc's framing) even though the Blade app built
in A.4 remains fully functional as a fallback/admin scoring surface.

- Screens: `StartInningsScreen`, `LiveScoringScreen` (§15 layout), `ExtrasModal`, `WicketModal`, `NewBatterModal`, `EndOfOverModal`, `BallHistoryScreen`, `EditBallScreen`.
- `src/services/ScoringService.ts` — calls the exact endpoints listed in A.4 (`recordDelivery`, `editDelivery`, `undoLastDelivery`, `startInnings`, `selectNextBowler`) — no new backend work expected here.
- **Offline resilience (§21 resume):** `src/storage/OfflineQueueInterface` + `AsyncStorageOfflineQueue` impl — deliveries are queued locally and flushed when connectivity returns, so a scorer at a ground with poor signal doesn't lose balls. `ScoringService` depends on `OfflineQueueInterface`, not the concrete storage (Dependency Inversion) — swappable for SQLite later without touching the service. This is mobile-only concern; the web app doesn't need it.
- `src/hooks/useLiveScoring.ts` — holds current striker/non-striker/bowler/over state, calls `ScoringService`, exposes derived values (current score, run rate) computed the same way the backend's `ScoreProjectionService` would (kept as pure functions in `src/utils/scoreProjection.ts` so optimistic UI updates match server truth once confirmed).
- Resume-on-launch: on app start, `useAuth`/`useLiveScoring` checks `GET /matches/{match}/live` for an unfinished match owned by the user (flow doc §3, §21) and routes to `LiveScoringScreen` instead of the dashboard.

**Definition of done:** a full innings can be scored ball-by-ball from mobile (runs, all extras, all dismissals), overs roll over with bowler selection, edit/undo work, and the match survives an app close/reopen mid-innings — even on a flaky connection.

## B.5 Phase 5 — Match Completion & Scorecard

- Screens: `MatchResultScreen`, `ScorecardScreen`, `PlayerOfTheMatchScreen`, `ShareSheet` (native share integration — WhatsApp/Instagram/Copy Link per §31).
- `src/services/ResultService.ts`, `ScorecardService.ts`, `ShareService.ts` — consume the A.5 endpoints unchanged.

**Definition of done:** match result (all four outcome types), full scorecard, player-of-the-match, and native share all work from mobile.

## B.6 Phase 6 — Advanced Features

- Screens: `PlayerStatisticsScreen`, `TournamentScreen` (fixtures, points table, knockouts), `MatchHistoryScreen` (with filters), push notification handling.
- Consumes the statistics/tournament/notification endpoints from A.6 unchanged; add push notification registration (device token → `NotificationService` from A.6).

**Definition of done:** career statistics, tournaments, match history with filters, and live public scoring are all available from mobile, at parity with web.

---

## 9. Cross-Cutting Concerns (apply from Phase 1 onward, both tracks)

- **Testing:** every new service gets a unit test against its interface (mock collaborators); every new endpoint gets a Feature test (`tests/Feature/Api` or `Web`); every new mobile screen gets a render smoke test; every new mobile service gets a Jest unit test with the API layer mocked.
- **Validation boundary:** all input validation happens in Form Requests (backend) or at the service call boundary (mobile) — never inline in controllers/screens.
- **Authorization:** every mutation on a team/match a user doesn't own is denied via a Policy, checked in the controller (`$this->authorize(...)`), not inside the service (keeps services usable from queued jobs/artisan commands without a request context).
- **Realtime:** all broadcasting goes through the `BroadcastsLiveScore` interface so it can be disabled/mocked in tests without touching scoring logic.
- **Versioning:** any breaking API change ships as `/api/v2`, per [api.md](api.md) — never mutate `/api/v1` contracts once the web app (and later mobile) depends on them. Because mobile is deferred, it's tempting to treat `/api/v1` as still-negotiable during Part A — don't; freeze each endpoint's contract as soon as the web app ships against it, since that contract is what the mobile track will build on without a backend engineer in the loop.
- **No hardcoded config:** API URLs, feature flags, and format rules (overs per format, super-over eligibility) belong in `config/` (backend) and `src/config` (mobile), never inline.

---

## 10. Suggested New Directories

```text
backend/app/Services/Contracts/        Interfaces for all domain services
backend/app/Services/Scoring/          Delivery processing, extras, dismissals, projection (Phase 4)
backend/app/Services/Scoring/Extras/
backend/app/Services/Scoring/Dismissals/
backend/app/Services/Results/          Result determination strategies (Phase 5)
backend/app/DataTransferObjects/       DeliveryInput, DeliveryOutcome, etc.

mobile/src/services/                   Already exists — one file per domain service (Part B)
mobile/src/hooks/                      Already exists — one hook per screen-cluster (Part B)
mobile/src/storage/OfflineQueue*.ts    Offline delivery queue (Part B, Phase 4)
mobile/src/utils/scoreProjection.ts    Pure functions mirroring backend ScoreProjectionService, for optimistic UI (Part B)
```

These slot into the structure already described in [architecture.md](architecture.md) and [backend.md](backend.md) — no restructuring required as each phase lands.
