<template>
  <div class="privacy-settings">
    <div class="privacy-header">
      <button @click="goBack" class="back-btn" title="Zurück zu Einstellungen">← Zurück</button>
      <h3>{{ 'Datenschutzerklärung & DSGVO' }}</h3>
      <p class="description">
        {{ 'Verwalten Sie Ihre persönlichen Daten gemäß DSGVO' }}
      </p>
    </div>

    <!-- Tabs -->
    <div class="privacy-tabs">
      <button 
        :class="['tab', { active: activeTab === 'data' }]"
        @click="activeTab = 'data'"
      >
        {{ 'Meine Daten' }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'consents' }]"
        @click="activeTab = 'consents'"
      >
        {{ 'Einwilligungen' }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'auditlog' }]"
        @click="activeTab = 'auditlog'; loadAuditLog()"
      >
        {{ 'Verlauf' }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'policy' }]"
        @click="activeTab = 'policy'"
      >
        {{ 'Datenschutzerklärung' }}
      </button>
    </div>

    <!-- Meine Daten Tab -->
    <div v-show="activeTab === 'data'" class="tab-content data-tab">
      <div class="data-actions">
        <div class="action-group">
          <h4>{{ 'Meine Daten verwalten' }}</h4>
          <p class="info">
            {{ 'Gemäß DSGVO Art. 15 haben Sie das Recht auf Auskunft über Ihre persönlichen Daten.' }}
          </p>

          <div class="actions">
            <button class="btn btn-primary" @click="exportData">
              📥 {{ 'Daten exportieren (JSON)' }}
            </button>
          </div>
        </div>

        <div class="action-group danger-zone">
          <h4>{{ 'Daten löschen' }}</h4>
          <p class="info warning">
            {{ 'Gemäß DSGVO Art. 17 können Sie die Löschung Ihrer Daten anfordern.' }}
          </p>

          <!-- Löschstatus-Prüfung -->
          <div v-if="deleteBlockers.length > 0" class="blockers-warning">
            <strong>⚠️ Vollständiges Löschen nicht möglich:</strong>
            <ul>
              <li v-for="blocker in deleteBlockers" :key="blocker.type">
                {{ blocker.message }}
              </li>
            </ul>
          </div>

          <div class="delete-options">
            <div class="delete-option">
              <label>
                <input 
                  v-model="deleteMode" 
                  type="radio" 
                  value="soft_delete"
                />
                <span class="label-text">
                  <strong>{{ 'Anonymisieren' }}</strong>
                  <small>{{ 'Persönliche Daten werden anonymisiert, Transaktionsverlauf bleibt für Buchhaltung.' }}</small>
                </span>
              </label>
            </div>

            <div class="delete-option">
              <label :class="{ disabled: !canHardDelete }">
                <input 
                  v-model="deleteMode" 
                  type="radio" 
                  value="hard_delete"
                  :disabled="!canHardDelete"
                />
                <span class="label-text">
                  <strong>{{ 'Komplettes Löschen' }}</strong>
                  <small>{{ canHardDelete ? 'Alle Daten werden unwiderruflich gelöscht.' : 'Nicht verfügbar - siehe Gründe oben.' }}</small>
                </span>
              </label>
            </div>
          </div>

          <button 
            class="btn btn-danger" 
            @click="confirmDelete"
            :disabled="deleteInProgress"
          >
            {{ deleteInProgress ? '...' : '🗑️ Daten löschen' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Einwilligungen Tab -->
    <div v-show="activeTab === 'consents'" class="tab-content consents-tab">
      <div class="consents-container">
        <h4>{{ 'Ihre Einwilligungen' }}</h4>
        <p class="info">
          {{ 'Sie können Ihre Einwilligungen jederzeit ändern. Änderungen werden sofort wirksam und protokolliert.' }}
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
            <div class="consent-meta">
              <small v-if="getConsentDate(consent.key)" class="consent-date">
                <span v-if="getConsentValue(consent.key)">✅ Gegeben am: </span>
                <span v-else>❌ Widerrufen am: </span>
                {{ formatDate(getConsentDate(consent.key)) }}
              </small>
            </div>
          </div>
        </div>

        <div class="consent-actions">
          <button class="btn btn-secondary" @click="revokeAllConsents">
            Alle Einwilligungen widerrufen
          </button>
        </div>
      </div>
    </div>

    <!-- Audit-Log Tab (NEU) -->
    <div v-show="activeTab === 'auditlog'" class="tab-content auditlog-tab">
      <div class="auditlog-container">
        <h4>{{ 'Verlauf Ihrer Datenverarbeitung' }}</h4>
        <p class="info">
          {{ 'Hier sehen Sie alle protokollierten Aktionen bezüglich Ihrer persönlichen Daten.' }}
        </p>

        <div v-if="auditLogLoading" class="loading">
          Verlauf wird geladen...
        </div>

        <div v-else-if="auditLog.length === 0" class="empty-state">
          <p>Keine Einträge vorhanden.</p>
        </div>

        <div v-else class="audit-log-list">
          <div v-for="entry in auditLog" :key="entry.id" class="audit-entry">
            <div class="audit-icon">
              <span v-if="entry.action.includes('export')">📥</span>
              <span v-else-if="entry.action.includes('delete')">🗑️</span>
              <span v-else-if="entry.action.includes('consent_given')">✅</span>
              <span v-else-if="entry.action.includes('consent_revoked')">❌</span>
              <span v-else-if="entry.action.includes('view')">👁️</span>
              <span v-else-if="entry.action.includes('edit')">✏️</span>
              <span v-else>📋</span>
            </div>
            <div class="audit-content">
              <div class="audit-action">{{ entry.actionLabel || entry.action }}</div>
              <div class="audit-details" v-if="entry.details && Object.keys(entry.details).length > 0">
                <small>{{ formatDetails(entry.details) }}</small>
              </div>
              <div class="audit-meta">
                <small>{{ formatDate(entry.createdAt) }}</small>
                <small v-if="entry.ipAddress"> · IP: {{ entry.ipAddress }}</small>
              </div>
            </div>
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
      auditLog: [],
      auditLogLoading: false,
      canHardDelete: true,
      deleteBlockers: [],
      message: null,
      currentUser: null,
      consentTypes: [
        {
          key: 'newsletter',
          label: 'Newsletter',
          description: 'Ich möchte den Vereinsnewsletter per E-Mail erhalten.',
        },
        {
          key: 'marketing',
          label: 'Marketing und Werbung',
          description: 'Meine Daten dürfen für Werbung und Marketing verwendet werden.',
        },
        {
          key: 'analytics',
          label: 'Anonyme Nutzungsstatistiken',
          description: 'Anonymisierte Nutzungsdaten dürfen für Statistiken verwendet werden.',
        },
        {
          key: 'partners',
          label: 'Weitergabe an Partner',
          description: 'Meine Daten dürfen an Partnerorganisationen weitergegeben werden.',
        },
        {
          key: 'photos',
          label: 'Veröffentlichung von Fotos',
          description: 'Fotos von mir dürfen auf der Vereinswebsite und in sozialen Medien veröffentlicht werden.',
        },
        {
          key: 'internal_communication',
          label: 'Interne Vereinskommunikation',
          description: 'Ich möchte über Vereinsaktivitäten per E-Mail informiert werden.',
        },
        {
          key: 'birthday_list',
          label: 'Geburtstagsliste',
          description: 'Mein Geburtstag darf in der internen Geburtstagsliste erscheinen.',
        },
        {
          key: 'member_directory',
          label: 'Mitgliederverzeichnis',
          description: 'Meine Kontaktdaten dürfen im internen Mitgliederverzeichnis erscheinen.',
        },
      ],
    };
  },

  mounted() {
    this.currentUser = getCurrentUser();
    this.loadPrivacyPolicy();
    this.loadConsents();
    this.checkDeleteEligibility();
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

    async checkDeleteEligibility() {
      if (!this.currentUser?.uid) return;
      try {
        const response = await api.get(`api/v1/privacy/can-delete/${this.currentUser.uid}`);
        this.canHardDelete = response.data?.canHardDelete ?? true;
        this.deleteBlockers = response.data?.blockers ?? [];
      } catch (error) {
        console.error('Error checking delete eligibility:', error);
        // Bei Fehler: Erlaube soft delete, blockiere hard delete
        this.canHardDelete = false;
      }
    },

    async loadAuditLog() {
      if (!this.currentUser?.uid) return;
      if (this.auditLog.length > 0) return; // Bereits geladen
      
      try {
        this.auditLogLoading = true;
        const response = await api.get(`api/v1/privacy/audit-log/${this.currentUser.uid}`);
        this.auditLog = response.data?.logs ?? [];
      } catch (error) {
        console.error('Error loading audit log:', error);
        this.auditLog = [];
      } finally {
        this.auditLogLoading = false;
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
        // Audit-Log neu laden wenn Tab offen
        if (this.activeTab === 'auditlog') {
          this.auditLog = [];
          this.loadAuditLog();
        }
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

        const response = await api.deleteMemberData(this.currentUser.uid, this.deleteMode);

        // Prüfe ob tatsächlich gelöscht wurde
        if (response.data?.success === false) {
          this.showMessage(response.data?.message || 'Keine Daten zum Löschen vorhanden', 'info');
          return;
        }

        this.showMessage(
          this.deleteMode === 'soft_delete'
            ? 'Daten erfolgreich anonymisiert'
            : 'Daten erfolgreich gelöscht',
          'success'
        );

        // Redirect nach erfolgreicher Löschung zur Nextcloud-Startseite
        setTimeout(() => {
          window.location.href = OC.generateUrl('/');
        }, 2000);
      } catch (error) {
        console.error('Error deleting data:', error);
        this.showMessage('Fehler beim Löschen: ' + (error.response?.data?.message || error.message), 'error');
      } finally {
        this.deleteInProgress = false;
      }
    },

    async revokeAllConsents() {
      if (!confirm('Wirklich alle Einwilligungen widerrufen?')) return;
      
      try {
        const consentsToRevoke = {};
        this.consentTypes.forEach(ct => {
          consentsToRevoke[ct.key] = false;
        });

        await api.post(`api/v1/privacy/consent/${this.currentUser.uid}/bulk`, {
          consents: consentsToRevoke
        });

        this.loadConsents();
        this.showMessage('Alle Einwilligungen widerrufen', 'success');
      } catch (error) {
        console.error('Error revoking consents:', error);
        this.showMessage('Fehler: ' + (error.response?.data?.message || error.message), 'error');
      }
    },

    getConsentValue(key) {
      return this.consents[key]?.given ?? false;
    },

    getConsentDate(key) {
      // Prüfe sowohl givenAt als auch revokedAt
      const consent = this.consents[key];
      if (!consent) return null;
      return consent.given ? consent.givenAt : consent.revokedAt;
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
      if (!dateString) return '';
      return new Date(dateString).toLocaleString('de-DE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
      });
    },

    formatDetails(details) {
      if (!details) return '';
      // Formatiere Details als lesbaren String
      const entries = Object.entries(details)
        .filter(([key]) => key !== 'description')
        .map(([key, value]) => `${key}: ${value}`);
      
      if (details.description) {
        entries.unshift(details.description);
      }
      
      return entries.join(' · ');
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

    .consent-meta {
      margin-left: 28px;
    }

    .consent-date {
      color: #999;
      font-size: 0.85em;
    }
  }

  .consent-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
  }

  .blockers-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 15px;
    margin-bottom: 15px;

    strong {
      color: #856404;
      display: block;
      margin-bottom: 10px;
    }

    ul {
      margin: 0;
      padding-left: 20px;

      li {
        color: #856404;
        margin-bottom: 5px;
      }
    }
  }

  .delete-option label.disabled {
    opacity: 0.5;
    cursor: not-allowed;

    input {
      cursor: not-allowed;
    }
  }

  // Audit-Log Tab Styles
  .auditlog-container {
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

    .loading {
      text-align: center;
      padding: 40px;
      color: #666;
    }

    .empty-state {
      text-align: center;
      padding: 40px;
      color: #999;
      background: #f9f9f9;
      border-radius: 6px;
    }
  }

  .audit-log-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .audit-entry {
    display: flex;
    gap: 15px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 3px solid #1e88e5;

    .audit-icon {
      font-size: 1.5em;
      flex-shrink: 0;
    }

    .audit-content {
      flex: 1;
      min-width: 0;

      .audit-action {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
      }

      .audit-details {
        color: #666;
        margin-bottom: 4px;
      }

      .audit-meta {
        color: #999;
        font-size: 0.85em;

        small {
          margin-right: 5px;
        }
      }
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

    &.btn-secondary {
      background: #f5f5f5;
      color: #333;
      border: 1px solid #ddd;

      &:hover {
        background: #eee;
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

    .audit-entry {
      flex-direction: column;
      gap: 10px;
    }
  }
}
</style>
