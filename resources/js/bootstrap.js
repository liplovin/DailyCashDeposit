import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add request interceptor to inject CSRF token
window.axios.interceptors.request.use((config) => {
    // Get fresh token from meta tag on every request
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (token) {
        // Always add to headers - this is the most reliable method
        config.headers['X-CSRF-TOKEN'] = token;
        
        // Also add to request body for POST/PUT/PATCH/DELETE (for max compatibility)
        const method = config.method?.toLowerCase?.() || '';
        if (['post', 'put', 'patch', 'delete'].includes(method)) {
            if (config.data instanceof FormData) {
                // Handle FormData (multipart/form-data)
                if (!config.data.has('_token')) {
                    config.data.append('_token', token);
                }
            } else if (typeof config.data === 'string') {
                // Handle URL-encoded form data (application/x-www-form-urlencoded)
                if (config.data && !config.data.includes('_token=')) {
                    config.data += (config.data.endsWith('&') ? '' : '&') + '_token=' + encodeURIComponent(token);
                }
            } else if (typeof config.data === 'object' && config.data !== null) {
                // Handle plain objects (application/json)
                config.data._token = token;
            } else if (!config.data) {
                // If no data yet, create an object with the token
                config.data = { _token: token };
            }
        }
    }
    
    return config;
}, (error) => {
    return Promise.reject(error);
});
