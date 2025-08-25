import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import { resolve, dirname } from 'path'
import { fileURLToPath } from 'url'
import { viteStaticCopy } from 'vite-plugin-static-copy'

const __dirname = dirname(fileURLToPath(import.meta.url));
const VITE_NEOS_ROOT = './'
const currentDir = dirname(fileURLToPath(import.meta.url));
const rootPath = resolve(currentDir, VITE_NEOS_ROOT);

const VITE_ENTRYPOINTS = [
    resolve(__dirname, 'assets/main.ts'),
    resolve(__dirname, 'assets/Styles/debug.pcss'),
];


export default defineConfig({
  //root: './',
  plugins: [
    tailwindcss(),
    viteStaticCopy({
      targets: [
        {
          src: resolve(rootPath, 'assets/images'),
          dest: 'Images'
        }
      ],
    }),
  ],

  server: {
    host: '0.0.0.0',
    port: 8989,
  },

  build: {
    manifest: true,
    outDir: './DistributionPackages/Custom.Template/Resources/Public',
    assetsDir: 'Public/assets',
    emptyOutDir: true,
    sourcemap: true,
    rollupOptions: {
      //input: resolve(__dirname, 'assets/main.ts'),
      input: VITE_ENTRYPOINTS.map((entry) => resolve(rootPath, entry)),
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].css',
      }
    }
  }
})
