import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
import { visualizer } from 'rollup-plugin-visualizer'
import terser from '@rollup/plugin-terser'

export default defineConfig({
  plugins: [
    vue(),
    visualizer({
      filename: 'bundle-stats.html',
      open: false,
      gzipSize: true
    })
  ],
  define: {
    'process.env': {}
  },
  build: {
    outDir: 'js/dist',
    lib: {
      entry: path.resolve(__dirname, 'js/main.js'),
      name: 'VereinApp',
      formats: ['es'],
      fileName: (format) => `nextcloud-verein.mjs`
    },
    rollupOptions: {
      external: [],
      output: {
        globals: {},
        inlineDynamicImports: true
      },
      plugins: [
        terser({
          compress: {
            drop_console: true,
            drop_debugger: true,
            passes: 3,
            pure_funcs: ['console.log', 'console.info', 'console.debug'],
            unsafe: true,
            unsafe_comps: true,
            unsafe_math: true
          },
          mangle: {
            properties: false
          },
          format: {
            comments: false
          }
        })
      ]
    },
    minify: false  // Use rollup-plugin-terser instead
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './js')
    }
  }
})
