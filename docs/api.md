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

## Authentication

[Laravel Sanctum](https://laravel.com/docs/sanctum) is installed and
configured (`config/sanctum.php`, `HasApiTokens` on the `User` model), so
token-based authentication is ready to be wired up for the mobile app,
the web app, and future API clients. No login/register endpoints, roles,
or permissions exist yet.

## Realtime

[Laravel Reverb](https://laravel.com/docs/reverb) is installed and
configured as the broadcast driver (`BROADCAST_CONNECTION=reverb`), with
`routes/channels.php` in place for future channel authorization. No
events are broadcast yet — this is infrastructure only, intended for
future live scores, commentary, and match event updates.

## What's intentionally not here yet

No player/team/tournament/match/scoring endpoints. See
[development-guidelines.md](development-guidelines.md).
