// vite.config.js
import { defineConfig } from 'vite'
import Symfony from '@symfony/reprise/vite'

export default defineConfig({
    // Définit le point d'entrée de votre application
    build: {
        rollupOptions: {
            input: {
                app: './assets/app.js', // ou './assets/app.ts' pour TypeScript
            },
        },
    },
    plugins: [
        Symfony({
            stimulus: 'assets/controllers.json',
        }),
    ],
})