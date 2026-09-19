# Architecture

Super Score is a single repository containing a Laravel backend (REST API +
Blade web app) and a React Native mobile app. This document describes the
foundation architecture only — no cricket domain modules exist yet.

## System Diagram

```text
                         SUPER SCORE
                              │
             ┌────────────────┴────────────────┐
             │                                 │
        Laravel Application              React Native
             │                                 │
      ┌──────┴──────┐                          │
      │             │                          │
    Blade        REST API ◄────────────────────┘
      │             │
      │        /api/v1
      │             │
      └──────┬──────┘
             │
       Laravel Core
             │
      ┌──────┼────────┐
      │      │        │
    MySQL  Redis   Reverb
             │
          Queue
```

## Components

- **Laravel application** (`backend/`) — a single Laravel codebase serving
  two front doors:
  - **REST API** under `/api/v1`, versioned, consumed by the mobile app and
    any future clients. Controllers live in `app/Http/Controllers/Api/V1`.
  - **Blade web application**, server-rendered with Bootstrap 5 and
    Alpine.js. Controllers live in `app/Http/Controllers/Web`.
- **React Native app** (`mobile/`) — a TypeScript app that talks to the
  Laravel REST API over HTTP via a centralized Axios client. It does not
  talk to MySQL/Redis/Reverb directly.
- **MySQL 8** — primary relational datastore.
- **Redis** — cache store and queue backend.
- **Laravel Queue** — background job processing, backed by Redis.
- **Laravel Reverb** — WebSocket server for realtime features (planned:
  live scores, commentary, match events). Infrastructure only for now.
- **Laravel Sanctum** — API token authentication, installed but not yet
  wired into any login/register flow.

## Why one Laravel app for both API and web

Cricket data (matches, scorecards, statistics) will be shared between the
web dashboard and the mobile app. Keeping both in one Laravel codebase
avoids duplicating models, policies, and business logic across two
backends, while still keeping API and Web controllers, requests, and
resources in clearly separate namespaces (`Http/Controllers/Api/V1` vs.
`Http/Controllers/Web`).

## Future domain layer

The `app/` directory already has placeholders for where cricket domain
logic will live as it's built:

```text
app/Actions/     Single-purpose application actions
app/Events/      Domain events (e.g. future WicketFallen, ScoreUpdated)
app/Jobs/        Queued jobs
app/Listeners/   Event listeners
app/Policies/    Authorization policies
app/Services/    Domain services
app/Support/     Shared helpers/utilities
```

None of these contain cricket logic yet — see
[development-guidelines.md](development-guidelines.md) for what's in scope
for this stage of the project.
