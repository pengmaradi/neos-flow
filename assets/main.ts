import './style.pcss'

import Alpine from 'alpinejs'

declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

const initializeComponents = () => {
  // call all components here!!!
  console.log(`start components 😂...`);
  
}

document.addEventListener('DOMContentLoaded', () => {
  window.Alpine = Alpine
  initializeComponents()
  Alpine.start()
})
