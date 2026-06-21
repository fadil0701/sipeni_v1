import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode, command }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const subpath = (env.APP_SUBPATH || '').replace(/^\/|\/$/g, '');
    const base = command === 'build'
        ? (subpath ? `/${subpath}/build/` : '/build/')
        : '/';

    return {
        base,
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            strictPort: false,
            hmr: {
                host: process.env.VITE_HMR_HOST || '127.0.0.1',
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
