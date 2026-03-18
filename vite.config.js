import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';

export default defineConfig({
    plugins: [
        symfonyPlugin(),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        cors: true,
    },
    build: {
        rollupOptions: {
            input: {
                app: './assets/app.js',
            },
        },
    },
});
