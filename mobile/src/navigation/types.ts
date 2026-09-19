/**
 * Root stack param list for the app's navigation.
 *
 * Only "Home" exists today. Add future screens (Matches, Teams, Players,
 * Tournaments, Statistics, Profile, Scorer, ...) here as
 * `ScreenName: paramsType | undefined` so the whole app stays type-safe
 * as it grows, e.g.:
 *
 *   export type RootStackParamList = {
 *     Home: undefined;
 *     Matches: undefined;
 *     MatchDetails: { matchId: string };
 *   };
 */
export type RootStackParamList = {
  Home: undefined;
};
