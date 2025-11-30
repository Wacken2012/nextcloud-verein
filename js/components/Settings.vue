<template>
  <div class="settings-page">
    <h2>Einstellungen</h2>

    <div class="settings-grid">
      <div class="card">
        <h3>Rollen & Berechtigungen</h3>
        <p>Verwalte Rollen und die zugehörigen Berechtigungen.</p>
        <router-link to="/roles" class="button">Zu Rollen</router-link>
      </div>

      <div class="card">
        <h3>SEPA / Exporte</h3>
        <p>Export-Optionen verwalten und SEPA-Export erstellen.</p>
        <router-link to="/sepa" class="button">Zu SEPA</router-link>
      </div>

      <div class="card">
        <h3>Diagramme</h3>
        <p>Aktiviere oder deaktiviere die Statistik-Diagramme auf dem Dashboard.</p>
        <label class="toggle-row">
          <input type="checkbox" :checked="settings.enable_charts" @change="onToggleCharts($event)" />
          <span>Diagramme aktivieren</span>
        </label>
        <p v-if="saving" class="hint">Speichern...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { settingsStore as settings } from '../store/settings'

const saving = ref(false)

const onToggleCharts = async (e) => {
  saving.value = true
  await settings.setChartsEnabled(e.target.checked)
  saving.value = false
}

onMounted(() => {
  if (!settings.loaded) {
    settings.load().catch(() => {/* ignore errors, keep defaults */})
  }
})
</script>

<style scoped>
.settings-page { padding: 20px }
.settings-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap:16px }
.card { background: var(--color-main-background); border:1px solid var(--color-border); padding:16px; border-radius:8px }
.toggle-row { display:flex; align-items:center; gap:8px; margin-top:8px }
.hint { font-size: var(--font-size-sm); color: var(--color-text-secondary); margin-top:4px }
.button { margin-top:12px; display:inline-block }
</style>
