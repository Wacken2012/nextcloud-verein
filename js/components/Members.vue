<template>
  <div class="members-container">
    <!-- Alert Komponente -->
    <Alert
      ref="alertRef"
      type="error"
      :message="alertError"
      :errors="alertErrors"
    />

    <!-- Form für neues Mitglied -->
    <div class="form-section">
      <h2>Neues Mitglied hinzufügen</h2>
      <form @submit.prevent="addMember" class="member-form">
        <input
          v-model="formData.name"
          type="text"
          placeholder="Name"
          required
          class="form-input"
        />
        <input
          v-model="formData.email"
          type="email"
          placeholder="E-Mail"
          required
          class="form-input"
        />
        <input
          v-model="formData.address"
          type="text"
          placeholder="Adresse"
          class="form-input"
        />
        <input
          v-model="formData.iban"
          type="text"
          placeholder="IBAN"
          class="form-input"
        />
        <input
          v-model="formData.bic"
          type="text"
          placeholder="BIC"
          class="form-input"
        />
        <select v-model="formData.role" class="form-input">
          <option value="member">Mitglied</option>
          <option value="admin">Administrator</option>
          <option value="treasurer">Kassierer</option>
        </select>
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Wird gespeichert...' : 'Hinzufügen' }}
        </button>
      </form>
    </div>

    <!-- Members Table -->
    <div class="table-section">
      <h2>Mitgliederliste</h2>
      <div class="table-wrapper">
        <table class="members-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>E-Mail</th>
              <th>Adresse</th>
              <th>IBAN</th>
              <th>Rolle</th>
              <th>Aktionen</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="member in members" :key="member.id" :class="{ editing: editingId === member.id }">
              <td v-if="editingId !== member.id">{{ member.name }}</td>
              <td v-if="editingId === member.id">
                <input v-model="editData.name" class="form-input-inline" />
              </td>

              <td v-if="editingId !== member.id">{{ member.email }}</td>
              <td v-if="editingId === member.id">
                <input v-model="editData.email" type="email" class="form-input-inline" />
              </td>

              <td v-if="editingId !== member.id">{{ member.address || '-' }}</td>
              <td v-if="editingId === member.id">
                <input v-model="editData.address" class="form-input-inline" />
              </td>

              <td v-if="editingId !== member.id">{{ member.iban || '-' }}</td>
              <td v-if="editingId === member.id">
                <input v-model="editData.iban" class="form-input-inline" />
              </td>

              <td v-if="editingId !== member.id">
                <span :class="['role-badge', member.role]">{{ member.role }}</span>
              </td>
              <td v-if="editingId === member.id">
                <select v-model="editData.role" class="form-input-inline">
                  <option value="member">Mitglied</option>
                  <option value="admin">Administrator</option>
                  <option value="treasurer">Kassierer</option>
                </select>
              </td>

              <td class="actions">
                <button
                  v-if="editingId !== member.id"
                  @click="startEdit(member)"
                  class="btn btn-small btn-secondary"
                >
                  Bearbeiten
                </button>
                <button
                  v-else
                  @click="saveEdit(member.id)"
                  class="btn btn-small btn-success"
                  :disabled="loading"
                >
                  Speichern
                </button>
                <button
                  v-if="editingId === member.id"
                  @click="cancelEdit"
                  class="btn btn-small"
                >
                  Abbrechen
                </button>
                <button
                  @click="deleteMember(member.id)"
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
      <p v-if="members.length === 0" class="empty-state">Keine Mitglieder vorhanden</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { api } from '../api'
import Alert from './Alert.vue'

export default {
  name: 'Members',
  components: {
    Alert
  },
  setup() {
    const members = ref([])
    const loading = ref(false)
    const editingId = ref(null)
    const alertError = ref('')
    const alertErrors = ref([])
    const alertRef = ref(null)

    const formData = ref({
      name: '',
      email: '',
      address: '',
      iban: '',
  bic: '',
  role: 'member'
    })

    const editData = ref({})

    onMounted(async () => {
      await fetchMembers()
    })

    const fetchMembers = async () => {
      loading.value = true
      try {
        const response = await api.get('members')
        members.value = response.data.members || []
      } catch (error) {
        console.error('Error fetching members:', error)
      } finally {
        loading.value = false
      }
    }

    const addMember = async () => {
      loading.value = true
      alertError.value = ''
      alertErrors.value = []
      try {
        const response = await api.post('members', formData.value)
        if (response.data.status === 'error') {
          alertError.value = response.data.message
          alertErrors.value = response.data.errors || []
          if (alertRef.value) alertRef.value.open()
        } else {
          formData.value = { name: '', email: '', address: '', iban: '', bic: '', role: 'member' }
          await fetchMembers()
        }
      } catch (error) {
        alertError.value = error.message || 'Fehler beim Hinzufügen des Mitglieds'
        alertErrors.value = []
        if (alertRef.value) alertRef.value.open()
        console.error('Error adding member:', error)
      } finally {
        loading.value = false
      }
    }

    const startEdit = async (member) => {
      editingId.value = member.id
      editData.value = { ...member }

      try {
        const response = await api.getMember(member.id)
        const latest = response.data?.data || response.data?.member
        if (latest) {
          editData.value = { ...latest }
        }
      } catch (error) {
        console.error('Error loading member details:', error)
        alertError.value = 'Fehler beim Laden des Mitglieds'
        alertErrors.value = []
        if (alertRef.value) alertRef.value.open()
      }
    }

    const saveEdit = async (id) => {
      loading.value = true
      try {
        await api.put(`members/${id}`, editData.value)
        editingId.value = null
        await fetchMembers()
      } catch (error) {
        console.error('Error updating member:', error)
      } finally {
        loading.value = false
      }
    }

    const cancelEdit = () => {
      editingId.value = null
      editData.value = {}
    }

    const deleteMember = async (id) => {
      if (!confirm('Soll dieses Mitglied wirklich gelöscht werden?')) return
      
      loading.value = true
      try {
        await api.delete(`members/${id}`)
        await fetchMembers()
      } catch (error) {
        console.error('Error deleting member:', error)
      } finally {
        loading.value = false
      }
    }

    return {
      members,
      loading,
      editingId,
      formData,
      editData,
      addMember,
      startEdit,
      saveEdit,
      cancelEdit,
      deleteMember,
      alertRef,
      alertError,
      alertErrors
    }
  }
}
</script>

<style scoped lang="scss">
.members-container {
  max-width: 1200px;
  margin: 0 auto;
}

.form-section,
.table-section {
  background: var(--color-main-background);
  border-radius: 4px;
  padding: 20px;
  margin-bottom: 20px;
  border: 1px solid var(--color-border);

  h2 {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 18px;
    color: var(--color-text);
  }
}

.member-form {
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

.table-wrapper {
  overflow-x: auto;
}

.members-table {
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

      td {
        padding: 12px;
        color: var(--color-text);
      }
    }
  }
}

.role-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;

  &.member {
    background: var(--color-primary-light);
    color: var(--color-primary);
  }

  &.admin {
    background: var(--color-error-light);
    color: var(--color-error);
  }

  &.treasurer {
    background: var(--color-success-light);
    color: var(--color-success);
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
