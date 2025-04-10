// inside vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'
// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 3000, // Sets the development server to run on port 3000
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src') // '@' will point to the src directory
    }
  }
})