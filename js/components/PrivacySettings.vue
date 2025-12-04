<template>
  <div class="privacy-settings">
    <div class="privacy-header">
      <button @click="goBack" class="back-btn" title="Zurück zu Einstellungen">← Zurück</button>
      <h3>{{ $t('privacy.title', 'Datenschutzerklärung & DSGVO') }}</h3>
      <p class="description">
        {{ $t('privacy.description', 'Verwalten Sie Ihre persönlichen Daten gemäß DSGVO') }}
      </p>
    </div>

    <!-- Tabs -->
    <div class="privacy-tabs">
      <button 
        :class="['tab', { active: activeTab === 'data' }]"
        @click="activeTab = 'data'"
      >
        {{ $t('privacy.tabs.data', 'Meine Daten') }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'consents' }]"
        @click="activeTab = 'consents'"
      >
        {{ $t('privacy.tabs.consents', 'Einwilligungen') }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'policy' }]"
        @click="activeTab = 'policy'"
      >
        {{ $t('privacy.tabs.policy', 'Datenschutzerklärung') }}
      </button>
    </div>

    <!-- Meine Daten Tab -->
    <div v-show="activeTab === 'data'" class="tab-content data-tab">
      <div class="data-actions">
        <div class="action-group">
          <h4>{{ $t('privacy.data.manage', 'Meine Daten verwalten') }}</h4>
          <p class="info">
            {{ $t('privacy.data.gdprRight', 'Gemäß DSGVO Art. 15 haben Sie das Recht auf Auskunft über Ihre persönlichen Daten.') }}
          </p>

          <div class="actions">
            <button class="btn btn-primary" @click="exportData">
              📥 {{ $t('privacy.data.export', 'Daten exportieren (JSON)') }}
            </button>
          </div>
        </div>

        <div class="action-group danger-zone">
          <h4>{{ $t('privacy.data.delete', 'Daten löschen') }}</h4>
          <p class="info warning">
            {{ $t('privacy.data.deleteWarning', 'Gemäß DSGVO Art. 17 können Sie die Löschung Ihrer Daten anfordern.') }}
          </p>

          <div class="delete-options">
            <div class="delete-option">
              <label>
                <input 
                  v-model="deleteMode" 
                  type="radio" 
                  value="soft_delete"
                />
                <span class="label-text">
                  <strong>{{ $t('privacy.delete.soft', 'Anonymisieren') }}</strong>
                  <small>{{ $t('privacy.delete.softDesc', 'Persönliche Daten werden anonymisiert, Transaktionsverlauf bleibt für Buchhaltung.') }}</small>
                </span>
              </label>
            </div>

            <div class="delete-option">
              <label>
                <input 
                  v-model="deleteMode" 
                  type="radio" 
                  value="hard_delete"
                />
                <span class="label-text">
                  <strong>{{ $t('privacy.delete.hard', 'Komplettes Löschen') }}</strong>
                  <small>{{ $t('privacy.delete.hardDesc', 'Alle Daten werden gelöscht (nur wenn keine offenen Gebühren).') }}</small>
                </span>
              </label>
            </div>
          </div>

          <button 
            class="btn btn-danger" 
            @click="confirmDelete"
            :disabled="deleteInProgress"
          >
            {{ deleteInProgress ? '...' : '🗑️ ' + $t('privacy.delete.confirm', 'Daten löschen') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Einwilligungen Tab -->
    <div v-show="activeTab === 'consents'" class="tab-content consents-tab">
      <div class="consents-container">
        <h4>{{ $t('privacy.consents.title', 'Ihre Einwilligungen') }}</h4>
        <p class="info">
          {{ $t('privacy.consents.description', 'Sie können Ihre Einwilligungen jederzeit ändern.') }}
        </p>

        <div class="consent-items">
          <div v-for="consent in consentTypes" :key="consent.key" class="consent-item">
            <div class="consent-header">
              <label class="checkbox-label">
                <input 
                  type="checkbox"
                  :checked="getConsentValue(consent.key)"
                  @change="toggleConsent(consent.key, $event)"
                />
                <span class="consent-title">{{ consent.label }}</span>
              </label>
            </div>
            <p class="consent-description">{{ consent.description }}</p>
            <small v-if="getConsentDate(consent.key)" class="consent-date">
              {{ $t('privacy.consents.given', 'Gegeben am') }}: {{ formatDate(getConsentDate(consent.key)) }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <!-- Datenschutzerklärung Tab -->
    <div v-show="activeTab === 'policy'" class="tab-content policy-tab">
      <div class="policy-content" v-html="privacyPolicy"></div>
    </div>

    <!-- Erfolgs/Fehler Meldungen -->
    <div v-if="message" :class="['message', message.type]">
      <span>{{ message.text }}</span>
      <button @click="message = null" class="close-btn">✕</button>
    </div>
  </div>
</template>

<script>
import { api } from '@/api';
import { getCurrentUser } from '@nextcloud/auth';

export default {
  name: 'PrivacySettings',
  data() {
    return {
      activeTab: 'data',
      deleteMode: 'soft_delete',
      deleteInProgress: false,
      privacyPolicy: '',
      consents: {},
      message: null,
      currentUser: null,
      consentTypes: [
        {
          key: 'newsletter',
          label: 'Newsletter',
          description: 'Regelmäßige Updates über Vereinsaktivitäten',
        },
        {
          key: 'marketing',
          label: 'Marketing',
          description: 'Personalisierte Angebote und Kommunikation',
        },
        {
          key: 'analytics',
          label: 'Analytik',
          description: 'Nutzungsanalyse zur Verbesserung der App',
        },
        {
          key: 'partners',
          label: 'Partner',
          description: 'Weitergabe an Partner und Dienstleister',
        },
      ],
    };
  },

  mounted() {
    this.currentUser = getCurrentUser();
    this.loadPrivacyPolicy();
    this.loadConsents();
  },

  methods: {
    async loadPrivacyPolicy() {
      try {
        const response = await api.get('api/v1/privacy/policy');
        this.privacyPolicy = response.data?.policy || '<p>Keine Datenschutzerklärung vorhanden</p>';
      } catch (error) {
        console.error('Error loading privacy policy:', error);
      }
    },

    async loadConsents() {
      if (!this.currentUser?.uid) return;
      try {
        const response = await api.getConsentStatus(this.currentUser.uid);
        this.consents = response.data || {};
      } catch (error) {
        console.error('Error loading consents:', error);
      }
    },

    async exportData() {
      if (!this.currentUser?.uid) {
        this.showMessage('Fehler: Benutzer nicht identifiziert', 'error');
        return;
      }
      try {
        this.message = { text: 'Lädt...', type: 'info' };
        
        const response = await api.exportMemberData(this.currentUser.uid);
        
        // Create download link
        const blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `personal_data_${new Date().toISOString().split('T')[0]}.json`);
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);

        this.showMessage('Daten erfolgreich exportiert', 'success');
      } catch (error) {
        console.error('Error exporting data:', error);
        this.showMessage('Fehler beim Download: ' + (error.response?.data?.message || error.message), 'error');
      }
    },

    confirmDelete() {
      const message = this.deleteMode === 'soft_delete'
        ? 'Daten wirklich anonymisieren?'
        : 'Daten wirklich vollständig löschen? Dies kann nicht rückgängig gemacht werden!';

      if (!confirm(message)) return;

      if (this.deleteMode === 'hard_delete') {
        const confirmation = prompt('Bitte geben Sie "LÖSCHEN" ein um zu bestätigen:');
        if (confirmation !== 'LÖSCHEN') return;
      }

      this.deleteData();
    },

    async deleteData() {
      if (!this.currentUser?.uid) {
        this.showMessage('Fehler: Benutzer nicht identifiziert', 'error');
        return;
      }
      try {
        this.deleteInProgress = true;
        this.message = { text: 'Wird verarbeitet...', type: 'info' };

        await api.deleteMemberData(this.currentUser.uid, this.deleteMode);

        this.showMessage(
          this.deleteMode === 'soft_delete'
            ? 'Daten anonymisiert'
            : 'Daten gelöscht',
          'success'
        );

        // Redirect after delay
        setTimeout(() => {
          window.location.href = '/';
        }, 2000);
      } catch (error) {
        console.error('Error deleting data:', error);
        this.showMessage('Fehler beim Löschen: ' + (error.response?.data?.message || error.message), 'error');
      } finally {
        this.deleteInProgress = false;
      }
    },

    getConsentValue(key) {
      return this.consents[key]?.given ?? false;
    },

    getConsentDate(key) {
      return this.consents[key]?.timestamp;
    },

    async toggleConsent(key, event) {
      if (!this.currentUser?.uid) {
        this.showMessage('Fehler: Benutzer nicht identifiziert', 'error');
        return;
      }
      try {
        const given = event.target.checked;
        await api.saveConsent(this.currentUser.uid, key, given);

        this.loadConsents();
        this.showMessage(
          given
            ? 'Einwilligung gegeben'
            : 'Einwilligung widerrufen',
          'success'
        );
      } catch (error) {
        console.error('Error saving consent:', error);
        this.showMessage('Fehler beim Speichern: ' + (error.response?.data?.message || error.message), 'error');
        event.target.checked = !event.target.checked;
      }
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleString('de-DE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },

    showMessage(text, type = 'info') {
      this.message = { text, type };
      setTimeout(() => {
        this.message = null;
      }, 4000);
    },

    goBack() {
      this.$emit('show-component', 'Settings');
    },
  },
};
</script>

<style scoped lang="scss">
.privacy-settings {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  margin: 20px 0;

  .privacy-header {
    margin-bottom: 30px;
    position: relative;

    .back-btn {
      position: absolute;
      top: 0;
      left: 0;
      background: none;
      border: none;
      color: #0082c9;
      font-size: 1em;
      cursor: pointer;
      padding: 0;
      margin: 0;

      &:hover {
        color: #006aa3;
      }
    }

    h3 {
      font-size: 1.3em;
      font-weight: 600;
      color: #333;
      margin: 0 0 10px 0;
    }

    .description {
      color: #666;
      margin: 0;
      font-size: 0.95em;
    }
  }

  .privacy-tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #ddd;
    margin-bottom: 20px;

    .tab {
      padding: 12px 20px;
      border: none;
      background: none;
      color: #666;
      font-weight: 500;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      transition: all 0.2s;

      &:hover {
        color: #333;
      }

      &.active {
        color: #1e88e5;
        border-bottom-color: #1e88e5;
      }
    }
  }

  .tab-content {
    background: white;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .data-actions {
    .action-group {
      margin-bottom: 30px;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 6px;

      h4 {
        margin: 0 0 10px 0;
        color: #333;
        font-weight: 600;
      }

      .info {
        color: #666;
        font-size: 0.9em;
        margin: 0 0 15px 0;

        &.warning {
          color: #d97706;
          font-weight: 500;
        }
      }

      .actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
      }

      &.danger-zone {
        border-color: #ff6b6b;
        background: #fff5f5;

        h4 {
          color: #d32f2f;
        }
      }
    }
  }

  .delete-options {
    margin-bottom: 15px;

    .delete-option {
      margin-bottom: 15px;

      label {
        display: flex;
        align-items: flex-start;
        cursor: pointer;
        padding: 10px;
        border-radius: 4px;
        transition: background 0.2s;

        &:hover {
          background: rgba(0, 0, 0, 0.02);
        }

        input[type="radio"] {
          margin-right: 12px;
          margin-top: 2px;
          cursor: pointer;
        }

        .label-text {
          flex: 1;

          strong {
            display: block;
            color: #333;
            margin-bottom: 3px;
          }

          small {
            color: #999;
            display: block;
          }
        }
      }
    }
  }

  .consents-container {
    h4 {
      margin: 0 0 10px 0;
      color: #333;
      font-weight: 600;
    }

    .info {
      color: #666;
      font-size: 0.9em;
      margin: 0 0 20px 0;
    }
  }

  .consent-items {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .consent-item {
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    transition: border-color 0.2s;

    &:hover {
      border-color: #1e88e5;
    }

    .consent-header {
      margin-bottom: 10px;

      .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;

        input[type="checkbox"] {
          margin-right: 10px;
          cursor: pointer;
        }

        .consent-title {
          font-weight: 600;
          color: #333;
        }
      }
    }

    .consent-description {
      color: #666;
      margin: 0 0 8px 28px;
      font-size: 0.9em;
    }

    .consent-date {
      margin-left: 28px;
      color: #999;
      font-size: 0.85em;
    }
  }

  .policy-content {
    max-width: 900px;
    line-height: 1.6;
    color: #333;

    h1, h2, h3, h4, h5, h6 {
      margin-top: 20px;
      margin-bottom: 10px;
      color: #333;
      font-weight: 600;
    }

    p {
      margin-bottom: 12px;
    }

    ul, ol {
      margin-bottom: 12px;
      margin-left: 20px;

      li {
        margin-bottom: 6px;
      }
    }

    strong {
      color: #1e88e5;
      font-weight: 600;
    }
  }

  .message {
    margin-top: 20px;
    padding: 15px;
    border-radius: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideIn 0.3s;

    .close-btn {
      background: none;
      border: none;
      color: inherit;
      cursor: pointer;
      font-size: 1.2em;
      opacity: 0.7;

      &:hover {
        opacity: 1;
      }
    }

    &.success {
      background: #e8f5e9;
      color: #2e7d32;
      border-left: 4px solid #4caf50;
    }

    &.error {
      background: #ffebee;
      color: #c62828;
      border-left: 4px solid #f44336;
    }

    &.info {
      background: #e3f2fd;
      color: #1565c0;
      border-left: 4px solid #2196f3;
    }
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;

    &.btn-primary {
      background: #1e88e5;
      color: white;

      &:hover {
        background: #1565c0;
      }
    }

    &.btn-danger {
      background: #d32f2f;
      color: white;

      &:hover:not(:disabled) {
        background: #b71c1c;
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
}

@media (max-width: 768px) {
  .privacy-settings {
    padding: 15px;

    .privacy-tabs {
      flex-wrap: wrap;
    }

    .tab-content {
      padding: 15px;
    }

    .data-actions .action-group {
      padding: 15px;
    }

    .actions {
      flex-direction: column !important;

      .btn {
        width: 100%;
      }
    }
  }
}
</style>
