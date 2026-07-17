import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    watch: {
      usePolling: true
    },
    proxy: {
      '/api': {
        target: 'http://nginx-gateway',
        changeOrigin: true
      },
      '/ws': {
        target: 'ws://nginx-gateway',
        ws: true,
        changeOrigin: true
      }
    }
  }
})

