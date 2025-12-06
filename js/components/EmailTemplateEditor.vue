<template>
  <div class="email-template-editor">
    <h2>📧 E-Mail-Template-Editor</h2>
    <p class="description">Passen Sie das Design und den Inhalt Ihrer Mahnungs-E-Mails an.</p>

    <div v-if="loading" class="loading">
      <span class="icon-loading"></span> Lade Einstellungen...
    </div>

    <div v-else class="editor-content">
      <!-- Tab Navigation -->
      <div class="tab-navigation">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['tab-button', { active: activeTab === tab.id }]"
          @click="activeTab = tab.id"
        >
          {{ tab.icon }} {{ tab.label }}
        </button>
      </div>

      <!-- Vereins-Branding Tab -->
      <div v-show="activeTab === 'branding'" class="tab-content">
        <div class="form-group">
          <label for="club_name">Vereinsname</label>
          <input type="text" id="club_name" v-model="settings.club_name" placeholder="Mein Verein e.V." />
        </div>

        <div class="form-group">
          <label for="club_logo_url">Logo-URL</label>
          <input type="url" id="club_logo_url" v-model="settings.club_logo_url" placeholder="https://..." />
          <small>Direkter Link zu Ihrem Logo (PNG, JPG, max. 200px breit)</small>
        </div>

        <div class="form-group">
          <label for="club_address">Adresse</label>
          <textarea id="club_address" v-model="settings.club_address" rows="3" placeholder="Musterstraße 1&#10;12345 Musterstadt"></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="club_email">E-Mail</label>
            <input type="email" id="club_email" v-model="settings.club_email" placeholder="vorstand@verein.de" />
          </div>
          <div class="form-group">
            <label for="club_phone">Telefon</label>
            <input type="tel" id="club_phone" v-model="settings.club_phone" placeholder="+49 123 456789" />
          </div>
        </div>

        <div class="form-group">
          <label for="club_website">Website</label>
          <input type="url" id="club_website" v-model="settings.club_website" placeholder="https://www.verein.de" />
        </div>
      </div>

      <!-- Design Tab -->
      <div v-show="activeTab === 'design'" class="tab-content">
        <div class="form-row">
          <div class="form-group">
            <label for="primary_color">Primärfarbe</label>
            <div class="color-input">
              <input type="color" id="primary_color" v-model="settings.primary_color" />
              <input type="text" v-model="settings.primary_color" class="color-text" />
            </div>
          </div>
          <div class="form-group">
            <label for="secondary_color">Sekundärfarbe</label>
            <div class="color-input">
              <input type="color" id="secondary_color" v-model="settings.secondary_color" />
              <input type="text" v-model="settings.secondary_color" class="color-text" />
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="header_text">Briefkopf-Text (optional)</label>
          <textarea id="header_text" v-model="settings.header_text" rows="2" placeholder="Zusätzlicher Text im Briefkopf"></textarea>
        </div>

        <div class="form-group">
          <label for="footer_text">Fußzeilen-Text</label>
          <textarea id="footer_text" v-model="settings.footer_text" rows="3" placeholder="Text am Ende der E-Mail"></textarea>
        </div>
      </div>

      <!-- Bankdaten Tab -->
      <div v-show="activeTab === 'bank'" class="tab-content">
        <div class="form-group">
          <label for="bank_name">Bank</label>
          <input type="text" id="bank_name" v-model="settings.bank_name" placeholder="Sparkasse Musterstadt" />
        </div>

        <div class="form-group">
          <label for="bank_account_holder">Kontoinhaber</label>
          <input type="text" id="bank_account_holder" v-model="settings.bank_account_holder" placeholder="Mein Verein e.V." />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="bank_iban">IBAN</label>
            <input type="text" id="bank_iban" v-model="settings.bank_iban" placeholder="DE89 3704 0044 0532 0130 00" />
          </div>
          <div class="form-group">
            <label for="bank_bic">BIC</label>
            <input type="text" id="bank_bic" v-model="settings.bank_bic" placeholder="COBADEFFXXX" />
          </div>
        </div>
      </div>

      <!-- Mahntexte Tab -->
      <div v-show="activeTab === 'texts'" class="tab-content">
        <div class="reminder-level-selector">
          <button 
            v-for="level in [1, 2, 3]" 
            :key="level"
            :class="['level-button', `level-${level}`, { active: selectedLevel === level }]"
            @click="selectedLevel = level"
          >
            {{ level }}. Mahnung
          </button>
        </div>

        <div class="form-group">
          <label :for="`reminder_subject_${selectedLevel}`">Betreff</label>
          <input 
            type="text" 
            :id="`reminder_subject_${selectedLevel}`" 
            v-model="settings[`reminder_subject_${selectedLevel}`]" 
          />
        </div>

        <div class="form-group">
          <label :for="`reminder_intro_${selectedLevel}`">Einleitungstext</label>
          <textarea 
            :id="`reminder_intro_${selectedLevel}`" 
            v-model="settings[`reminder_intro_${selectedLevel}`]" 
            rows="3"
          ></textarea>
          <small>Dieser Text erscheint nach der Anrede</small>
        </div>

        <div class="form-group">
          <label :for="`reminder_closing_${selectedLevel}`">Abschlusstext</label>
          <textarea 
            :id="`reminder_closing_${selectedLevel}`" 
            v-model="settings[`reminder_closing_${selectedLevel}`]" 
            rows="3"
          ></textarea>
          <small>Dieser Text erscheint vor der Signatur</small>
        </div>
      </div>

      <!-- Signatur Tab -->
      <div v-show="activeTab === 'signature'" class="tab-content">
        <div class="form-group">
          <label for="signature_name">Name/Grußformel</label>
          <input type="text" id="signature_name" v-model="settings.signature_name" placeholder="Der Vereinsvorstand" />
        </div>

        <div class="form-group">
          <label for="signature_title">Titel/Position (optional)</label>
          <input type="text" id="signature_title" v-model="settings.signature_title" placeholder="1. Vorsitzender" />
        </div>
      </div>

      <!-- Aktionsleiste -->
      <div class="action-bar">
        <button class="button primary" @click="saveSettings" :disabled="saving">
          <span v-if="saving" class="icon-loading-small"></span>
          {{ saving ? 'Speichern...' : '💾 Speichern' }}
        </button>
        <button class="button" @click="showPreview">
          👁️ Vorschau
        </button>
        <button class="button" @click="sendTestEmail">
          📤 Test-E-Mail senden
        </button>
      </div>

      <!-- Erfolgsmeldung -->
      <div v-if="message" :class="['message', messageType]">
        {{ message }}
      </div>
    </div>

    <!-- Vorschau-Modal -->
    <div v-if="previewVisible" class="preview-modal" @click.self="previewVisible = false">
      <div class="preview-container">
        <div class="preview-header">
          <h3>E-Mail-Vorschau</h3>
          <div class="preview-level-selector">
            <button 
              v-for="level in [1, 2, 3]" 
              :key="level"
              :class="['level-button', `level-${level}`, { active: previewLevel === level }]"
              @click="loadPreview(level)"
            >
              {{ level }}. Mahnung
            </button>
          </div>
          <button class="close-button" @click="previewVisible = false">✕</button>
        </div>
        <div class="preview-subject">
          <strong>Betreff:</strong> {{ previewSubject }}
        </div>
        <div class="preview-frame" v-html="previewHtml"></div>
      </div>
    </div>

    <!-- Test-E-Mail Modal -->
    <div v-if="testEmailVisible" class="preview-modal" @click.self="testEmailVisible = false">
      <div class="test-email-container">
        <h3>Test-E-Mail senden</h3>
        <div class="form-group">
          <label for="test_email">E-Mail-Adresse</label>
          <input type="email" id="test_email" v-model="testEmail" placeholder="ihre@email.de" />
        </div>
        <div class="form-group">
          <label>Mahnstufe</label>
          <div class="reminder-level-selector">
            <button 
              v-for="level in [1, 2, 3]" 
              :key="level"
              :class="['level-button', `level-${level}`, { active: testEmailLevel === level }]"
              @click="testEmailLevel = level"
            >
              {{ level }}. Mahnung
            </button>
          </div>
        </div>
        <div class="action-bar">
          <button class="button primary" @click="confirmSendTestEmail" :disabled="sendingTest">
            {{ sendingTest ? 'Senden...' : 'Senden' }}
          </button>
          <button class="button" @click="testEmailVisible = false">Abbrechen</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '@nextcloud/axios';
import { generateUrl } from '@nextcloud/router';

export default {
  name: 'EmailTemplateEditor',
  data() {
    return {
      loading: true,
      saving: false,
      sendingTest: false,
      settings: {},
      activeTab: 'branding',
      selectedLevel: 1,
      message: '',
      messageType: 'success',
      previewVisible: false,
      previewHtml: '',
      previewSubject: '',
      previewLevel: 1,
      testEmailVisible: false,
      testEmail: '',
      testEmailLevel: 1,
      tabs: [
        { id: 'branding', label: 'Vereinsdaten', icon: '🏢' },
        { id: 'design', label: 'Design', icon: '🎨' },
        { id: 'bank', label: 'Bankdaten', icon: '🏦' },
        { id: 'texts', label: 'Mahntexte', icon: '📝' },
        { id: 'signature', label: 'Signatur', icon: '✍️' },
      ],
    };
  },
  mounted() {
    this.loadSettings();
  },
  methods: {
    async loadSettings() {
      this.loading = true;
      try {
        const response = await axios.get(generateUrl('/apps/verein/api/v1/email-template/settings'));
        if (response.data.status === 'success') {
          this.settings = response.data.data;
        }
      } catch (error) {
        console.error('Error loading settings:', error);
        this.showMessage('Fehler beim Laden der Einstellungen', 'error');
      } finally {
        this.loading = false;
      }
    },
    async saveSettings() {
      this.saving = true;
      try {
        const response = await axios.post(
          generateUrl('/apps/verein/api/v1/email-template/settings'),
          this.settings
        );
        if (response.data.status === 'success') {
          this.showMessage('Einstellungen wurden gespeichert', 'success');
        } else {
          this.showMessage(response.data.message || 'Fehler beim Speichern', 'error');
        }
      } catch (error) {
        console.error('Error saving settings:', error);
        this.showMessage('Fehler beim Speichern der Einstellungen', 'error');
      } finally {
        this.saving = false;
      }
    },
    async showPreview() {
      this.previewLevel = this.selectedLevel;
      await this.loadPreview(this.selectedLevel);
      this.previewVisible = true;
    },
    async loadPreview(level) {
      this.previewLevel = level;
      try {
        // Erst speichern, dann Vorschau laden
        await this.saveSettings();
        const response = await axios.get(
          generateUrl('/apps/verein/api/v1/email-template/preview'),
          { params: { level } }
        );
        if (response.data.status === 'success') {
          this.previewHtml = response.data.data.html;
          this.previewSubject = response.data.data.subject;
        }
      } catch (error) {
        console.error('Error loading preview:', error);
        this.showMessage('Fehler beim Laden der Vorschau', 'error');
      }
    },
    sendTestEmail() {
      this.testEmailLevel = this.selectedLevel;
      this.testEmailVisible = true;
    },
    async confirmSendTestEmail() {
      if (!this.testEmail || !this.testEmail.includes('@')) {
        this.showMessage('Bitte geben Sie eine gültige E-Mail-Adresse ein', 'error');
        return;
      }
      this.sendingTest = true;
      try {
        const response = await axios.post(
          generateUrl('/apps/verein/api/v1/email-template/test'),
          { email: this.testEmail, level: this.testEmailLevel }
        );
        if (response.data.status === 'success') {
          this.showMessage(response.data.message, 'success');
          this.testEmailVisible = false;
        } else {
          this.showMessage(response.data.message || 'Fehler beim Senden', 'error');
        }
      } catch (error) {
        console.error('Error sending test email:', error);
        this.showMessage('Fehler beim Senden der Test-E-Mail', 'error');
      } finally {
        this.sendingTest = false;
      }
    },
    showMessage(text, type = 'success') {
      this.message = text;
      this.messageType = type;
      setTimeout(() => {
        this.message = '';
      }, 5000);
    },
  },
};
</script>

<style scoped>
.email-template-editor {
  max-width: 800px;
  padding: 20px;
}

h2 {
  margin-bottom: 5px;
}

.description {
  color: var(--color-text-lighter);
  margin-bottom: 20px;
}

.loading {
  text-align: center;
  padding: 40px;
}

.tab-navigation {
  display: flex;
  gap: 5px;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--color-border);
  padding-bottom: 10px;
  flex-wrap: wrap;
}

.tab-button {
  padding: 8px 16px;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 4px;
  transition: background 0.2s;
}

.tab-button:hover {
  background: var(--color-background-hover);
}

.tab-button.active {
  background: var(--color-primary);
  color: white;
}

.tab-content {
  background: var(--color-background-dark);
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 4px;
  font-weight: 500;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  font-size: 14px;
}

.form-group small {
  display: block;
  margin-top: 4px;
  color: var(--color-text-lighter);
  font-size: 12px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.color-input {
  display: flex;
  gap: 8px;
  align-items: center;
}

.color-input input[type="color"] {
  width: 50px;
  height: 36px;
  padding: 2px;
  cursor: pointer;
}

.color-input .color-text {
  width: 100px;
}

.reminder-level-selector {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
}

.level-button {
  padding: 8px 16px;
  border: 2px solid;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s;
}

.level-button.level-1 {
  border-color: #0082c9;
  color: #0082c9;
  background: transparent;
}
.level-button.level-1.active,
.level-button.level-1:hover {
  background: #e8f4f8;
}

.level-button.level-2 {
  border-color: #856404;
  color: #856404;
  background: transparent;
}
.level-button.level-2.active,
.level-button.level-2:hover {
  background: #fff3cd;
}

.level-button.level-3 {
  border-color: #721c24;
  color: #721c24;
  background: transparent;
}
.level-button.level-3.active,
.level-button.level-3:hover {
  background: #f8d7da;
}

.action-bar {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.button {
  padding: 10px 20px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  background: var(--color-main-background);
}

.button.primary {
  background: var(--color-primary);
  color: white;
  border-color: var(--color-primary);
}

.button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  margin-top: 16px;
  padding: 12px 16px;
  border-radius: 4px;
}

.message.success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.message.error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Modal Styles */
.preview-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
}

.preview-container {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.preview-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  border-bottom: 1px solid var(--color-border);
  gap: 16px;
}

.preview-header h3 {
  margin: 0;
}

.preview-level-selector {
  display: flex;
  gap: 8px;
}

.close-button {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  padding: 4px 8px;
}

.preview-subject {
  padding: 12px 16px;
  background: var(--color-background-dark);
  border-bottom: 1px solid var(--color-border);
}

.preview-frame {
  flex: 1;
  overflow: auto;
  padding: 0;
}

.preview-frame :deep(body) {
  margin: 0;
}

.test-email-container {
  background: white;
  border-radius: 8px;
  padding: 24px;
  width: 90%;
  max-width: 400px;
}

.test-email-container h3 {
  margin-top: 0;
}

@media (max-width: 600px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .tab-navigation {
    overflow-x: auto;
  }
}
</style>
