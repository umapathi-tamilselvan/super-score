# Development Guidelines

These guidelines apply to the current foundation stage of Super Score.
They will be extended as cricket domain features are added.

## Scope of this stage

This repository currently contains **only the project foundation**:

- A Laravel app (`backend/`) with a health-check API endpoint and a
  placeholder home page, Sanctum and Reverb installed, MySQL/Redis/Queue
  configured, and a Docker Compose dev environment.
- A React Native app (`mobile/`) with a single placeholder screen, basic
  navigation, and a centralized API client, ready to grow.

No cricket domain logic exists yet: no players, teams, tournaments,
matches, playing XI, toss, innings, overs, deliveries, wickets, extras,
partnerships, scorecards, statistics, leaderboards, live scoring, live
commentary, or analytics. No admin dashboard, notifications, payments,
social features, AI features, fantasy cricket, or live streaming.

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
