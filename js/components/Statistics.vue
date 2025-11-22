<template>
  <div class="statistics-container">
    <header class="section-header">
      <h1>📊 Dashboard</h1>
      <p class="section-subtitle">Übersicht und Statistiken der Vereinsverwaltung</p>
    </header>

    <!-- Alert für Fehler -->
    <Alert
      ref="alertRef"
      type="error"
      title="Fehler beim Laden"
      :message="errorMessage"
      :errors="errorList"
    />

    <!-- Loading State -->
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Daten werden geladen...</p>
    </div>

    <!-- Statistik-Widgets -->
    <div v-else class="stats-grid">
      <!-- Widget: Mitglieder -->
      <div class="stat-widget" role="button" tabindex="0" @click="emit('navigate','members')" @keydown.enter="emit('navigate','members')">
        <div class="stat-header">
          <h3 class="stat-title">👥 Mitglieder</h3>
          <span class="stat-icon primary">👥</span>
        </div>
        <p class="stat-value">{{ statistics.memberCount }}</p>
        <p class="stat-label">Registrierte Mitglieder</p>
      </div>

      <!-- Widget: Offene Gebühren -->
      <div class="stat-widget warning" role="button" tabindex="0" @click="emit('navigate','finance')" @keydown.enter="emit('navigate','finance')">
        <div class="stat-header">
          <h3 class="stat-title">📋 Offene Gebühren</h3>
          <span class="stat-icon warning-icon">📋</span>
        </div>
        <p class="stat-value">{{ formatCurrency(statistics.totalOpen) }}</p>
        <p class="stat-label">{{ statistics.openCount }} Einträge</p>
      </div>

      <!-- Widget: Bezahlte Gebühren -->
      <div class="stat-widget success" role="button" tabindex="0" @click="emit('navigate','finance')" @keydown.enter="emit('navigate','finance')">
        <div class="stat-header">
          <h3 class="stat-title">✓ Bezahlte Gebühren</h3>
          <span class="stat-icon success-icon">✓</span>
        </div>
        <p class="stat-value">{{ formatCurrency(statistics.totalPaid) }}</p>
        <p class="stat-label">{{ statistics.paidCount }} Einträge</p>
      </div>

      <!-- Widget: Überfällige Gebühren -->
      <div class="stat-widget error" role="button" tabindex="0" @click="emit('navigate','finance')" @keydown.enter="emit('navigate','finance')">
        <div class="stat-header">
          <h3 class="stat-title">⚠️ Überfällige Gebühren</h3>
          <span class="stat-icon error-icon">⚠️</span>
        </div>
        <p class="stat-value">{{ formatCurrency(statistics.totalOverdue) }}</p>
        <p class="stat-label">{{ statistics.overdueCount }} Einträge</p>
      </div>
    </div>

    <!-- Charts -->
    <div v-if="!loading" class="charts-grid">
      <!-- Balkendiagramm: Gebührenstatus -->
      <div class="chart-container">
        <h3 class="chart-title">💰 Gebührenstatus</h3>
        <div class="chart-wrapper">
          <Bar
            :data="feeStatusChartData"
            :options="chartOptions.bar"
          />
        </div>
      </div>

      <!-- Liniendiagramm: Mitgliederwachstum (Simuliert) -->
      <div class="chart-container">
        <h3 class="chart-title">📈 Mitgliederwachstum (Letzte 6 Monate)</h3>
        <div class="chart-wrapper">
          <Line
            :data="memberGrowthChartData"
            :options="chartOptions.line"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { Bar, Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Filler,
  Title,
  Tooltip,
  Legend,
  Colors,
} from 'chart.js'
import api from '../api'
import Alert from './Alert.vue'

// allow widgets to ask the parent to navigate to a different tab
const emit = defineEmits(['navigate'])

// Registriere ChartJS Komponenten
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Filler,
  Title,
  Tooltip,
  Legend,
  Colors
)

interface Statistics {
  memberCount: number
  totalOpen: number
  totalPaid: number
  totalOverdue: number
  openCount: number
  paidCount: number
  overdueCount: number
}

const loading = ref(true)
const errorMessage = ref('')
const errorList = ref<string[]>([])
const alertRef = ref<InstanceType<typeof Alert> | null>(null)

const statistics = reactive<Statistics>({
  memberCount: 0,
  totalOpen: 0,
  totalPaid: 0,
  totalOverdue: 0,
  openCount: 0,
  paidCount: 0,
  overdueCount: 0,
})

// Chart Daten und Optionen
const feeStatusChartData = ref({
  labels: ['Offen', 'Bezahlt', 'Überfällig'],
  datasets: [
    {
      label: 'Betrag (€)',
      data: [0, 0, 0],
      backgroundColor: ['#ffd54f', '#4caf50', '#f44336'],
      borderColor: ['#fbc02d', '#388e3c', '#d32f2f'],
      borderWidth: 1,
    },
  ],
})

const memberGrowthChartData = ref({
  labels: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun'],
  datasets: [
    {
      label: 'Mitglieder',
      data: [8, 9, 10, 11, 11, 12],
      borderColor: '#1976d2',
      backgroundColor: 'rgba(25, 118, 210, 0.1)',
      fill: true,
      tension: 0.3,
      pointBackgroundColor: '#1976d2',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 5,
      pointHoverRadius: 7,
    },
  ],
})

const chartOptions = reactive({
  bar: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        display: true,
        position: 'top' as const,
      },
      title: {
        display: false,
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function (value: unknown) {
            return '€' + value
          },
        },
      },
    },
  },
  line: {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: {
        display: true,
        position: 'top' as const,
      },
      title: {
        display: false,
      },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1,
        },
      },
    },
  },
})

// Funktionen
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('de-DE', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
  }).format(amount)
}

const loadStatistics = async () => {
  try {
    loading.value = true
    errorMessage.value = ''
    errorList.value = []

    // Lade Mitglieder
    const membersResponse = await api.getMembers()
    if (membersResponse.status === 'ok') {
      statistics.memberCount = membersResponse.members?.length || 0
    }

    // Lade Gebühren
    const feesResponse = await api.getFees()
    if (feesResponse.status === 'ok') {
      const fees = feesResponse.fees || []

      // Berechne Statistiken
      statistics.totalOpen = 0
      statistics.totalPaid = 0
      statistics.totalOverdue = 0
      statistics.openCount = 0
      statistics.paidCount = 0
      statistics.overdueCount = 0

      fees.forEach((fee) => {
        switch (fee.status) {
          case 'open':
            statistics.totalOpen += fee.amount
            statistics.openCount++
            break
          case 'paid':
            statistics.totalPaid += fee.amount
            statistics.paidCount++
            break
          case 'overdue':
            statistics.totalOverdue += fee.amount
            statistics.overdueCount++
            break
        }
      })

      // Aktualisiere Chart-Daten
      feeStatusChartData.value.datasets[0].data = [
        statistics.totalOpen,
        statistics.totalPaid,
        statistics.totalOverdue,
      ]
    }

    loading.value = false
  } catch (error) {
    loading.value = false
    errorMessage.value = 'Fehler beim Laden der Statistiken'
    errorList.value = [
      error instanceof Error ? error.message : 'Unbekannter Fehler',
    ]
    console.error('Statistics Error:', error)
  }
}

onMounted(() => {
  loadStatistics()
})
</script>

<style scoped lang="scss">
// Responsive Breakpoints
$breakpoint-tablet: 768px;
$breakpoint-mobile: 480px;

.statistics-container {
  display: block;
  gap: 2rem;
  /* use the available width but limit for readability on very large screens */
  width: calc(100% - 48px);
  max-width: 1600px;
  margin: 0 24px;

  @media (min-width: 1100px) {
    /* two-column layout: left column for stat cards, right column for charts */
    display: grid;
    grid-template-columns: 360px 1fr;
    gap: 24px 32px;
    align-items: start;
  }
}

.section-header {
  margin: 0;

  h1 {
    margin: 0 0 0.5rem 0;
    font-size: 28px;
    font-weight: 600;
    color: var(--color-text);

    @media (max-width: $breakpoint-tablet) {
      font-size: 24px;
    }

    @media (max-width: $breakpoint-mobile) {
      font-size: 20px;
    }
  }

  .section-subtitle {
    margin: 0;
    font-size: 14px;
    color: var(--color-text-secondary);

    @media (max-width: $breakpoint-mobile) {
      font-size: 13px;
    }
  }
}

.loading {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 300px;
  color: var(--color-text-secondary);
  font-size: 16px;
  gap: 1rem;

  .spinner {
    width: 40px;
    height: 40px;
    border: 4px solid var(--color-border);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
  }

  p {
    margin: 0;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Statistik-Widgets */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 18px;
  width: 100%;

  @media (min-width: 1100px) {
    /* stack stat cards vertically in the left column on wide screens */
    grid-auto-flow: row;
  }

  @media (max-width: $breakpoint-tablet) {
    grid-template-columns: 1fr;
    gap: 12px;
  }
}

.stat-widget {
  background: var(--color-background, #ffffff);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;

  @media (max-width: $breakpoint-tablet) {
    padding: 16px;
  }

  @media (max-width: $breakpoint-mobile) {
    padding: 14px;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: var(--color-primary);
  }

  &.success {
    border-left: 4px solid var(--color-success, #4caf50);
  }

  &.warning {
    border-left: 4px solid var(--color-warning, #ffc107);
  }

  &.error {
    border-left: 4px solid var(--color-error, #f44336);
  }
}

.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
}

.stat-title {
  margin: 0;
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-secondary);
  flex: 1;

  @media (max-width: $breakpoint-mobile) {
    font-size: 12px;
  }
}

.stat-icon {
  font-size: 20px;
  flex-shrink: 0;

  @media (max-width: $breakpoint-mobile) {
    font-size: 18px;
  }

  &.primary {
    filter: hue-rotate(0deg);
  }

  &.warning-icon {
    filter: hue-rotate(30deg);
  }

  &.success-icon {
    filter: hue-rotate(90deg);
  }

  &.error-icon {
    filter: hue-rotate(-10deg);
  }
}

.stat-value {
  margin: 0 0 8px 0;
  font-size: 24px;
  font-weight: 700;
  color: var(--color-text);

  @media (max-width: $breakpoint-tablet) {
    font-size: 22px;
  }

  @media (max-width: $breakpoint-mobile) {
    font-size: 20px;
  }
}

.stat-label {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-secondary);

  @media (max-width: $breakpoint-mobile) {
    font-size: 11px;
  }
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 20px;
  width: 100%;
  align-items: start;

  @media (min-width: 1100px) {
    /* allow two charts side-by-side in the right column */
    grid-template-columns: repeat(2, minmax(320px, 1fr));
  }

  @media (max-width: $breakpoint-tablet) {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  @media (max-width: $breakpoint-mobile) {
    gap: 12px;
  }
}

.chart-container {
  background: var(--color-background, #ffffff);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;

  @media (max-width: $breakpoint-tablet) {
    padding: 16px;
  }

  @media (max-width: $breakpoint-mobile) {
    padding: 12px;
  }

  .chart-title {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
    color: var(--color-text);

    @media (max-width: $breakpoint-tablet) {
      font-size: 14px;
      margin-bottom: 12px;
    }

    @media (max-width: $breakpoint-mobile) {
      font-size: 13px;
      margin-bottom: 10px;
    }
  }
}

.chart-wrapper {
  position: relative;
  width: 100%;
  min-height: 300px;
  display: flex;
  align-items: center;

  @media (max-width: $breakpoint-mobile) {
    min-height: 250px;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .stat-widget {
    background: var(--color-background, #1a1a1a);
    border-color: var(--color-border, rgba(255, 255, 255, 0.1));

    &:hover {
      background: var(--color-background-hover, rgba(255, 255, 255, 0.05));
    }
  }

  .chart-container {
    background: var(--color-background, #1a1a1a);
    border-color: var(--color-border, rgba(255, 255, 255, 0.1));
  }
}
</style>
