module.exports = {
  preset: '@react-native/jest-preset',
  // The RN preset only whitelists react-native itself for transformation.
  // These extra packages ship untranspiled ESM and also need to go
  // through babel-jest instead of being ignored.
  transformIgnorePatterns: [
    'node_modules/(?!((jest-)?react-native|@react-native(-community)?|@react-navigation|react-native-screens|react-native-safe-area-context)/)',
  ],
};
