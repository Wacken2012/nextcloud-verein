<template>
  <div class="reminder-settings">
    <div class="reminder-header">
      <h3>{{ $t('reminders.title', 'Automatische Mahnungen') }}</h3>
      <p class="description">{{ $t('reminders.description', 'Konfigurieren Sie die automatischen Mahnungen für ausstehende Zahlungen') }}</p>
    </div>

    <div class="settings-container">
      <!-- Aktivierungsschalter -->
      <div class="setting-group">
        <label>
          <input 
            v-model="config.enabled" 
            type="checkbox" 
            @change="toggleReminders"
          />
          <span>{{ $t('reminders.enable', 'Automatische Mahnungen aktivieren') }}</span>
        </label>
      </div>

      <transition name="fade">
        <div v-if="config.enabled" class="settings-content">
          <!-- Mahnstufen-Intervalle -->
          <div class="setting-group">
            <h4>{{ $t('reminders.intervals.title', 'Mahnstufen') }}</h4>
            
            <div class="interval-setting">
              <label>
                {{ $t('reminders.intervals.level1', 'Stufe 1 (Vorerinnerung)') }}
                <span class="info">{{ $t('reminders.intervals.level1.info', 'Tage vor Fälligkeit') }}</span>
              </label>
              <input 
                v-model.number="config.intervals.level_1" 
                type="number" 
                min="1" 
                max="30"
                @change="saveConfig"
              />
              <span class="unit">{{ $t('common.days', 'Tage') }}</span>
            </div>

            <div class="interval-setting">
              <label>
                {{ $t('reminders.intervals.level2', 'Stufe 2 (erste Mahnung)') }}
                <span class="info">{{ $t('reminders.intervals.level2.info', 'Tage nach Fälligkeit') }}</span>
              </label>
              <input 
                v-model.number="config.intervals.level_2" 
                type="number" 
                min="1" 
                max="30"
                @change="saveConfig"
              />
              <span class="unit">{{ $t('common.days', 'Tage') }}</span>
            </div>

            <div class="interval-setting">
              <label>
                {{ $t('reminders.intervals.level3', 'Stufe 3 (zweite Mahnung)') }}
                <span class="info">{{ $t('reminders.intervals.level3.info', 'Tage nach Stufe 2') }}</span>
              </label>
              <input 
                v-model.number="config.intervals.level_3" 
                type="number" 
                min="1" 
                max="30"
                @change="saveConfig"
              />
              <span class="unit">{{ $t('common.days', 'Tage') }}</span>
            </div>
          </div>

          <!-- Zwischenversand-Intervall -->
          <div class="setting-group">
            <label>
              {{ $t('reminders.daysBetween.title', 'Tage zwischen wiederholten Mahnungen') }}
              <span class="info">{{ $t('reminders.daysBetween.info', 'Minimale Tage, bevor eine weitere Mahnung an das gleiche Mitglied versandt wird') }}</span>
            </label>
            <input 
              v-model.number="config.daysBetween" 
              type="number" 
              min="1" 
              max="30"
              @change="saveConfig"
            />
            <span class="unit">{{ $t('common.days', 'Tage') }}</span>
          </div>

          <!-- Status -->
          <div class="status-section">
            <p v-if="saveSuccess" class="success">
              ✓ {{ $t('common.saved', 'Einstellungen gespeichert') }}
            </p>
            <p v-if="saveError" class="error">
              ✗ {{ saveError }}
            </p>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import { api } from '@/api';

export default {
  name: 'ReminderSettings',
  data() {
    return {
      config: {
        enabled: false,
        intervals: {
          level_1: 7,
          level_2: 3,
          level_3: 7,
        },
        daysBetween: 3,
      },
      saveSuccess: false,
      saveError: null,
      loading: true,
    };
  },

  computed: {
    hasChanges() {
      return JSON.stringify(this.config) !== JSON.stringify(this.initialConfig);
    },
  },

  mounted() {
    this.loadConfig();
  },

  methods: {
    async loadConfig() {
      try {
        this.loading = true;
        const response = await api.get('/reminders/config');
        this.config = response.data;
        this.initialConfig = JSON.parse(JSON.stringify(this.config));
      } catch (error) {
        console.error('Error loading reminder config:', error);
        this.saveError = this.$t('common.error.load', 'Fehler beim Laden der Einstellungen');
      } finally {
        this.loading = false;
      }
    },

    async saveConfig() {
      try {
        this.saveError = null;
        const response = await api.post('/reminders/config', this.config);
        this.config = response.data;
        this.initialConfig = JSON.parse(JSON.stringify(this.config));
        this.saveSuccess = true;
        setTimeout(() => {
          this.saveSuccess = false;
        }, 3000);
      } catch (error) {
        console.error('Error saving reminder config:', error);
        this.saveError = this.$t('common.error.save', 'Fehler beim Speichern der Einstellungen');
      }
    },

    async toggleReminders() {
      await this.saveConfig();
    },
  },
};
</script>

<style scoped lang="scss">
.reminder-settings {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  margin: 20px 0;

  .reminder-header {
    margin-bottom: 30px;

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

  .settings-container {
    background: white;
    padding: 20px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  }

  .setting-group {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;

    &:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    h4 {
      font-weight: 600;
      color: #333;
      margin: 0 0 15px 0;
      font-size: 1em;
    }

    label {
      display: flex;
      align-items: center;
      cursor: pointer;
      color: #333;
      font-weight: 500;

      input[type="checkbox"] {
        margin-right: 10px;
        cursor: pointer;
      }

      span.info {
        display: block;
        font-size: 0.85em;
        color: #999;
        font-weight: 400;
        margin-top: 5px;
        margin-left: 26px;
      }
    }
  }

  .interval-setting {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;

    label {
      flex: 1;
      display: flex;
      flex-direction: column;
      margin: 0;

      span.info {
        margin: 0;
        margin-top: 3px;
      }
    }

    input {
      width: 80px;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1em;

      &:focus {
        outline: none;
        border-color: #1e88e5;
        box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
      }
    }

    .unit {
      color: #999;
      font-size: 0.9em;
      min-width: 50px;
    }
  }

  .status-section {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;

    p {
      margin: 0;
      padding: 10px 15px;
      border-radius: 4px;
      font-size: 0.95em;

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
    }
  }

  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.3s;
  }

  .fade-enter,
  .fade-leave-to {
    opacity: 0;
  }
}

@media (max-width: 768px) {
  .reminder-settings {
    padding: 15px;

    .settings-container {
      padding: 15px;
    }

    .interval-setting {
      flex-direction: column;
      align-items: flex-start;

      input {
        width: 100%;
      }
    }
  }
}
</style>
