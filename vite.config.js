import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
      ],
      refresh: true,
    }),

    tailwindcss(),

    // Copies node_modules/tinymce -> public/build/vendor/tinymce
    viteStaticCopy({
      targets: [
        // v4 keeps the source path; stripBase drops the leading node_modules/.
        { src: 'node_modules/tinymce', dest: 'vendor', rename: { stripBase: 1 } },
      ],
    }),
  ],

  build: {
    // Inline small assets (< 4 KB) directly into CSS/JS
    assetsInlineLimit: 4096,

    rolldownOptions: {
      output: {
        // Split large vendor libraries into separate chunks so browsers
        // cache them independently from app code changes
        codeSplitting: {
          groups: [
            { name: 'vendor-chart', test: /node_modules[\\/](chart\.js|@kurkle)[\\/]/ },
            { name: 'vendor-alpine', test: /node_modules[\\/]alpinejs[\\/]/ },
          ],
        },
      },
    },

    cssCodeSplit: true,
    sourcemap: false,
  },
})
