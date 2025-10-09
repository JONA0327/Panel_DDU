import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/ddu-modal.css',
                'resources/js/ddu-modal.js',
            ],
            refresh: true,
        }),
    ],
});
