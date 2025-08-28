import './style.pcss'

import Alpine from 'alpinejs'
import DarkMode from './JavaScript/DarkMode'

declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

const initializeComponents = () => {
  // call all components here!!!
  console.log(`start components 😂...`)
  DarkMode()
  
}

document.addEventListener('DOMContentLoaded', () => {
  window.Alpine = Alpine
  initializeComponents()
  Alpine.start()
})
