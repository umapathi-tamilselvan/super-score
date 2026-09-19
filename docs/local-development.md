# Local Development

## Prerequisites

- Docker + Docker Compose (backend)
- Node.js 20+ and npm (both backend asset build and mobile app)
- PHP 8.3+ and Composer (only needed if running the backend outside
  Docker)
- For mobile native builds: Android Studio / Xcode as usual for React
  Native (see [mobile.md](mobile.md))

## Backend

```bash
git clone <repo-url> super-score
cd super-score

cp backend/.env.example backend/.env
cd backend && composer install && php artisan key:generate && cd ..

docker compose up -d --build
docker compose exec app php artisan migrate
```

This starts six services (see [architecture.md](architecture.md)):

| Service | Purpose                              | Host port |
|---------|---------------------------------------|-----------|
| app     | PHP-FPM running the Laravel app       | —         |
| nginx   | Web server, proxies to `app`          | 8000      |
| mysql   | MySQL 8 database                      | 3306      |
| redis   | Redis (cache + queue)                 | 6379      |
| queue   | `php artisan queue:work` worker       | —         |
| reverb  | `php artisan reverb:start` WebSocket server | 8082 (mapped from container port 8080) |

Verify it's working:

```bash
curl http://localhost:8000/api/v1/health
curl -I http://localhost:8000/
```

Stop everything with `docker compose down`.

### Running without Docker

You can run the Laravel app directly against the host PHP for quick
iteration, but you'll need MySQL/Redis available (or switch
`DB_CONNECTION` to something else, and `SESSION_DRIVER`/`CACHE_STORE`/
`QUEUE_CONNECTION` to `file`/`sync` temporarily):

```bash
cd backend
composer install
npm install && npm run build
php artisan serve
```

## Mobile

```bash
cd mobile
npm install
npm run android
```

```bash
npm run ios   # macOS only — first run: cd ios && pod install
```

By default the app points at `http://localhost:8000` in development
(`src/config/api.ts`). If you're running the backend via Docker on the
same machine and testing on the Android emulator, use
`http://10.0.2.2:8000` instead — see [mobile.md](mobile.md) for details.

## Ports summary

| Port | Service                          |
|------|-----------------------------------|
| 8000 | Laravel (web + API), via nginx    |
| 3306 | MySQL                              |
| 6379 | Redis                               |
| 8082 | Reverb (WebSocket, host-mapped)     |

## Troubleshooting

- **Permission errors writing to `storage/` inside Docker**: the backend
  container's entrypoint (`docker/php/entrypoint.sh`) re-chowns
  `storage/` and `bootstrap/cache/` to `www-data` on every start, to
  correct for the bind-mounted host directory's ownership. If you still
  see permission errors, restart the affected container.
- **Reverb port already in use**: the `reverb` service maps container
  port `8080` to host port `8082` by default (some environments already
  use `8080`). Change the host-side mapping in `docker-compose.yml` if
  `8082` is also taken.
