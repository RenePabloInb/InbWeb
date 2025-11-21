// astro.config.mjs
import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';
import compress from 'astro-compress';

export default defineConfig({
  output: 'static',
  base: '/', // Mantener igual
  server: { port: 4321, host: true },
  build: {
    format: 'file',
    inlineStylesheets: 'auto',
    assets: '_assets',
  },
  integrations: [
    tailwind({
      applyBaseStyles: true,
    }),
    // Compress solo en producción (hace el build más rápido)
    compress({
      CSS: true,
      HTML: {
        removeAttributeQuotes: false,
        removeComments: true,
      },
      Image: false, // No comprimir imágenes en build (ya están optimizadas)
      JavaScript: true,
      SVG: true,
    }),
  ],
  vite: {
    server: {
      // Proxy para XAMPP
      proxy: {
        '/api': 'http://localhost/inbolsa-api/api',
      },
    },
    build: {
      target: ['es2020', 'edge88', 'firefox79', 'chrome87', 'safari14'],
      cssMinify: true,
      outDir: 'dist',
      // Optimizaciones para acelerar el build
      chunkSizeWarningLimit: 1000,
      rollupOptions: {
        input: {
          main: 'src/pages/**/*.astro',
          lib: 'src/lib/**/*.ts',
        },
        output: {
          // Mejor manejo de chunks
          manualChunks: undefined,
        },
      },
    },
    // Optimizar el manejo de assets estáticos
    assetsInclude: ['**/*.webp', '**/*.jpg', '**/*.png'],
  },
})
