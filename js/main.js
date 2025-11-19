import { createApp } from 'vue'
import App from './components/App.vue'

// Polyfill for older browsers: ensure MediaQueryList supports addEventListener/removeEventListener
if (typeof window !== 'undefined' && typeof window.matchMedia === 'function') {
  try {
    const mql = window.matchMedia('(min-width: 0px)')
    if (mql && typeof mql.addEventListener !== 'function' && typeof mql.addListener === 'function') {
      const proto = Object.getPrototypeOf(mql)
      if (proto) {
        proto.addEventListener = function (type, listener) {
          this.addListener(listener)
        }
        proto.removeEventListener = function (type, listener) {
          this.removeListener(listener)
        }
      }
    }
  } catch (e) {
    // ignore: matchMedia not available or unexpected environment
  }
}

// Nextcloud Theme Integration
import './theme.scss'

// Register Chart.js 'Filler' plugin to avoid runtime warnings when using 'fill'
import { Chart, Filler } from 'chart.js'
Chart.register(Filler)

createApp(App)
  .mount('#app')

