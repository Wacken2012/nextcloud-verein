<template>
  <div class="settings-page">
    <h2>Einstellungen</h2>

    <div class="settings-grid">
      <div class="card">
        <h3>🔐 Rollen & Berechtigungen</h3>
        <p>Verwalte Rollen und die zugehörigen Berechtigungen.</p>
        <button @click="showComponent('RolesManager')" class="button">Zu Rollen-Manager</button>
      </div>

      <div class="card">
        <h3>📧 Automatische Mahnungen</h3>
        <p>Konfiguriere automatische Mahnungen für fällige Zahlungen.</p>
        <button @click="showComponent('ReminderSettings')" class="button">Zu Mahnungen</button>
      </div>

      <div class="card">
        <h3>📋 Mahnung-Protokoll</h3>
        <p>Übersicht aller versendeten Mahnungen und deren Status.</p>
        <button @click="showComponent('ReminderLog')" class="button">Zum Protokoll</button>
      </div>

      <div class="card">
        <h3>🔒 Datenschutz & DSGVO</h3>
        <p>Verwalte Datenexport, Löschung und Einwilligungen.</p>
        <button @click="showComponent('PrivacySettings')" class="button">Zu Datenschutz</button>
      </div>

      <div class="card">
        <h3>💾 SEPA / Exporte</h3>
        <p>Export-Optionen verwalten und SEPA-Export erstellen.</p>
        <button @click="navigate('sepa')" class="button">Zu SEPA</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Settings',
  methods: {
    showComponent(componentName) {
      this.$emit('show-component', componentName);
    },
    navigate(tab) {
      this.$emit('navigate', tab);
    }
  }
}
</script>

<style scoped>
.settings-page { 
  padding: 20px;
}

.settings-grid { 
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
  margin-top: 20px;
}

.card { 
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: box-shadow 0.2s;
}

.card:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.card h3 {
  margin: 0 0 10px 0;
  color: #0082c9;
  font-size: 1.1em;
}

.card p {
  color: var(--color-text-maxcontrast);
  margin: 0 0 15px 0;
  font-size: 0.9em;
  line-height: 1.4;
}

.button {
  display: inline-block;
  padding: 8px 16px;
  background: #0082c9;
  color: white;
  border-radius: 4px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
  margin-top: 12px;
}

.button:hover {
  background: #006aa3;
}
</style>
