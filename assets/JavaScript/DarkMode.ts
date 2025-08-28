
import Alpine from 'alpinejs'

const DarkMode = () => {
  Alpine.data('darkMode', () => ({
    darkmode: localStorage.getItem('theme') === 'dark',
    bodyClass: '',
    init() {
      this.addMacOSClass()
      this.updateBodyClass()

      this.$watch('darkmode', (value: boolean) => {
        this.updateTheme(value)
      })
    },

    addMacOSClass() {
      if (navigator.userAgent.includes('Macintosh')) {
        document.documentElement.classList.add('os-macos');
      }
    },

    updateBodyClass() {
      this.bodyClass = this.darkmode ? 'dark' : ''
      document.documentElement.classList.toggle('dark', this.darkmode)
    },

    updateTheme(value: boolean) {
      this.bodyClass = value ? 'dark' : ''
      localStorage.setItem('theme', value ? 'dark' : 'light')
      document.documentElement.classList.toggle('dark', value)
    },

    toggleDarkmode() {
      this.darkmode = !this.darkmode
      console.log('toggle...');
      
    },
  }))
}

export default DarkMode
