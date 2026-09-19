import axios, { AxiosError } from 'axios';

import { API_TIMEOUT, API_URL } from '../config/api';

/**
 * Centralized Axios instance for all network requests.
 *
 * Every API call in the app should go through this client rather than
 * creating ad-hoc axios/fetch calls, so that base URL, timeout, headers
 * and error handling stay consistent in one place.
 */
export const apiClient = axios.create({
  baseURL: API_URL,
  timeout: API_TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// --- Request interceptor -------------------------------------------------
// Placeholder for future authentication: once auth is implemented, attach
// the current auth token here, e.g.:
//
//   apiClient.interceptors.request.use((config) => {
//     const token = getStoredAuthToken();
//     if (token) {
//       config.headers.Authorization = `Bearer ${token}`;
//     }
//     return config;
//   });
//
// No token storage, retrieval, or refresh logic exists yet — this is
// intentionally left unimplemented for now.

// --- Response interceptor -------------------------------------------------
// Minimal, generic normalization/logging of errors. No retry logic and no
// business logic here on purpose.
apiClient.interceptors.response.use(
  response => response,
  (error: AxiosError) => {
    const status = error.response?.status;
    const message = error.response?.data ?? error.message;

    console.warn('[apiClient] Request failed', { status, message });

    return Promise.reject(error);
  },
);

export default apiClient;
