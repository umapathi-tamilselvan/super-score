# Mobile (React Native)

`mobile/` is a React Native app (TypeScript) that will become the Super
Score companion app. Only the foundation exists so far.

## Stack

- React Native (TypeScript template)
- React Navigation (native stack)
- Axios
- ESLint + Prettier
- Jest + React Native Testing Library

## Directory structure

```text
mobile/
├── src/
│   ├── api/            Axios client (client.ts)
│   ├── assets/          Images, fonts, etc.
│   ├── components/      Reusable UI components
│   ├── config/          Environment/API configuration (api.ts)
│   ├── constants/        App-wide constants
│   ├── hooks/            Reusable React hooks
│   ├── navigation/       RootNavigator + navigation types
│   ├── screens/          Screen components (HomeScreen.tsx)
│   ├── services/         Business-logic services (built on top of api/)
│   ├── storage/          Local persistence helpers
│   ├── types/            Shared TypeScript types
│   └── utils/            Generic utilities
├── android/              Native Android project
├── ios/                  Native iOS project
└── __tests__/
```

## Navigation

`src/navigation/RootNavigator.tsx` currently exposes a single `Home`
route. `src/navigation/types.ts` defines `RootStackParamList`, which is
the single place to add new routes (`Matches`, `Teams`, `Players`,
`Tournaments`, `Statistics`, `Profile`, `Scorer`, ...) as the app grows,
without restructuring the navigator itself.

## API configuration

`src/config/api.ts` is the single source of truth for `API_BASE_URL` /
`API_URL`, resolved per environment (`development` / `staging` /
`production` via `APP_ENV`). Nothing else in the app should hardcode an
API URL — import from this module instead.

`src/api/client.ts` exports a configured `apiClient` (Axios instance)
with the base URL, timeout, and JSON headers already set, plus a
commented placeholder showing where an auth token will be attached once
authentication is implemented. No authentication logic exists yet.

> **Android emulator note:** the emulator can't reach your host machine
> via `localhost` — use `http://10.0.2.2:<port>` instead. iOS simulators
> can use `localhost` directly. Physical devices need your machine's LAN
> IP. See `mobile/.env.example`.

## Running

```bash
cd mobile
npm install
npm run android
npm run ios     # macOS only, requires: cd ios && pod install
```

## Testing & quality

```bash
npm test          # Jest
npx eslint .       # lint
npx tsc --noEmit   # type-check (strict mode)
```

## What's intentionally not here yet

No Scoring/Player/Team/Tournament/Match/Statistics screens, no
authentication, no state management beyond React Navigation. See
[development-guidelines.md](development-guidelines.md).
