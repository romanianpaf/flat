import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { viteCommonjs } from '@originjs/vite-plugin-commonjs'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default defineConfig({
  plugins: [
    vue(),
    viteCommonjs()  // Suport pentru require() în migrare
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        // Silențiază deprecation warnings din sass
        silenceDeprecations: ['legacy-js-api'],
      }
    }
  },
  build: {
    outDir: 'dist',
    assetsDir: '',
    rollupOptions: {
      output: {
        // Structură similară cu Vue CLI pentru compatibilitate cu rebuild.sh
        entryFileNames: 'js/[name].[hash].js',
        chunkFileNames: 'js/[name].[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name].[hash][extname]'
          }
          if (/\.(png|jpe?g|gif|svg|webp|ico)$/.test(assetInfo.name)) {
            return 'img/[name].[hash][extname]'
          }
          if (/\.(woff2?|eot|ttf|otf)$/.test(assetInfo.name)) {
            return 'fonts/[name].[hash][extname]'
          }
          return '[name].[hash][extname]'
        }
      }
    }
  },
  server: {
    port: 8080,
    open: true
  }
})
