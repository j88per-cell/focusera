// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import fs from 'node:fs'
import path from 'node:path'

const themesDir = path.resolve(__dirname, 'resources/js/Themes')
const themes = fs.existsSync(themesDir)
  ? fs.readdirSync(themesDir).filter((d) => fs.existsSync(path.join(themesDir, d, 'app.js')))
  : []

// Build inputs: admin + every theme's main
const inputs = [
  'resources/js/Admin/app.js',
  ...themes.map((t) => `resources/js/Themes/${t}/app.js`),
  'resources/css/admin.css',
  ...themes.map((t) => `resources/css/Themes/${t}.css`),
]


export default defineConfig({
  plugins: [
    laravel({
      input: inputs,
      refresh: false,
    }),
	vue()
  ],
  resolve: {
  alias: {
    '@': path.resolve(__dirname, 'resources/js')
  }
}
})
