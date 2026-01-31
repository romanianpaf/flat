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
  optimizeDeps: {
    exclude: [
      'jkanban', 
      'dragula',
      '@fullcalendar/core',
      '@fullcalendar/daygrid',
      '@fullcalendar/interaction',
      '@fullcalendar/timegrid'
    ],  // Excludem bibliotecile problematice din pre-bundling
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
        // Silențiază deprecation warnings din sass (Bootstrap/template folosesc sintaxă veche)
        silenceDeprecations: [
          'legacy-js-api',
          'import',           // @import deprecated, trebuie @use
          'global-builtin',   // mix(), map-merge(), unit() etc.
          'color-functions',  // darken(), lighten(), fade-in()
          'function-units',   // passing numbers without units
          'if-function',      // if() syntax
          'abs-percent',      // abs() with percentage
        ],
      }
    }
  },
  build: {
    outDir: 'dist',
    assetsDir: '',
    chunkSizeWarningLimit: 1000, // Crește limita la 1MB pentru warning
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
        },
        // Code splitting pentru biblioteci mari
        manualChunks: {
          'vendor-vue': ['vue', 'vue-router', 'vuex'],
          'vendor-ui': ['bootstrap', 'sweetalert2', 'chart.js'],
          'vendor-forms': ['vee-validate', 'yup', 'choices.js', 'quill'],
          'vendor-utils': ['axios', 'jsona', 'three'],
        }
      }
    }
  },
  server: {
    port: 8080,
    open: true
  }
})
