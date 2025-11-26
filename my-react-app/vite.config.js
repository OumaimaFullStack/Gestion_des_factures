import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      // Proxy all requests ending with .php to your PHP backend
      '^/.*\\.php$': {
        target: 'http://localhost:8000', // Assurez-vous que c'est le bon port pour votre backend PHP
        changeOrigin: true,
        secure: false,  // Désactivez la vérification SSL pour éviter les problèmes si vous n'utilisez pas HTTPS
        rewrite: (path) => path.replace(/^\/.*\.php$/, '/$&'), // Assurez-vous que l'URL est réécrite correctement
      },
    },
  },
})
