import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/material-management.js',
                    'resources/js/bootstrap.js',
                    'resources/js/login.js',
                    'resources/js/Unggah.js'],
            refresh: true,
        }),
    ],
});
