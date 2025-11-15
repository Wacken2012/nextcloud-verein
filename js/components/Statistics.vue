<template>
  <div class="statistics-container">
    <h2>📊 Dashboard</h2>

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
      <p>Daten werden geladen...</p>
    </div>

    <!-- Statistik-Widgets -->
    <div v-else class="stats-grid">
      <!-- Widget: Mitglieder -->
      <div class="stat-widget">
        <h3>👥 Mitglieder</h3>
        <p class="stat-value">{{ statistics.memberCount }}</p>
        <p class="stat-label">Registrierte Mitglieder</p>
      </div>

      <!-- Widget: Offene Gebühren -->
      <div class="stat-widget warning">
        <h3>📋 Offene Gebühren</h3>
        <p class="stat-value">{{ formatCurrency(statistics.totalOpen) }}</p>
        <p class="stat-label">{{ statistics.openCount }} Einträge</p>
      </div>

      <!-- Widget: Bezahlte Gebühren -->
      <div class="stat-widget success">
        <h3>✓ Bezahlte Gebühren</h3>
        <p class="stat-value">{{ formatCurrency(statistics.totalPaid) }}</p>
        <p class="stat-label">{{ statistics.paidCount }} Einträge</p>
      </div>

      <!-- Widget: Überfällige Gebühren -->
      <div class="stat-widget error">
        <h3>⚠️ Überfällige Gebühren</h3>
        <p class="stat-value">{{ formatCurrency(statistics.totalOverdue) }}</p>
        <p class="stat-label">{{ statistics.overdueCount }} Einträge</p>
      </div>
    </div>

    <!-- Charts -->
    <div v-if="!loading" class="charts-grid">
      <!-- Balkendiagramm: Gebührenstatus -->
      <div class="chart-container">
        <h3>💰 Gebührenstatus</h3>
        <Bar
          :data="feeStatusChartData"
          :options="chartOptions.bar"
        />
      </div>

      <!-- Liniendiagramm: Mitgliederwachstum (Simuliert) -->
      <div class="chart-container">
        <h3>📈 Mitgliederwachstum (Letzte 6 Monate)</h3>
        <Line
          :data="memberGrowthChartData"
          :options="chartOptions.line"
        />
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
  Title,
  Tooltip,
  Legend,
  Colors,
} from 'chart.js'
import api from '../api'
import Alert from './Alert.vue'

// Registriere ChartJS Komponenten
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
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

<style scoped>
.statistics-container {
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

h2 {
  margin: 0 0 20px 0;
  font-size: 24px;
  color: var(--color-main-text);
}

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 300px;
  color: var(--color-text-light);
  font-size: 16px;
}

/* Statistik-Widgets */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 32px;
}

.stat-widget {
  background: var(--color-background-secondary, #f5f5f5);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-widget:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-widget h3 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--color-text-lighter);
}

.stat-value {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: bold;
  color: var(--color-main-text);
}

.stat-label {
  margin: 0;
  font-size: 12px;
  color: var(--color-text-lighter);
}

/* Typ-spezifische Styles */
.stat-widget.success {
  border-left: 4px solid #4caf50;
}

.stat-widget.warning {
  border-left: 4px solid #ffc107;
}

.stat-widget.error {
  border-left: 4px solid #f44336;
}

/* Charts Grid */
.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 20px;
}

.chart-container {
  background: var(--color-background-secondary, #f5f5f5);
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.chart-container h3 {
  margin: 0 0 20px 0;
  font-size: 16px;
  font-weight: 600;
  color: var(--color-main-text);
}

/* Responsive */
@media (max-width: 768px) {
  .statistics-container {
    padding: 12px;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .charts-grid {
    grid-template-columns: 1fr;
  }

  .stat-value {
    font-size: 24px;
  }
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .stat-widget {
    background: #2a2a2a;
  }
}
</style>
