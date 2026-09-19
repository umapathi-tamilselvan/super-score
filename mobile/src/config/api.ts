/**
 * Central environment / API configuration.
 *
 * This is the single source of truth for the API base URL and related
 * network settings. Nothing else in the app should hardcode a URL —
 * always import from here instead.
 *
 * Note on react-native-config: a native-linked `.env` solution
 * (react-native-config) was intentionally left out of this initial
 * foundation because wiring it up requires Android Gradle changes and
 * iOS Info.plist/xcconfig edits that cannot be verified without a
 * device/emulator/macOS in this environment. This module is written so
 * that swapping in `react-native-config` (or any other env loader)
 * later is a small, localized change: replace the `getEnv()` /
 * `ENV` resolution below with a read from `Config.APP_ENV` and keep
 * everything downstream (API_BASE_URL, API_URL, etc.) the same.
 */

export type Environment = 'development' | 'staging' | 'production';

const DEFAULT_ENV: Environment = 'development';

const isEnvironment = (value: unknown): value is Environment =>
  value === 'development' || value === 'staging' || value === 'production';

const resolveEnv = (): Environment => {
  const candidate = process.env.APP_ENV;
  return isEnvironment(candidate) ? candidate : DEFAULT_ENV;
};

/** Current running environment. Defaults to 'development'. */
export const CURRENT_ENV: Environment = resolveEnv();

/**
 * Base URL per environment.
 *
 * NOTE for Android emulator users: `localhost` / `127.0.0.1` from inside
 * the Android emulator refers to the emulator itself, not your host
 * machine. Use `10.0.2.2` instead (e.g. `http://10.0.2.2:8000`) to reach
 * a backend running on your development machine. iOS simulators can use
 * `localhost` directly. See mobile/.env.example and mobile/README.md.
 */
const API_BASE_URLS: Record<Environment, string> = {
  development: 'http://localhost:8000',
  staging: 'https://staging.superscore.app',
  production: 'https://api.superscore.app',
};

export const API_BASE_URL = API_BASE_URLS[CURRENT_ENV];
export const API_VERSION = 'v1';
export const API_URL = `${API_BASE_URL}/api/${API_VERSION}`;
export const API_TIMEOUT = 15000;
