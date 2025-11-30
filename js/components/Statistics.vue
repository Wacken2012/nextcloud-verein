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

      <!-- Widget: Fällige Gebühren -->
      <div class="stat-widget warning" role="button" tabindex="0" @click="emit('navigate','finance')" @keydown.enter="emit('navigate','finance')">
        <div class="stat-header">
          <h3 class="stat-title">📋 Fällige Gebühren</h3>
          <span class="stat-icon warning-icon">📋</span>
        </div>
        <p class="stat-value">{{ formatCurrency(statistics.totalDue) }}</p>
        <p class="stat-label">{{ statistics.dueCount }} Einträge</p>
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

    <!-- Charts (temporarily disabled to restore navigation) -->
    <div v-if="!loading && showCharts" class="charts-grid">
      <!-- Balkendiagramm: Gebührenstatus -->
      <div class="chart-container">
        <h3 class="chart-title">💰 Gebührenstatus</h3>
        <div class="chart-wrapper">
          <component
            v-if="chartsReady"
            :is="BarComp"
            :data="feeStatusChartData"
            :options="chartOptions.bar"
          />
        </div>
      </div>

      <!-- Liniendiagramm: Mitgliederwachstum (Simuliert) -->
      <div class="chart-container">
        <h3 class="chart-title">📈 Mitgliederwachstum (Letzte 6 Monate)</h3>
        <div class="chart-wrapper">
          <component
            v-if="chartsReady"
            :is="LineComp"
            :data="memberGrowthChartData"
            :options="chartOptions.line"
          />
        </div>
      </div>
    </div>
    <!-- Placeholder boxes when charts are disabled -->
    <div v-else-if="!loading && !showCharts" class="charts-grid chart-placeholders">
      <div class="chart-container placeholder">
        <h3 class="chart-title">💰 Gebührenstatus</h3>
        <p class="chart-disabled-hint">Diagramm vorübergehend deaktiviert.</p>
      </div>
      <div class="chart-container placeholder">
        <h3 class="chart-title">📈 Mitgliederwachstum (Letzte 6 Monate)</h3>
        <p class="chart-disabled-hint">Diagramm vorübergehend deaktiviert.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive, computed, watch, nextTick, shallowRef } from 'vue'
// Lazy load chart components to avoid early DOM binding issues
let Bar: any = null
let Line: any = null
const BarComp = shallowRef<any | null>(null)
const LineComp = shallowRef<any | null>(null)
const chartsReady = ref(false)
import api from '../api'
import Alert from './Alert.vue'
import { settingsStore } from '../store/settings'

// allow widgets to ask the parent to navigate to a different tab
const emit = defineEmits(['navigate'])

// Charts visibility controlled by global settings store (guard if not loaded or settings failed)
const showCharts = computed(() => settingsStore.loaded && settingsStore.enable_charts)

// Lazy registration: load Chart.js and vue-chartjs only when needed
const ensureChartsLoaded = async () => {
  if (Bar && Line) return
  const [chartJsMod, vueChartMod] = await Promise.all([
    import('chart.js'),
    import('vue-chartjs')
  ])
  const ChartJS: any = chartJsMod.Chart
  const {
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
  } = chartJsMod
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
  Bar = vueChartMod.Bar
  Line = vueChartMod.Line
  BarComp.value = vueChartMod.Bar
  LineComp.value = vueChartMod.Line
  chartsReady.value = true
}

watch(showCharts, async (val) => {
  if (val) {
    // Defer to ensure DOM is fully ready and containers sized
    await nextTick()
    await new Promise((resolve) => requestAnimationFrame(() => resolve(null)))
    await ensureChartsLoaded()
  }
})

interface Statistics {
  memberCount: number
  totalOpen: number
  totalPaid: number
  totalOverdue: number
  totalDue: number
  openCount: number
  paidCount: number
  overdueCount: number
  dueCount: number
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
  totalDue: 0,
  openCount: 0,
  paidCount: 0,
  overdueCount: 0,
  dueCount: 0,
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

    // Lade Mitglieder-Statistiken
    const memberStatsResponse = await api.getMemberStatistics()
    if (memberStatsResponse.data.status === 'ok') {
      statistics.memberCount = memberStatsResponse.data.data.total || 0
    }

    // Lade Gebühren-Statistiken
    const feeStatsResponse = await api.getFeeStatistics()
    if (feeStatsResponse.data.status === 'ok') {
      const feeData = feeStatsResponse.data.data

      // Aktualisiere Statistiken
      statistics.totalOpen = feeData.pendingAmount || 0
      statistics.totalPaid = feeData.paidAmount || 0
      statistics.totalOverdue = feeData.overdueAmount || 0
      statistics.totalDue = feeData.dueAmount || 0
      statistics.openCount = feeData.counts?.pending || 0
      statistics.paidCount = feeData.counts?.paid || 0
      statistics.overdueCount = feeData.counts?.overdue || 0
      statistics.dueCount = feeData.counts?.due || 0

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

// Debug instrumentation for Chart.js root cause (logs once when charts become enabled)
watch(showCharts, (val) => {
  if (val) {
    // Defer to ensure DOM layout stable
    requestAnimationFrame(() => {
      const chartWrappers = document.querySelectorAll('.chart-wrapper')
      chartWrappers.forEach((el, i) => {
        // Log element type and dimensions
        // Avoid throwing if element missing
        try {
          console.log('[ChartDebug] wrapper', i, {
            nodeName: el.nodeName,
            width: (el as HTMLElement).offsetWidth,
            height: (el as HTMLElement).offsetHeight
          })
        } catch (e) {
          console.warn('[ChartDebug] error inspecting wrapper', e)
        }
      })
    })
  }
})

onMounted(() => {
  loadStatistics()
})
</script>

<style scoped lang="scss">
// Responsive Breakpoints
$breakpoint-tablet: 768px;
$breakpoint-mobile: 480px;

.statistics-container {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  width: 100%;

  @media (min-width: 1200px) {
    /* three-column layout on large screens */
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    align-items: start;
  }

  @media (min-width: 768px) and (max-width: 1199px) {
    /* two-column layout on tablets */
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
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
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  cursor: pointer;

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
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
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
