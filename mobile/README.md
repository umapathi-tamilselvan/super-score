# Super Score Mobile

The React Native mobile app for **Super Score**, a cricket scoring and
statistics platform.

This package currently contains only the **foundation** of the app —
project scaffolding, navigation shell, API/config plumbing, and a single
placeholder screen. No cricket-specific features (scoring, matches,
teams, players, tournaments, statistics, etc.) have been built yet; they
will be added on top of this foundation.

## Tech stack

- [React Native](https://reactnative.dev) (TypeScript template)
- [React Navigation](https://reactnavigation.org) (native stack)
- [Axios](https://axios-http.com) for HTTP requests
- [ESLint](https://eslint.org) + [Prettier](https://prettier.io)
- [Jest](https://jestjs.io) + [@testing-library/react-native](https://callstack.github.io/react-native-testing-library/)

## Prerequisites

- Node.js (see `.nvmrc`/`engines` in `package.json`; developed against
  Node 20, the RN template's `package.json` requests Node >= 22 — either
  works for the JS/TS tooling used here, but keep native builds on a
  supported Node version)
- npm
- For Android builds: Android Studio, an Android SDK, and either an
  emulator or a physical device
- For iOS builds: **macOS** with Xcode and [CocoaPods](https://cocoapods.org)
  (iOS builds cannot be done on Linux/Windows)

## Setup

```sh
npm install
```

## Running

Start Metro (the JS bundler) in one terminal:

```sh
npm start
```

Then, in another terminal:

```sh
# Android (emulator or device connected)
npm run android

# iOS — macOS only, and only after installing CocoaPods dependencies:
cd ios && pod install && cd ..
npm run ios
```

## Tests

```sh
npm test
```

This runs the Jest suite, including a test asserting that `HomeScreen`
renders the "Super Score" title.

## Linting

```sh
npx eslint .
```

Type-checking:

```sh
npx tsc --noEmit
```

## Project structure (`src/`)

```
src/
  api/          Axios client and API call wrappers (src/api/client.ts)
  assets/       Images, fonts, and other static assets
  components/   Reusable, presentational UI components
  config/       Environment/config modules (src/config/api.ts)
  constants/    App-wide constant values
  hooks/        Custom React hooks
  navigation/   Navigators and navigation types (RootNavigator, types.ts)
  screens/      Top-level screens (e.g. HomeScreen)
  services/     Higher-level business/data logic built on top of api/
  storage/      Local persistence (e.g. AsyncStorage wrappers), once needed
  types/        Shared TypeScript types and ambient declarations
  utils/        General-purpose helper functions
```

Folders that are currently empty contain a `.gitkeep` placeholder so the
structure is preserved in version control until real files land in them.

## Environment configuration

There is no build-time `.env` loader (e.g. `react-native-config`) wired
up yet — it was intentionally left out of this initial foundation
because linking it requires native Android/iOS project changes that
can't be verified without a device/emulator/macOS in the environment
this scaffold was built in. Instead, `src/config/api.ts` is the single
source of truth for `API_BASE_URL` / `API_URL` and friends, keyed off a
`development | staging | production` environment. Every network call
should go through `src/api/client.ts` (which reads from this config)
rather than hardcoding a URL.

See `.env.example` at the root of `mobile/` for the environment
variable shape a future native env-loader integration would use.

**Android emulator note:** from inside the Android emulator,
`localhost`/`127.0.0.1` refers to the emulator itself, not your host
machine. Use `http://10.0.2.2:<port>` to reach a backend running on your
development machine. iOS simulators can use `localhost` directly, and
physical devices need your machine's LAN IP address.
