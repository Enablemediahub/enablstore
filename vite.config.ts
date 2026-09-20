import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.ts',
            refresh: true,
        }),
        tailwindcss(),
        VitePWA({
            registerType: 'autoUpdate',
            scope: '/',
            workbox: {
                navigateFallbackDenylist: [/^\/client\/[^/]+\/pos\/manifest\.json$/],
                navigateFallback: '/',
                runtimeCaching: [
                    {
                        urlPattern: ({ request }) =>
                            request.destination === 'document',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages',
                        },
                    },
                ],
            },
            manifest: {
                name: 'Enablstore POS',
                short_name: 'Enablstore',
                description: 'Offline-capable retail point of sale',
                theme_color: '#059669',
                background_color: '#f9fafb',
                display: 'standalone',
                start_url: '/dashboard',
                scope: '/',
                icons: [
                    {
                        src: '/images/Enablstore-cropped.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                ],
            },
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
