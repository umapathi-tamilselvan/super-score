# Backend (Laravel)

`backend/` is a Laravel application (PHP 8.3+) serving both the REST API
and the Blade web application.

## Stack

- Laravel (PHP 8.3+)
- MySQL 8
- Redis (cache, queue)
- Laravel Sanctum (API token auth — installed, not yet wired to any UI)
- Laravel Reverb (WebSocket server — installed, not yet broadcasting any
  domain events)
- PHPUnit (test runner)
- Laravel Pint (code style)

## Directory structure

```text
backend/
├── app/
│   ├── Actions/                   Single-purpose application actions
│   ├── Enums/                     OtpPurpose, PreferredRole, etc.
│   ├── Events/                    Domain events
│   ├── Exceptions/                Custom exceptions
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/            API controllers (versioned)
│   │   │   └── Web/               Web (Blade) controllers
│   │   ├── Middleware/             e.g. EnsureOtpVerified
│   │   ├── Requests/               Form request validation classes
│   │   └── Resources/              API resource transformers
│   ├── Jobs/                      Queued jobs
│   ├── Listeners/                 Event listeners
│   ├── Models/
│   ├── Notifications/             e.g. OtpCodeNotification
│   ├── Policies/                  Authorization policies
│   ├── Services/                  Domain services, bound to app/Services/Contracts
│   │   └── Contracts/              Service interfaces (Dependency Inversion)
│   └── Support/                   Shared helpers/utilities
├── database/
├── resources/
│   ├── views/
│   │   ├── layouts/               Blade layouts (app.blade.php)
│   │   ├── components/            Reusable Blade components
│   │   └── pages/                 Page views (home.blade.php)
│   ├── css/                       app.css (Bootstrap + Bootstrap Icons)
│   └── js/                        app.js (Alpine.js bootstrap)
├── routes/
│   ├── api.php                    All routes prefixed /api/v1
│   ├── web.php
│   └── channels.php                Broadcasting channel authorization
└── tests/
    ├── Feature/
    │   ├── Api/
    │   └── Web/
    └── Unit/
```

API controllers and Web controllers are kept in separate namespaces so the
two front doors never accidentally share request/response concerns.

## Routes

- `GET /` → `Web\HomeController` → renders `resources/views/pages/home.blade.php` (redirects to `/dashboard` if already logged in)
- `GET /api/v1/health` → `Api\V1\HealthController` → JSON health check
- Auth, OTP verification, password reset, profile, dashboard, team, player, and match setup routes — see [api.md](api.md) for the full endpoint list. Web equivalents live alongside them in `routes/web.php`, guarded by the `guest`/`auth`/`otp.verified` middleware, with ownership checked via `TeamPolicy`/`PlayerPolicy`/`CricketMatchPolicy`.

All API routes are versioned under `/api/v1` (see `routes/api.php`), so
breaking changes in the future can ship as `/api/v2` without touching
existing mobile clients.

## Running commands

Once the Docker stack is up (`docker compose up -d` from the repo root):

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan tinker
```

Or locally against the same `backend/` code without Docker (useful for
quick iteration, but you'll need your own MySQL/Redis or to temporarily
switch `DB_CONNECTION`/`CACHE_STORE`/`SESSION_DRIVER`/`QUEUE_CONNECTION` to
drivers that don't need them):

```bash
cd backend
composer install
npm install && npm run build   # builds Bootstrap/Alpine assets for Blade
php artisan serve
php artisan test
./vendor/bin/pint
```

## Environment configuration

Copy `backend/.env.example` to `backend/.env` and set `APP_KEY`
(`php artisan key:generate`). The example file is pre-configured to match
the Docker Compose service names (`mysql`, `redis`, `reverb`) so
`docker compose up -d` works out of the box.

## What's intentionally not here yet

No scoring engine (innings/overs/deliveries/wickets/extras), no match
result or scorecard, no tournament models, no real admin dashboard
(only the Phase 1 shell), no push/SMS notifications (OTP is email-only
for now, behind a swappable `OtpNotifierInterface`). See
[development-guidelines.md](development-guidelines.md) and
[implementation-plan.md](implementation-plan.md).
