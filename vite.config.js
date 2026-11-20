import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { fontsource } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',
                // Panel Admin (Filament).
                'resources/css/filament/admin/theme.css',
                'resources/js/filament/admin.ts',
            ],
            refresh: true,
            // Roboto dari paket npm @fontsource/roboto (versi terkunci di package-lock.json), bukan
            // diunduh dari fonts.bunny.net saat build: build (lokal & CI) tidak bergantung pada
            // layanan pihak ketiga yang bisa down (mis. 504 Gateway Timeout).
            fonts: [
                fontsource('Roboto', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
