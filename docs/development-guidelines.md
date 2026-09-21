# Development Guidelines

These guidelines apply to the current foundation stage of Super Score.
They will be extended as cricket domain features are added.

## Scope of this stage

Per [implementation-plan.md](implementation-plan.md), the web app
(backend + Blade) is being built end-to-end through six phases before
mobile starts. Current status:

- **Phase 1 — Foundation: done.** Registration, OTP verification
  (email), login, logout, forgot/reset password, profile setup/edit, and
  a dashboard shell — on both `/api/v1` and the Blade web app, sharing
  the same `AuthService`/`OtpService`/`ProfileService` layer. See
  [api.md](api.md) for the endpoint list.
- **Phase 2 — Teams & Players: done.** Team CRUD; a player is a
  registered user's own cricket profile (one per account, self-service
  only — see `PlayerPolicy`); team owners add any existing player to
  their squad (reusable across teams) and assign
  captain/vice-captain/wicket-keeper — on both `/api/v1` and the Blade
  web app, sharing `TeamService`/`PlayerService`. See [api.md](api.md).
- **Phase 3 — Match Setup: done.** Create match, select the two teams
  (validated for a full squad each), select each team's playing XI
  (captain + wicket keeper required, vice-captain optional), and record
  the toss (batting/bowling-first derived, not stored) — on both
  `/api/v1` and the Blade web app, sharing `MatchSetupService`/
  `PlayingXiService`/`TossService`. The model is `CricketMatch` (`Match`
  is a PHP reserved word). See [api.md](api.md).
- **Phase 4 onward — not started.** No innings, overs, deliveries,
  wickets, extras, partnerships, scorecards, statistics, leaderboards,
  live scoring, live commentary, tournaments, or analytics. No
  notifications, payments, social features, AI features, fantasy
  cricket, or live streaming.
- A React Native app (`mobile/`) with a single placeholder screen, basic
  navigation, and a centralized API client — mobile work doesn't start
  until the web track finishes (see implementation-plan.md).

When those features are built, they should slot into the structure
already in place (`app/Actions`, `app/Services`, `app/Http/Controllers/Api/V1`,
`mobile/src/screens`, `mobile/src/navigation`, etc.) rather than
requiring a restructure.

## Backend code quality

- **Laravel Pint** for code style (`./vendor/bin/pint`), PSR-12 baseline.
- Follow Laravel conventions: thin controllers, validation via Form
  Requests (`app/Http/Requests`), API responses shaped via API Resources
  (`app/Http/Resources`) once endpoints return models.
- Use type declarations on all new methods/properties.
- Keep `app/Http/Controllers/Api/V1` and `app/Http/Controllers/Web`
  strictly separate — a controller should not serve both API and web
  responses.
- Configuration belongs in `config/` + `.env`, never hardcoded.
- Every new endpoint or page should have a corresponding Feature test
  under `tests/Feature/Api` or `tests/Feature/Web`.

## Mobile code quality

- **ESLint + Prettier** must pass (`npx eslint .`).
- **TypeScript strict mode** must pass (`npx tsc --noEmit`).
- No business logic inside UI components — components render, hooks and
  services (`src/hooks`, `src/services`) hold logic, `src/api` holds
  network calls.
- Never hardcode an API URL — always import from `src/config/api.ts`.
- Every new screen should have at least a render smoke test under
  `__tests__/`.

## Adding a new screen (mobile) without restructuring

1. Add the route to `RootStackParamList` in `src/navigation/types.ts`.
2. Create the screen component under `src/screens/`.
3. Register it as a `<Stack.Screen />` in `src/navigation/RootNavigator.tsx`.

## Adding a new API endpoint (backend)

1. Add the route under the existing `v1` group in `routes/api.php`.
2. Add a controller under `app/Http/Controllers/Api/V1`.
3. Validate input with a Form Request in `app/Http/Requests` if the
   endpoint accepts input.
4. Shape the response with an API Resource in `app/Http/Resources` if it
   returns a model.
5. Add a Feature test under `tests/Feature/Api`.

## Git

- Single repository at the `super-score/` root — `backend/` and
  `mobile/` are not separate repos or submodules.
- Never commit `.env` files, only `.env.example`.
- Keep commit messages focused and descriptive of the "why", not just
  the "what".
