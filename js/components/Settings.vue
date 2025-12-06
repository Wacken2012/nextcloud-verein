<template>
  <div class="settings-page">
    <h2>Einstellungen</h2>

    <!-- Vereinsdaten Section -->
    <div class="settings-section">
      <h3>🏢 Vereinsdaten</h3>
      <p class="section-description">Diese Daten werden in E-Mails, der Datenschutzerklärung und Dokumenten verwendet.</p>
      
      <div class="form-grid">
        <div class="form-group">
          <label for="club_name">Vereinsname *</label>
          <input 
            type="text" 
            id="club_name" 
            v-model="clubData.name" 
            placeholder="Mein Verein e.V."
            @change="saveClubData"
          />
        </div>
        
        <div class="form-group">
          <label for="club_email">E-Mail-Adresse *</label>
          <input 
            type="email" 
            id="club_email" 
            v-model="clubData.email" 
            placeholder="vorstand@verein.de"
            @change="saveClubData"
          />
        </div>
        
        <div class="form-group full-width">
          <label for="club_address">Adresse</label>
          <textarea 
            id="club_address" 
            v-model="clubData.address" 
            rows="3"
            placeholder="Musterstraße 1&#10;12345 Musterstadt"
            @change="saveClubData"
          ></textarea>
        </div>
      </div>
      
      <div v-if="saveMessage" :class="['save-message', saveMessage.type]">
        {{ saveMessage.text }}
      </div>
    </div>

    <!-- Settings Cards Grid -->
    <div class="settings-grid">
      <div class="card">
        <h3>🔐 Rollen & Berechtigungen</h3>
        <p>Verwalte Rollen und die zugehörigen Berechtigungen.</p>
        <button @click="showComponent('RolesManager')" class="button">Zu Rollen-Manager</button>
      </div>

      <div class="card">
        <h3>📧 E-Mail-Template</h3>
        <p>Passen Sie Logo, Briefkopf und Texte Ihrer Mahnungs-E-Mails an.</p>
        <button @click="showComponent('EmailTemplateEditor')" class="button">Zum Template-Editor</button>
      </div>

      <div class="card">
        <h3>⚙️ Automatische Mahnungen</h3>
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
        <button @click="navigate('finance')" class="button">Zu SEPA</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'Settings',
  emits: ['show-component', 'navigate'],
  data() {
    return {
      clubData: {
        name: '',
        email: '',
        address: ''
      },
      saveMessage: null,
      loading: false
    }
  },
  async mounted() {
    await this.loadClubData()
  },
  methods: {
    showComponent(componentName) {
      this.$emit('show-component', componentName);
    },
    navigate(tab) {
      this.$emit('navigate', tab);
    },
    async loadClubData() {
      try {
        const response = await axios.get(generateUrl('/apps/verein/api/v1/email-template/settings'))
        if (response.data?.settings) {
          this.clubData.name = response.data.settings.clubName || ''
          this.clubData.email = response.data.settings.clubEmail || ''
          this.clubData.address = response.data.settings.clubAddress || ''
        }
      } catch (error) {
        console.error('Error loading club data:', error)
      }
    },
    async saveClubData() {
      try {
        this.loading = true
        await axios.post(generateUrl('/apps/verein/api/v1/email-template/settings'), {
          club_name: this.clubData.name,
          club_email: this.clubData.email,
          club_address: this.clubData.address
        })
        this.showSaveMessage('Vereinsdaten gespeichert', 'success')
      } catch (error) {
        console.error('Error saving club data:', error)
        this.showSaveMessage('Fehler beim Speichern', 'error')
      } finally {
        this.loading = false
      }
    },
    showSaveMessage(text, type) {
      this.saveMessage = { text, type }
      setTimeout(() => {
        this.saveMessage = null
      }, 3000)
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

/* Vereinsdaten Section */
.settings-section {
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 24px;
}

.settings-section h3 {
  margin: 0 0 8px 0;
  color: var(--color-main-text);
  font-size: 1.2em;
}

.section-description {
  color: var(--color-text-maxcontrast);
  margin: 0 0 16px 0;
  font-size: 0.9em;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 500;
  margin-bottom: 6px;
  color: var(--color-main-text);
  font-size: 0.9em;
}

.form-group input,
.form-group textarea {
  padding: 10px 12px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.95em;
  background: var(--color-main-background);
  color: var(--color-main-text);
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #0082c9;
}

.form-group textarea {
  resize: vertical;
  min-height: 80px;
}

.save-message {
  margin-top: 12px;
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 0.9em;
}

.save-message.success {
  background: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
}

.save-message.error {
  background: #ffebee;
  color: #c62828;
  border: 1px solid #ffcdd2;
}

@media (max-width: 600px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
