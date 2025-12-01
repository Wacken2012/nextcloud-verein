<template>
  <div class="reminder-log">
    <div class="log-header">
      <h3>{{ $t('reminders.log.title', 'Mahnung-Protokoll') }}</h3>
      <div class="log-controls">
        <input 
          v-model="filterMember"
          type="text" 
          :placeholder="$t('reminders.log.filterMember', 'Nach Mitglied filtern...')"
          class="filter-input"
        />
        <select v-model="filterStatus" class="filter-select">
          <option value="">{{ $t('reminders.log.allStatus', 'Alle Status') }}</option>
          <option value="pending">{{ $t('reminders.log.pending', 'Ausstehend') }}</option>
          <option value="sent">{{ $t('reminders.log.sent', 'Versandt') }}</option>
          <option value="resolved">{{ $t('reminders.log.resolved', 'Bezahlt') }}</option>
          <option value="error">{{ $t('reminders.log.error', 'Fehler') }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="loading">
      {{ $t('common.loading', 'Lädt...') }}
    </div>

    <table v-else class="log-table">
      <thead>
        <tr>
          <th>{{ $t('common.date', 'Datum') }}</th>
          <th>{{ $t('common.member', 'Mitglied') }}</th>
          <th>{{ $t('reminders.log.level', 'Stufe') }}</th>
          <th>{{ $t('reminders.log.status', 'Status') }}</th>
          <th>{{ $t('reminders.log.action', 'Aktion') }}</th>
          <th>{{ $t('reminders.log.result', 'Ergebnis') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="filteredLogs.length === 0" class="no-data">
          <td colspan="6">{{ $t('common.noData', 'Keine Daten vorhanden') }}</td>
        </tr>
        <tr v-for="log in filteredLogs" :key="log.id" :class="getRowClass(log)">
          <td>{{ formatDate(log.created_at) }}</td>
          <td>{{ log.member_name }}</td>
          <td>
            <span class="level-badge" :class="'level-' + log.reminder_level">
              {{ $t('reminders.level' + log.reminder_level, 'Stufe ' + log.reminder_level) }}
            </span>
          </td>
          <td>
            <span class="status-badge" :class="'status-' + log.status">
              {{ getStatusLabel(log.status) }}
            </span>
          </td>
          <td>{{ getActionLabel(log.action) }}</td>
          <td>
            <span v-if="log.email_sent" class="success">
              ✓ {{ $t('reminders.log.emailSent', 'Email versandt') }}
            </span>
            <span v-else-if="log.email_error" class="error">
              ✗ {{ log.email_error }}
            </span>
            <span v-else class="neutral">{{ $t('reminders.log.pending', 'Ausstehend') }}</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { api } from '@/api';

export default {
  name: 'ReminderLog',
  data() {
    return {
      logs: [],
      filterMember: '',
      filterStatus: '',
      loading: true,
      error: null,
    };
  },

  computed: {
    filteredLogs() {
      return this.logs.filter(log => {
        const memberMatch = this.filterMember === '' || 
          log.member_name.toLowerCase().includes(this.filterMember.toLowerCase());
        const statusMatch = this.filterStatus === '' || log.status === this.filterStatus;
        return memberMatch && statusMatch;
      });
    },
  },

  mounted() {
    this.loadLogs();
    // Automatisch aktualisieren alle 30 Sekunden
    this.pollInterval = setInterval(() => this.loadLogs(), 30000);
  },

  beforeUnmount() {
    if (this.pollInterval) {
      clearInterval(this.pollInterval);
    }
  },

  methods: {
    async loadLogs() {
      try {
        this.loading = true;
        const response = await api.get('/reminders/log');
        this.logs = response.data || [];
      } catch (error) {
        console.error('Error loading reminder logs:', error);
        this.error = this.$t('common.error.load', 'Fehler beim Laden');
      } finally {
        this.loading = false;
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

    getStatusLabel(status) {
      const labels = {
        'pending': this.$t('reminders.log.pending', 'Ausstehend'),
        'sent': this.$t('reminders.log.sent', 'Versandt'),
        'resolved': this.$t('reminders.log.resolved', 'Bezahlt'),
        'error': this.$t('reminders.log.error', 'Fehler'),
      };
      return labels[status] || status;
    },

    getActionLabel(action) {
      const labels = {
        'reminder_sent': this.$t('reminders.log.reminderSent', 'Mahnung versandt'),
        'email_sent': this.$t('reminders.log.emailSent', 'Email versandt'),
        'email_error': this.$t('reminders.log.emailError', 'Email-Fehler'),
        'email_missing': this.$t('reminders.log.emailMissing', 'Email fehlt'),
        'marked_resolved': this.$t('reminders.log.markedResolved', 'Als bezahlt markiert'),
      };
      return labels[action] || action;
    },

    getRowClass(log) {
      if (log.email_error) return 'row-error';
      if (log.email_sent) return 'row-success';
      return 'row-neutral';
    },
  },
};
</script>

<style scoped lang="scss">
.reminder-log {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  margin: 20px 0;

  .log-header {
    margin-bottom: 20px;

    h3 {
      font-size: 1.3em;
      font-weight: 600;
      color: #333;
      margin: 0 0 15px 0;
    }

    .log-controls {
      display: flex;
      gap: 10px;

      .filter-input,
      .filter-select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9em;

        &:focus {
          outline: none;
          border-color: #1e88e5;
          box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
      }

      .filter-input {
        flex: 1;
        min-width: 200px;
      }
    }
  }

  .loading {
    text-align: center;
    padding: 40px;
    color: #666;
  }

  .log-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    thead {
      background: #f5f5f5;
      border-bottom: 2px solid #ddd;

      th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 0.9em;
      }
    }

    tbody {
      tr {
        border-bottom: 1px solid #eee;

        &:hover {
          background: #f9f9f9;
        }

        &.no-data td {
          text-align: center;
          padding: 30px;
          color: #999;
        }

        &.row-error {
          background: #ffebee;
        }

        &.row-success {
          background: #e8f5e9;
        }

        td {
          padding: 12px 15px;
          color: #333;
          font-size: 0.9em;
        }
      }
    }

    .level-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 0.85em;
      font-weight: 500;

      &.level-1 {
        background: #e3f2fd;
        color: #1565c0;
      }

      &.level-2 {
        background: #fff3e0;
        color: #e65100;
      }

      &.level-3 {
        background: #ffebee;
        color: #c62828;
      }
    }

    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 0.85em;
      font-weight: 500;

      &.status-pending {
        background: #f5f5f5;
        color: #666;
      }

      &.status-sent {
        background: #e8f5e9;
        color: #2e7d32;
      }

      &.status-resolved {
        background: #c8e6c9;
        color: #1b5e20;
      }

      &.status-error {
        background: #ffcdd2;
        color: #b71c1c;
      }
    }

    .success {
      color: #2e7d32;
      font-weight: 500;
    }

    .error {
      color: #c62828;
      font-weight: 500;
    }

    .neutral {
      color: #999;
    }
  }
}

@media (max-width: 768px) {
  .reminder-log {
    padding: 15px;

    .log-controls {
      flex-direction: column;

      .filter-input {
        min-width: 100%;
      }
    }

    .log-table {
      font-size: 0.8em;

      th, td {
        padding: 10px;
      }
    }
  }
}
</style>
