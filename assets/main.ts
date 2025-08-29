import './style.pcss'

import Alpine from 'alpinejs'
import DarkMode from './JavaScript/DarkMode'
import DialRotation from './JavaScript/DialRotation'

declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

const initializeComponents = () => {
  // call all components here!!!
  DarkMode()
  DialRotation()
  
}

document.addEventListener('DOMContentLoaded', () => {
  window.Alpine = Alpine
  initializeComponents()
  Alpine.start()
})
