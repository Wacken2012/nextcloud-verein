import { reactive } from 'vue'
import api from '../api'

export const settingsStore = reactive({
  loaded: false,
  enable_charts: false,
  async load() {
    try {
      const resp = await api.getAppSettings()
      if (resp.data.status === 'ok') {
        this.enable_charts = !!resp.data.data.enable_charts
        this.loaded = true
      }
    } catch (e) {
      console.error('Failed to load app settings', e)
      this.loaded = true
    }
  },
  async setChartsEnabled(flag) {
    try {
      const resp = await api.setChartsEnabled(flag)
      if (resp.data.status === 'ok') {
        this.enable_charts = !!resp.data.data.enable_charts
      }
    } catch (e) {
      console.error('Failed to update charts flag', e)
    }
  }
})
