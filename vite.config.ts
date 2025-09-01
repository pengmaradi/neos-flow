import { defineConfig, UserConfig } from 'vite'
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
]

export default defineConfig((config: UserConfig) => {

  return {
     base: '/',
     plugins: [
       tailwindcss(),
       viteStaticCopy({
         targets: [
           {
             src: resolve(rootPath, 'assets/Images'),
             dest: '',
           }
         ],
         watch: {
           reloadPageOnChange: false
         },
         silent: false,
         structured: false,
       }),
     ],
 
     server: {
       host: '0.0.0.0',
       port: 8989,
       origin: 'http://localhost:8989',
       open: false,
       hmr: {
        host: 'localhost'
       }
     },
 
     build: {
       manifest: true,
       outDir: 'DistributionPackages/Custom.Template/Resources/Public',
       emptyOutDir: true,
       
       watch: (config.mode == 'development') ? {
         exclude: [
           'bin/**',
           'Build/**',
           'Configuration/**',
           'Data/**',
           'Packages/**',
           'Web/**',
           'DistributionPackages/**/Resources/Public'
         ]
       } : null,

       sourcemap: true,
       rollupOptions: {
         input: VITE_ENTRYPOINTS.map((entry) => resolve(rootPath, entry)),
 
         output: {
           entryFileNames: '[name].js',
           chunkFileNames: '[name].js',
           assetFileNames: '[name].css',
         }
       },
     }
 }
})
