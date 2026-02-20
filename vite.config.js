import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        host: '0.0.0.0',   // allow access from LAN
        port: 5173,        // or any port you prefer
        strictPort: true,
        hmr: {
            host: '192.168.1.5',
            port: 5173,
        },
        cors: true, // enable CORS for all origins
    },

    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});

