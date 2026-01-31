import axios from 'axios';
window.axios = axios;

// Set base URL dynamically using window.location.origin to match the current page's origin
window.axios.defaults.baseURL = window.location.origin;

// Enable credentials for cross-origin requests
window.axios.defaults.withCredentials = true;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Add CSRF token from meta tag so POST requests from Axios are accepted by Laravel
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
// Prefer JSON responses for XHR
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Interceptor: detect network errors and emit a custom DOM event so Vue pages can show friendly messages
window.axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response) {
      // Handle 403 errors specifically
      if (error.response.status === 403) {
        console.error('403 Forbidden Error:', error.response.data);
        window.dispatchEvent(new CustomEvent('auth-error', { detail: { message: 'Access forbidden. Please check your authentication.', status: 403 } }));
      }
    } else if (!error.response) {
      // No response means network error / connection refused
      window.dispatchEvent(new CustomEvent('network-error', { detail: { message: 'Network Error: Cannot reach backend server.' } }))
    }
    return Promise.reject(error)
  }
)
