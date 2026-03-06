import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Add request interceptor to inject CSRF token
window.axios.interceptors.request.use((config) => {
    // Get fresh token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (token) {
        // Add to headers
        config.headers['X-CSRF-TOKEN'] = token;
        
        // Add to FormData if it's FormData
        if (config.data instanceof FormData && !config.data.has('_token')) {
            config.data.append('_token', token);
        }
    }
    
    return config;
}, (error) => {
    return Promise.reject(error);
});
