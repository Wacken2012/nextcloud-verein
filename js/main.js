import { createApp } from 'vue'
import App from './components/App.vue'

// Nextcloud Theme Integration
import './theme.scss'

// Chart.js is now lazy-loaded with Statistics component for better initial load

createApp(App)
  .mount('#app')

