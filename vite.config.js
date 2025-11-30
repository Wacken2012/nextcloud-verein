import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  define: {
    'process.env': {}
  },
  build: {
    outDir: 'js/dist',
    lib: {
      entry: path.resolve(__dirname, 'js/main.js'),
      name: 'VereinApp',
      formats: ['es'],
      // produce an ES module file with .mjs extension so Nextcloud can load it as module
      fileName: (format) => `nextcloud-verein.mjs`
    },
    rollupOptions: {
      external: [],
      output: {
        globals: {}
      }
    },
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: false
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './js')
    }
  }
})
