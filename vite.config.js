import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ command, mode }) => {
    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        // Solo aplica configuración de server si estás en desarrollo
        ...(mode !== 'production' && {
            server: {
                cors: true,
                host: 'localhost',
                port: 5173,
                strictPort: true,
            },
        }),
    };
});
