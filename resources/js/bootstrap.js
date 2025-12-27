import axios from 'axios';
window.axios = axios;

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
    if (!error.response) {
      // No response means network error / connection refused
      window.dispatchEvent(new CustomEvent('network-error', { detail: { message: 'Network Error: Cannot reach backend server.' } }))
    }
    return Promise.reject(error)
  }
)
