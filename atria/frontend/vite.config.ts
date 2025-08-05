import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { resolve } from 'path'
import { copyFileSync } from 'fs'
import { join } from 'path'

export default defineConfig({
  plugins: [
    react(),
    {
      name: 'copy-htaccess',
      closeBundle() {
        // Copy .htaccess to build directory
        try {
          copyFileSync(
            join(__dirname, '.htaccess'),
            join(__dirname, 'build', '.htaccess')
          );
          console.log('✅ .htaccess copied to build directory');
        } catch (error) {
          console.warn('⚠️ .htaccess not found, skipping copy');
        }
      },
    },
  ],
  server: {
    proxy: {
      '/api': 'http://backend.atria.live',
    },
  },
  build: {
    outDir: 'build',
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'index.html'),
      },
    },
  },
  assetsInclude: ['**/*.jpeg', '**/*.jpg', '**/*.png', '**/*.gif', '**/*.svg', '**/*.webp'],
})
