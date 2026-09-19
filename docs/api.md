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

No player/team/tournament/match/scoring endpoints yet (Phase 1 —
Foundation is done; Teams & Players is next). See
[development-guidelines.md](development-guidelines.md) and
[implementation-plan.md](implementation-plan.md).
