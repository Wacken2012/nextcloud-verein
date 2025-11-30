<template>
  <div class="fee-list">
    <h2>Beitragsverwaltung</h2>
    
    <!-- Controls Section -->
    <div class="controls">
      <div class="left-controls">
        <button @click="showAddForm = true" class="button primary">
          ➕ Neuer Beitrag
        </button>
      </div>
      <div class="filters">
        <select v-model="statusFilter" class="status-filter">
          <option value="">Alle Status</option>
          <option value="open">Offen</option>
          <option value="paid">Bezahlt</option>
          <option value="overdue">Überfällig</option>
        </select>
        <ExportButtons resource="fees" inline @success="showSuccess" @error="showError" />
      </div>
    </div>
    
    <!-- Statistics -->
    <div class="statistics">
      <div class="stat-card">
        <div class="stat-value">{{ stats.total }}</div>
        <div class="stat-label">Gesamt</div>
      </div>
      <div class="stat-card open">
        <div class="stat-value">{{ stats.open }}</div>
        <div class="stat-label">Offen</div>
      </div>
      <div class="stat-card paid">
        <div class="stat-value">{{ stats.paid }}</div>
        <div class="stat-label">Bezahlt</div>
      </div>
      <div class="stat-card overdue">
        <div class="stat-value">{{ stats.overdue }}</div>
        <div class="stat-label">Überfällig</div>
      </div>
      <div class="stat-card amount">
        <div class="stat-value">{{ stats.totalAmount.toFixed(2) }} €</div>
        <div class="stat-label">Gesamtsumme offen</div>
      </div>
    </div>
    
    <!-- Add/Edit Fee Form -->
    <div v-if="showAddForm" class="form-container">
      <h3>{{ editingFee ? 'Beitrag bearbeiten' : 'Neuer Beitrag' }}</h3>
      <form @submit.prevent="saveFee">
        <input v-model.number="form.memberId" type="number" placeholder="Mitglieds-ID" required />
        <input v-model.number="form.amount" type="number" step="0.01" placeholder="Betrag" required />
        <select v-model="form.status" required>
          <option value="open">Offen</option>
          <option value="paid">Bezahlt</option>
          <option value="overdue">Überfällig</option>
        </select>
        <input v-model="form.dueDate" type="date" placeholder="Fälligkeitsdatum" required />
        <div class="form-buttons">
          <button type="submit" class="button primary">Speichern</button>
          <button type="button" @click="cancelEdit" class="button">Abbrechen</button>
        </div>
      </form>
    </div>

    <!-- Fees Table -->
    <div v-if="filteredFees.length > 0" class="table-container">
      <table>
        <thead>
          <tr>
            <th>Mitglied</th>
            <th>Betrag</th>
            <th>Status</th>
            <th>Fälligkeitsdatum</th>
            <th>Beschreibung</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="fee in filteredFees" :key="fee.id" :class="getRowClass(fee)">
            <td>{{ getMemberName(fee.memberId) }}</td>
            <td class="amount">{{ fee.amount.toFixed(2) }} €</td>
            <td>
              <span :class="['status-badge', `status-${fee.status}`]">
                {{ getStatusLabel(fee.status) }}
              </span>
            </td>
            <td :class="{ 'overdue-date': isOverdue(fee) }">
              {{ formatDate(fee.dueDate) }}
            </td>
            <td class="description">{{ fee.description || '-' }}</td>
            <td class="actions">
              <button @click="editFee(fee)" class="button-small" title="Bearbeiten">
                ✏️
              </button>
              <button
                v-if="fee.status === 'open'"
                @click="markAsPaid(fee)"
                class="button-small success"
                title="Als bezahlt markieren"
              >
                ✓
              </button>
              <button @click="deleteFee(fee.id)" class="button-small danger" title="Löschen">
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div class="table-info">
        {{ filteredFees.length }} von {{ fees.length }} Beiträgen
      </div>
    </div>
    <p v-else class="no-data">
      {{ fees.length > 0 ? 'Keine Beiträge gefunden (Filter anpassen)' : 'Keine Beiträge vorhanden.' }}
    </p>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import * as notify from '../notify'
import ExportButtons from './ExportButtons.vue'

export default {
  name: 'FeeList',
  components: { ExportButtons },
  data() {
    return {
      fees: [],
      members: [],
      showAddForm: false,
      editingFee: null,
      statusFilter: '',
      form: {
        memberId: '',
        amount: '',
        status: 'open',
        dueDate: '',
        description: ''
      }
    }
  },
  computed: {
    filteredFees() {
      if (!this.statusFilter) {
        return this.fees
      }
      return this.fees.filter(fee => fee.status === this.statusFilter)
    },
    stats() {
      const stats = {
        total: this.fees.length,
        open: 0,
        paid: 0,
        overdue: 0,
        totalAmount: 0
      }
      
      this.fees.forEach(fee => {
        if (fee.status === 'open') {
          stats.open++
          stats.totalAmount += fee.amount
        } else if (fee.status === 'paid') {
          stats.paid++
        } else if (fee.status === 'overdue') {
          stats.overdue++
          stats.totalAmount += fee.amount
        }
      })
      
      return stats
    }
  },
  mounted() {
    this.loadFees()
    this.loadMembers()
  },
  methods: {
    async loadFees() {
      try {
        const response = await axios.get(generateUrl('/apps/verein/api/fees'))
        this.fees = response.data
      } catch (error) {
        console.error('Error loading fees:', error)
        this.showError('Fehler beim Laden der Beiträge')
      }
    },
    async loadMembers() {
      try {
        const response = await axios.get(generateUrl('/apps/verein/api/members'))
        this.members = response.data
      } catch (error) {
        console.error('Error loading members:', error)
      }
    },
    async saveFee() {
      try {
        if (this.editingFee) {
          await axios.put(
            generateUrl(`/apps/verein/api/fees/${this.editingFee.id}`),
            this.form
          )
          this.showSuccess('Beitrag erfolgreich aktualisiert')
        } else {
          await axios.post(generateUrl('/apps/verein/api/fees'), this.form)
          this.showSuccess('Beitrag erfolgreich angelegt')
        }
        this.loadFees()
        this.cancelEdit()
      } catch (error) {
        console.error('Error saving fee:', error)
        this.showError('Fehler beim Speichern des Beitrags')
      }
    },
    editFee(fee) {
      this.editingFee = fee
      this.form = { ...fee }
      this.showAddForm = true
    },
    async deleteFee(id) {
      if (!confirm('Beitrag wirklich löschen?')) {
        return
      }
      
      try {
        await axios.delete(generateUrl(`/apps/verein/api/fees/${id}`))
        this.showSuccess('Beitrag erfolgreich gelöscht')
        this.loadFees()
      } catch (error) {
        console.error('Error deleting fee:', error)
        this.showError('Fehler beim Löschen des Beitrags')
      }
    },
    async markAsPaid(fee) {
      try {
        await axios.put(
          generateUrl(`/apps/verein/api/fees/${fee.id}`),
          {
            ...fee,
            status: 'paid',
            paidDate: new Date().toISOString().split('T')[0]
          }
        )
        this.showSuccess('Beitrag als bezahlt markiert')
        this.loadFees()
      } catch (error) {
        console.error('Error marking fee as paid:', error)
        this.showError('Fehler beim Aktualisieren des Beitragsstatus')
      }
    },
    cancelEdit() {
      this.showAddForm = false
      this.editingFee = null
      this.form = {
        memberId: '',
        amount: '',
        status: 'open',
        dueDate: '',
        description: ''
      }
    },
    getStatusLabel(status) {
      const labels = {
        open: 'Offen',
        paid: 'Bezahlt',
        overdue: 'Überfällig'
      }
      return labels[status] || status
    },
    getMemberName(memberId) {
      const member = this.members.find(m => m.id === memberId)
      return member ? member.name : `ID: ${memberId}`
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      return date.toLocaleDateString('de-DE')
    },
    isOverdue(fee) {
      if (fee.status !== 'open') return false
      const today = new Date()
      const dueDate = new Date(fee.dueDate)
      return dueDate < today
    },
    getRowClass(fee) {
      if (this.isOverdue(fee)) {
        return 'row-overdue'
      }
      if (fee.status === 'paid') {
        return 'row-paid'
      }
      return ''
    },
    showSuccess(message) { notify.success(message) },
    showError(message) { notify.error(message) }
  }
}
</script>

<style scoped>
.fee-list {
  padding: 20px;
}

.controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.filters {
  display: flex;
  gap: 10px;
}

.status-filter {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
}

.statistics {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.stat-card {
  background: white;
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  text-align: center;
  border-left: 4px solid #ccc;
}

.stat-card.open {
  border-left-color: #ff9800;
}

.stat-card.paid {
  border-left-color: #4caf50;
}

.stat-card.overdue {
  border-left-color: #f44336;
}

.stat-card.amount {
  border-left-color: #2196f3;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  color: #333;
}

.stat-label {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
}

.table-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.button.primary {
  background-color: #0082c9;
  color: white;
}

.button.primary:hover {
  background-color: #006ba3;
}

.button {
  background-color: #f0f0f0;
  color: #333;
}

.button:hover {
  background-color: #e0e0e0;
}

.button-small {
  padding: 4px 8px;
  background-color: #f0f0f0;
  margin-right: 5px;
  font-size: 16px;
}

.button-small:hover {
  background-color: #e0e0e0;
}

.button-small.success {
  background-color: #e8f5e9;
}

.button-small.success:hover {
  background-color: #c8e6c9;
}

.button-small.danger:hover {
  background-color: #ffcccc;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f5f5f5;
  font-weight: bold;
  color: #333;
}

tr:hover {
  background-color: #f9f9f9;
}

.row-overdue {
  background-color: #ffebee;
}

.row-overdue:hover {
  background-color: #ffcdd2;
}

.row-paid {
  opacity: 0.6;
}

.amount {
  font-weight: bold;
  text-align: right;
  font-family: monospace;
}

.description {
  max-width: 200px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.status-open {
  background-color: #fff3e0;
  color: #f57c00;
}

.status-paid {
  background-color: #e8f5e9;
  color: #2e7d32;
}

.status-overdue {
  background-color: #ffebee;
  color: #c62828;
}

.overdue-date {
  color: #c62828;
  font-weight: bold;
}

.actions {
  white-space: nowrap;
}

.table-info {
  padding: 10px 12px;
  background-color: #f5f5f5;
  border-top: 1px solid #ddd;
  font-size: 13px;
  color: #666;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #666;
  font-style: italic;
}

.form-container {
  background: #f5f5f5;
  padding: 20px;
  margin: 20px 0;
  border-radius: 8px;
}

form input,
form select,
form textarea {
  display: block;
  width: 100%;
  margin-bottom: 10px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-buttons {
  margin-top: 15px;
}
</style>
