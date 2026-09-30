import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Dua entry point terpisah: panel admin (Gentelella, tanpa Bootstrap/jQuery)
// dan situs publik (UniPulse, Bootstrap 5). Jangan saling impor.
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/admin.scss',
                'resources/js/admin.js',
                'resources/scss/public.scss',
                'resources/js/public.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // SCSS template UniPulse memakai @import dan fungsi lama; file aslinya tidak diedit.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
