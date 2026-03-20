import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/accreditation.css',   // ← add this
                'resources/css/global-search.css',   // ← add this
            ],
            refresh: true,
        }),
    ],
});
