import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

/**
 * Landing screen for Super Score. This is placeholder foundation UI —
 * real cricket features (matches, scoring, teams, statistics, ...) will
 * replace/extend this later.
 */
function HomeScreen(): React.JSX.Element {
  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.container}>
        <Text style={styles.title}>Super Score</Text>
        <Text style={styles.subtitle}>
          Cricket Scoring & Statistics Platform
        </Text>
        <View style={styles.badge}>
          <Text style={styles.badgeText}>Coming Soon</Text>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#0B1120',
  },
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
  },
  title: {
    fontSize: 36,
    fontWeight: '800',
    color: '#FFFFFF',
    letterSpacing: 0.5,
  },
  subtitle: {
    marginTop: 8,
    fontSize: 15,
    color: '#94A3B8',
    textAlign: 'center',
  },
  badge: {
    marginTop: 28,
    paddingHorizontal: 16,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: '#1E293B',
    borderWidth: 1,
    borderColor: '#334155',
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '600',
    color: '#38BDF8',
    letterSpacing: 0.5,
  },
});

export default HomeScreen;
