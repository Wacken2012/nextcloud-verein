<template>
  <div class="finance-container">
    <!-- Form für neue Gebühr -->
    <div class="form-section">
      <h2>Neue Gebühr hinzufügen</h2>
      <form @submit.prevent="addFee" class="fee-form">
        <select
          v-model="formData.memberId"
          required
          class="form-input"
        >
          <option value="">-- Mitglied wählen --</option>
          <option v-for="member in members" :key="member.id" :value="member.id">
            {{ member.name }}
          </option>
        </select>
        <input
          v-model.number="formData.amount"
          type="number"
          step="0.01"
          placeholder="Betrag"
          required
          class="form-input"
        />
        <input
          v-model="formData.dueDate"
          type="datetime-local"
          required
          class="form-input"
        />
        <select v-model="formData.status" class="form-input">
          <option value="pending">Ausstehend</option>
          <option value="open">Offen</option>
          <option value="paid">Bezahlt</option>
          <option value="overdue">Überfällig</option>
        </select>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Wird gespeichert...' : 'Hinzufügen' }}
        </button>
      </form>
    </div>

    <!-- Statistics -->
    <div class="stats-section">
      <div class="stat-card">
        <div class="stat-label">Gesamt ausstehend</div>
        <div class="stat-value">{{ totalOutstanding.toFixed(2) }} €</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Bezahlt</div>
        <div class="stat-value">{{ totalPaid.toFixed(2) }} €</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">Anzahl Gebühren</div>
        <div class="stat-value">{{ fees.length }}</div>
      </div>
    </div>

    <!-- Fees Table -->
    <div class="table-section">
      <div class="section-header">
        <h2>Gebührenliste</h2>
        <div class="export-buttons">
          <ExportButtons resource="fees" inline />
        </div>
      </div>
      <div class="table-wrapper">
        <table class="fees-table">
          <thead>
            <tr>
              <th>Mitglied</th>
              <th>Betrag</th>
              <th>Status</th>
              <th>Fällig am</th>
              <th>Bezahlt am</th>
              <th>Aktionen</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="fee in fees" :key="fee.id" :class="{ editing: editingId === fee.id, [fee.status]: true }">
              <td>{{ getMemberName(fee.memberId) }}</td>

              <td v-if="editingId !== fee.id">{{ fee.amount.toFixed(2) }} €</td>
              <td v-if="editingId === fee.id">
                <input v-model.number="editData.amount" type="number" step="0.01" class="form-input-inline" />
              </td>

              <td v-if="editingId !== fee.id">
                <span :class="['status-badge', fee.status]">{{ getStatusLabel(fee.status) }}</span>
              </td>
              <td v-if="editingId === fee.id">
                <select v-model="editData.status" class="form-input-inline">
                  <option value="pending">Ausstehend</option>
                  <option value="open">Offen</option>
                  <option value="paid">Bezahlt</option>
                  <option value="overdue">Überfällig</option>
                </select>
              </td>

              <td v-if="editingId !== fee.id">{{ formatDate(fee.dueDate) }}</td>
              <td v-if="editingId === fee.id">
                <input v-model="editData.dueDate" type="datetime-local" class="form-input-inline" />
              </td>

              <td>{{ fee.paidDate ? formatDate(fee.paidDate) : '-' }}</td>

              <td class="actions">
                <button
                  v-if="editingId !== fee.id"
                  @click="startEdit(fee)"
                  class="btn btn-small btn-secondary"
                >
                  Bearbeiten
                </button>
                <button
                  v-else
                  @click="saveEdit(fee.id)"
                  class="btn btn-small btn-success"
                  :disabled="loading"
                >
                  Speichern
                </button>
                <button
                  v-if="editingId === fee.id"
                  @click="cancelEdit"
                  class="btn btn-small"
                >
                  Abbrechen
                </button>
                <button
                  @click="deleteFee(fee.id)"
                  class="btn btn-small btn-danger"
                  :disabled="loading"
                >
                  Löschen
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-if="fees.length === 0" class="empty-state">Keine Gebühren vorhanden</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { api } from '../api'
import ExportButtons from './ExportButtons.vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'Finance',
  components: { ExportButtons },
  setup() {
    const fees = ref([])
    const members = ref([])
    const loading = ref(false)
    const editingId = ref(null)

    const formData = ref({
      memberId: '',
      amount: '',
      status: 'pending',
      dueDate: ''
    })

    const editData = ref({})

    onMounted(async () => {
      await fetchMembers()
      await fetchFees()
    })

    const fetchMembers = async () => {
      try {
        const response = await api.get('members')
        members.value = response.data.members || []
      } catch (error) {
        console.error('Error fetching members:', error)
      }
    }

    const fetchFees = async () => {
      loading.value = true
      try {
        const response = await api.get('finance')
        fees.value = response.data.fees || []
      } catch (error) {
        console.error('Error fetching fees:', error)
      } finally {
        loading.value = false
      }
    }

    const addFee = async () => {
      loading.value = true
      try {
        await api.post('finance', formData.value)
        formData.value = { memberId: '', amount: '', status: 'pending', dueDate: '' }
        await fetchFees()
      } catch (error) {
        console.error('Error adding fee:', error)
      } finally {
        loading.value = false
      }
    }

    const startEdit = (fee) => {
      editingId.value = fee.id
      editData.value = { ...fee }
    }

    const saveEdit = async (id) => {
      loading.value = true
      try {
        await api.put(`finance/${id}`, editData.value)
        editingId.value = null
        await fetchFees()
      } catch (error) {
        console.error('Error updating fee:', error)
      } finally {
        loading.value = false
      }
    }

    const cancelEdit = () => {
      editingId.value = null
      editData.value = {}
    }

    const deleteFee = async (id) => {
      if (!confirm('Soll diese Gebühr wirklich gelöscht werden?')) return
      
      loading.value = true
      try {
        await api.delete(`finance/${id}`)
        await fetchFees()
      } catch (error) {
        console.error('Error deleting fee:', error)
      } finally {
        loading.value = false
      }
    }

    const getMemberName = (memberId) => {
      const member = members.value.find(m => m.id === memberId)
      return member ? member.name : `Mitglied #${memberId}`
    }

    const getStatusLabel = (status) => {
      const labels = {
        pending: 'Ausstehend',
        open: 'Offen',
        paid: 'Bezahlt',
        overdue: 'Überfällig'
      }
      return labels[status] || status
    }

    const formatDate = (dateString) => {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('de-DE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      })
    }

    const totalOutstanding = computed(() => {
      return fees.value
        .filter(f => f.status !== 'paid')
        .reduce((sum, f) => sum + (f.amount || 0), 0)
    })

    const totalPaid = computed(() => {
      return fees.value
        .filter(f => f.status === 'paid')
        .reduce((sum, f) => sum + (f.amount || 0), 0)
    })

    // Export is handled by <ExportButtons /> now

    return {
      fees,
      members,
      loading,
      editingId,
      formData,
      editData,
      totalOutstanding,
      totalPaid,
      addFee,
      startEdit,
      saveEdit,
      cancelEdit,
      deleteFee,
      getMemberName,
      getStatusLabel,
      formatDate
    }
  }
}
</script>

<style scoped lang="scss">
.finance-container {
  /* Use full width with responsive layout */
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 2rem;

  @media (min-width: 1200px) {
    /* two-column layout for wide screens: form on the left, stats/table on the right */
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 2rem;
    align-items: start;
  }
}

.form-section,
.table-section {
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 20px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);

  h2 {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 18px;
    color: var(--color-text);
  }
}

.fee-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  align-items: end;
}

.form-input,
.form-input-inline {
  padding: 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  background: var(--color-main-background);
  color: var(--color-text);
  font-size: 14px;

  &:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px var(--color-primary-light);
  }
}

.form-input-inline {
  width: 100%;
}

.stats-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.stat-card {
  background: var(--color-main-background);
  border: 1px solid var(--color-border);
  border-radius: 4px;
  padding: 16px;
  text-align: center;

  .stat-label {
    color: var(--color-text-secondary);
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .stat-value {
    color: var(--color-primary);
    font-size: 24px;
    font-weight: 700;
  }
}

.table-wrapper {
  overflow-x: auto;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  gap: 16px;
  flex-wrap: wrap;

  h2 {
    margin: 0;
    flex: 1;
  }
}

.export-buttons {
  display: flex;
  gap: 8px;
}

.fees-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;

  thead {
    background: var(--color-background-hover);
    border-bottom: 2px solid var(--color-border);

    th {
      padding: 12px;
      text-align: left;
      font-weight: 600;
      color: var(--color-text);
    }
  }

  tbody {
    tr {
      border-bottom: 1px solid var(--color-border);
      transition: background 0.2s;

      &:hover {
        background: var(--color-background-hover);
      }

      &.editing {
        background: var(--color-primary-light);
      }

      &.paid {
        opacity: 0.7;
      }

      &.overdue {
        background: var(--color-error-light);
      }

      td {
        padding: 12px;
        color: var(--color-text);
      }
    }
  }
}

.status-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;

  &.pending,
  &.open {
    background: var(--color-warning-light);
    color: var(--color-warning);
  }

  &.paid {
    background: var(--color-success-light);
    color: var(--color-success);
  }

  &.overdue {
    background: var(--color-error-light);
    color: var(--color-error);
  }
}

.actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.btn {
  padding: 6px 12px;
  border: 1px solid var(--color-border);
  border-radius: 4px;
  background: var(--color-main-background);
  color: var(--color-text);
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;

  &:hover:not(:disabled) {
    background: var(--color-background-hover);
    border-color: var(--color-primary);
  }

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  &.btn-primary {
    background: var(--color-primary);
    color: white;
    border-color: var(--color-primary);

    &:hover:not(:disabled) {
      background: var(--color-primary-element);
    }
  }

  &.btn-success {
    background: var(--color-success);
    color: white;
    border-color: var(--color-success);
  }

  &.btn-danger {
    background: var(--color-error);
    color: white;
    border-color: var(--color-error);
  }

  &.btn-secondary {
    background: var(--color-main-background);
    color: var(--color-text);
    border: 1px solid var(--color-border);

    &:hover:not(:disabled) {
      background: var(--color-background-hover);
      border-color: var(--color-primary);
    }
  }

  &.btn-secondary {
    background: var(--color-background-secondary);
    color: var(--color-text);
  }

  &.btn-small {
    padding: 4px 8px;
    font-size: 11px;
  }
}

.empty-state {
  text-align: center;
  color: var(--color-text-secondary);
  padding: 40px 20px;
}
</style>
