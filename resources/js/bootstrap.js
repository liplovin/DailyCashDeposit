import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token Management
function getCSRFToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

// Set initial CSRF token from meta tag
const initialToken = getCSRFToken();
if (initialToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = initialToken;
}

// Request interceptor: Always include latest CSRF token
window.axios.interceptors.request.use(
    config => {
        const token = getCSRFToken();
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
        return config;
    },
    error => Promise.reject(error)
);

// Response interceptor: Handle 419 errors and update CSRF token
window.axios.interceptors.response.use(
    response => {
        // Update CSRF token on successful response
        const newToken = response.headers['x-csrf-token'] || getCSRFToken();
        if (newToken && newToken !== getCSRFToken()) {
            document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', newToken);
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
        }
        return response;
    },
    error => {
        if (error.response?.status === 419) {
            // Session expired, reload page to get new token
            window.location.reload();
        }
        if (error.response?.status === 414) {
            // URI too long - likely CSRF token mismatch, reload to sync token
            console.warn('414 URI Too Long - reloading to sync CSRF token');
            window.location.reload();
        }
        return Promise.reject(error);
    }
);
