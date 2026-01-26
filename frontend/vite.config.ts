import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { resolve } from 'path'

export default defineConfig(({ command }) => ({
  plugins: [react()],
  root: __dirname,
  server: {
    port: 5173,
  },
  build: {
    outDir: resolve(__dirname, '../backend/public/build'),
    emptyOutDir: true,
    rollupOptions: {
      input: resolve(__dirname, 'index.html')
    }
  }
}))
