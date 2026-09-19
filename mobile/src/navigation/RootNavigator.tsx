import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import React from 'react';

import HomeScreen from '../screens/HomeScreen';
import type { RootStackParamList } from './types';

const Stack = createNativeStackNavigator<RootStackParamList>();

/**
 * App-wide navigator. Currently exposes a single "Home" route; new
 * screens can be added as `<Stack.Screen />` entries once they exist,
 * after extending `RootStackParamList` in ./types.ts.
 */
function RootNavigator(): React.JSX.Element {
  return (
    <NavigationContainer>
      <Stack.Navigator initialRouteName="Home">
        <Stack.Screen
          name="Home"
          component={HomeScreen}
          options={{ title: 'Super Score' }}
        />
      </Stack.Navigator>
    </NavigationContainer>
  );
}

export default RootNavigator;
