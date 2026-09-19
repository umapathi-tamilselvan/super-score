/**
 * Minimal ambient declaration for `process.env` as used by
 * src/config/api.ts. We deliberately avoid pulling in the full
 * `@types/node` package (its ambient globals can clash with React
 * Native's own type definitions) and instead declare just the shape we
 * rely on.
 */
declare const process: {
  env: {
    APP_ENV?: string;
    [key: string]: string | undefined;
  };
};
