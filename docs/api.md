# API

The Laravel backend exposes a versioned REST API under `/api/v1`,
consumed by the mobile app and any future clients (web SPA, third-party
integrations, etc.).

## Versioning

All API routes live in `backend/routes/api.php` and are grouped under the
`v1` prefix:

```php
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/health', HealthController::class)->name('health');
});
```

Future breaking changes should ship as a new `v2` group rather than
mutating `v1` in place, so existing mobile app installs keep working.

Controllers for the API live in `app/Http/Controllers/Api/V1`, separate
from the Blade web controllers in `app/Http/Controllers/Web`.

## Response shape

Successful responses follow this shape:

```json
{
    "success": true,
    "message": "Human-readable summary.",
    "data": { }
}
```

## Endpoints

### `GET /api/v1/health`

Health check — confirms the API is up and returns basic application
metadata. No authentication required.

**Response `200`:**

```json
{
    "success": true,
    "message": "Super Score API is running.",
    "data": {
        "application": "Super Score",
        "version": "v1"
    }
}
```

### Auth & Profile (Phase 1)

All endpoints below return the standard response shape. Endpoints under
"Requires `auth:sanctum`" expect an `Authorization: Bearer <token>`
header, issued at register/login. Endpoints also under "Requires OTP
verified" are additionally blocked (`403`) until the account's OTP has
been verified.

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/v1/register` | — | Create an account, sends an OTP code by email. |
| POST | `/api/v1/login` | — | Returns a Sanctum token + user. |
| POST | `/api/v1/forgot-password` | — | Sends a password reset link. |
| POST | `/api/v1/reset-password` | — | Resets the password with a valid reset token. |
| POST | `/api/v1/logout` | `auth:sanctum` | Revokes the current token. |
| POST | `/api/v1/otp/verify` | `auth:sanctum` | Verifies the account with an OTP code. |
| POST | `/api/v1/otp/resend` | `auth:sanctum` | Issues and sends a new OTP code. |
| GET | `/api/v1/profile` | `auth:sanctum` + OTP verified | Returns the authenticated user. |
| PUT | `/api/v1/profile` | `auth:sanctum` + OTP verified | Saves profile fields (name, email, mobile_number, preferred_role, photo). Marks the profile complete on first save. |

The business logic behind these endpoints lives in `app/Services`
(`AuthService`, `OtpService`, `ProfileService`), bound to interfaces in
`app/Services/Contracts` — see
[implementation-plan.md](implementation-plan.md) for the service-pattern
rationale. The Blade web app (`Web\AuthController`, `Web\ProfileController`)
calls the same services, so validation and business rules never diverge
between web and the API.

### Teams & Players (Phase 2)

All endpoints below require `auth:sanctum` + OTP verified, same as the
Profile endpoints.

**A player is a registered user's own cricket profile — one per
account, created only by that user.** There is no endpoint for creating
a player on someone else's behalf. A team owner can only *add an
existing player* (any registered user who has set one up) to their
squad; `TeamPolicy` still restricts team CRUD to the owning user, but
the player pool itself is shared across the whole app.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/teams` | List the authenticated user's teams. |
| POST | `/api/v1/teams` | Create a team (name, short_name, city, logo). |
| GET | `/api/v1/teams/{team}` | Team + full squad, with per-player leadership flags. |
| PUT | `/api/v1/teams/{team}` | Update team fields. |
| POST | `/api/v1/teams/{team}/players` | Add any registered player to this team's squad. |
| DELETE | `/api/v1/teams/{team}/players/{player}` | Remove a player from the squad. |
| PUT | `/api/v1/teams/{team}/role` | Assign captain/vice_captain/wicket_keeper to a squad player — clears whoever previously held that role for the team. |
| GET | `/api/v1/players` | Directory of every registered player (name/photo come from their account), for adding to a squad. |
| GET | `/api/v1/player-profile` | The authenticated user's own player profile, or `null` if they haven't set one up. |
| PUT | `/api/v1/player-profile` | Create-or-update the authenticated user's own player profile (role, date_of_birth, batting/bowling style). Always acts on the caller — there is no `user_id` parameter. |

Players are independent of any one team and can be attached to several
squads — per
[super-score-application-flow.md §9.2](super-score-application-flow.md#92-add-player),
reinterpreted so that "adding a player" means picking an existing
account, not typing in a new name. The business logic lives in
`TeamService`/`PlayerService` (`app/Services`), bound to
`TeamServiceInterface`/`PlayerServiceInterface` in
`app/Services/Contracts`. `TeamService` owns team CRUD and leadership
role assignment; `PlayerService` owns the user's own profile
(`saveOwnProfile`, an upsert) and squad membership (attach/detach) — see
[implementation-plan.md](implementation-plan.md).

### Match Setup (Phase 3)

All endpoints below require `auth:sanctum` + OTP verified. Match setup
is a linear flow — create the match, select its two teams, select each
team's playing XI, then record the toss — and `CricketMatchPolicy`
restricts every step to the match's organizer (the user who created
it). The model class is named `CricketMatch` because `Match` is a
reserved word in PHP; the table is still `matches`.

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/matches` | List the organizer's matches. |
| POST | `/api/v1/matches` | Create a match (name, format, overs, date, time, venue). |
| GET | `/api/v1/matches/{match}` | Match details, teams, playing XI completion, and toss result if recorded. |
| PUT | `/api/v1/matches/{match}/teams` | Select Team A / Team B. Both teams must be different and each must have at least `config('cricket.playing_xi_size')` (default 11) players in its squad. |
| PUT | `/api/v1/matches/{match}/playing-xi/{team}` | Set a team's playing XI: exactly `playing_xi_size` players from its squad, plus a captain and wicket keeper (vice-captain optional) chosen from among them. Replaces any previously saved XI for that team. |
| PUT | `/api/v1/matches/{match}/toss` | Record the toss winner and decision (bat/bowl). Requires both teams' playing XIs to be complete first. Batting/bowling-first are derived from winner + decision, not stored separately (see `Toss::battingTeam()`/`bowlingTeam()`). |

The business logic lives in `MatchSetupService`, `PlayingXiService`, and
`TossService` (`app/Services`), bound to their respective interfaces in
`app/Services/Contracts`. Each step's Form Request enforces the previous
step's completion (e.g. you cannot select a playing XI before both teams
are chosen) so the dependency chain is validated at the boundary, not
buried in the service layer.

## Authentication

[Laravel Sanctum](https://laravel.com/docs/sanctum) is installed and
configured (`config/sanctum.php`, `HasApiTokens` on the `User` model),
and wired up for register/login/logout as described above. OTP
verification (email-based for now, via `OtpNotifierInterface` — swappable
for SMS/WhatsApp later) gates access to endpoints marked "OTP verified"
above.

## Realtime

[Laravel Reverb](https://laravel.com/docs/reverb) is installed and
configured as the broadcast driver (`BROADCAST_CONNECTION=reverb`), with
`routes/channels.php` in place for future channel authorization. No
events are broadcast yet — this is infrastructure only, intended for
future live scores, commentary, and match event updates.

## What's intentionally not here yet

No scoring engine, no innings/overs/deliveries, no match result or
scorecard, no tournaments (Phases 1–3 — Foundation, Teams & Players,
and Match Setup — are done; the Scoring Engine is next). See
[development-guidelines.md](development-guidelines.md) and
[implementation-plan.md](implementation-plan.md).
