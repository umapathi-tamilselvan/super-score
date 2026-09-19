# Super Score

Cricket scoring and statistics platform.

This repository contains the initial project foundation only. Cricket domain
features (players, teams, tournaments, matches, scoring, statistics, etc.)
are not implemented yet — see [docs/development-guidelines.md](docs/development-guidelines.md)
for the roadmap and ground rules.

## Architecture

```text
Laravel
    ├── REST API   (/api/v1)
    └── Blade Web

React Native
    └── Mobile App
```

The Laravel application serves both the REST API (consumed by the mobile
app and future clients) and the Blade-based web application, from a single
codebase in `backend/`. The React Native app in `mobile/` talks to the API
over HTTP.

See [docs/architecture.md](docs/architecture.md) for the full system diagram.

## Technology

```text
Backend:
Laravel, PHP 8.3, MySQL 8, Redis, Laravel Reverb, Laravel Sanctum

Web:
Blade, Bootstrap 5, Bootstrap Icons, Vite, Alpine.js

Mobile:
React Native, TypeScript, Axios, React Navigation
```

## Project Structure

```text
super-score/
├── backend/    Laravel REST API + Blade web application
├── mobile/     React Native mobile application
├── docs/       Project documentation
├── docker/     Docker build context (PHP/nginx config) for the backend
├── docker-compose.yml
└── README.md
```

## Backend Setup

The backend runs via Docker Compose (app, nginx, mysql, redis, queue, reverb).

```bash
cp backend/.env.example backend/.env
cd backend && composer install && php artisan key:generate && cd ..
docker compose up -d --build
docker compose exec app php artisan migrate
```

The API is then available at `http://localhost:8000/api/v1` and the web
app at `http://localhost:8000`.

See [docs/backend.md](docs/backend.md) and [docs/local-development.md](docs/local-development.md)
for details.

## Mobile Setup

```bash
cd mobile
npm install
npm run android
```

```bash
npm run ios   # macOS only
```

The mobile app does not run inside Docker — it uses the standard React
Native development workflow. See [docs/mobile.md](docs/mobile.md).

## Testing

Backend:

```bash
cd backend
php artisan test        # or: ./vendor/bin/pint --test
```

Mobile:

```bash
cd mobile
npm test
npx eslint .
npx tsc --noEmit
```

## Documentation

- [docs/architecture.md](docs/architecture.md) — system architecture and diagrams
- [docs/backend.md](docs/backend.md) — Laravel backend structure and conventions
- [docs/mobile.md](docs/mobile.md) — React Native app structure and conventions
- [docs/api.md](docs/api.md) — REST API conventions and endpoints
- [docs/local-development.md](docs/local-development.md) — full local dev setup
- [docs/development-guidelines.md](docs/development-guidelines.md) — code quality and contribution guidelines
