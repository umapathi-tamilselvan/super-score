/**
 * @format
 */

import { render, screen } from '@testing-library/react-native';
import React from 'react';

import HomeScreen from '../src/screens/HomeScreen';

test('renders the Super Score title', () => {
  render(<HomeScreen />);

  expect(screen.getByText('Super Score')).toBeTruthy();
});

test('renders the subtitle and coming soon badge', () => {
  render(<HomeScreen />);

  expect(
    screen.getByText('Cricket Scoring & Statistics Platform'),
  ).toBeTruthy();
  expect(screen.getByText('Coming Soon')).toBeTruthy();
});
