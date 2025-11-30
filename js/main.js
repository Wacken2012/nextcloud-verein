import { createApp } from 'vue'
import App from './components/App.vue'
import { settingsStore } from './store/settings'

// Nextcloud Theme Integration
import './theme.scss'

// Mount app immediately; settings load lazily in Settings view
createApp(App).mount('#app')

