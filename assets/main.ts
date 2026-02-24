import './style.pcss'

import Alpine from 'alpinejs'
import DarkMode from './JavaScript/DarkMode'
import DialRotation from './JavaScript/DialRotation'
import MainMenu from './JavaScript/MainMenu'

declare global {
  interface Window {
    Alpine: typeof Alpine;
  }
}

const initializeComponents = () => {
  // call all components here!!!
  DarkMode()
  MainMenu()
  DialRotation()
  
}

document.addEventListener('DOMContentLoaded', () => {
  window.Alpine = Alpine
  initializeComponents()
  Alpine.start()
})
