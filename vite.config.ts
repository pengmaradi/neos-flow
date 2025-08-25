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


export default defineConfig(({command, mode}) => {
  const plugins = [tailwindcss(),]
  if (command === 'build' && mode === 'production') {
    plugins.push(
      viteStaticCopy({
        targets: [
          {
            src: resolve(rootPath, 'assets/Images'),
            dest: '',
            // overwrite: false
          }
        ],
        // watch: {
        //   reloadPageOnChange: false
        // },
        // silent: false,
        // structured: false,
      }),
    )
  }

  return {
  base: '/',
  plugins: plugins,

  server: {
    host: '0.0.0.0',
    port: 8989,
    origin: 'http://localhost:8989',
    open: false,
  },

  build: {
    manifest: true,
    outDir: 'DistributionPackages/Custom.Template/Resources/Public',
    emptyOutDir: true,
    sourcemap: true,
    rollupOptions: {
      input: VITE_ENTRYPOINTS.map((entry) => resolve(rootPath, entry)),

      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name].js',
        assetFileNames: '[name].css',
      }
    },

    watch: {
      skipWrite: true,
      exclude: ['node_modules', 'Packages', 'Data', 'Web', 'Public', 'Confuguration'],
      buildDelay: 100,

    }
  }
}})
