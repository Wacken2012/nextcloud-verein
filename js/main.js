import { createApp } from 'vue'
import App from './components/App.vue'

// Nextcloud Theme Integration
import './theme.scss'

// Register Chart.js 'Filler' plugin to avoid runtime warnings when using 'fill'
import { Chart, Filler } from 'chart.js'
Chart.register(Filler)

createApp(App)
  .mount('#app')

