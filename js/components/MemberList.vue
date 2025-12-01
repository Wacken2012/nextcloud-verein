<template>
  <div class="member-list">
    <h2>Mitgliederverwaltung</h2>
    
    <!-- Controls Section -->
    <div class="controls">
      <div class="buttons">
        <button @click="showAddForm = true" class="button primary">
          ➕ Neues Mitglied
        </button>
        <ExportButtons resource="members" @success="showSuccess" @error="showError" />
      </div>
      
      <div class="filters">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="🔍 Suche nach Name..."
          class="search-input"
        />
        
        <select v-model="roleFilter" class="role-filter">
          <option value="">Alle Rollen</option>
          <option value="member">Mitglied</option>
          <option value="treasurer">Kassierer</option>
          <option value="board">Vorstand</option>
        </select>
      </div>
    </div>
    
    <!-- Add/Edit Member Form -->
    <MemberForm
      v-if="showAddForm"
      :member="editingMember"
      :is-edit="!!editingMember"
      @submit="saveMember"
      @cancel="cancelEdit"
    />

    <!-- Members Table -->
    <div v-if="filteredMembers.length > 0" class="table-container">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>E-Mail</th>
            <th>IBAN</th>
            <th>Rolle</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in filteredMembers" :key="member.id">
            <td>{{ member.name }}</td>
            <td>{{ member.email }}</td>
            <td class="iban">{{ formatIban(member.iban) }}</td>
            <td>
              <span :class="['role-badge', `role-${member.role}`]">
                {{ getRoleLabel(member.role) }}
              </span>
            </td>
            <td class="actions">
              <button @click="editMember(member)" class="button-small" title="Bearbeiten">
                ✏️
              </button>
              <button @click="deleteMember(member.id)" class="button-small danger" title="Löschen">
                🗑️
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div class="table-info">
        {{ filteredMembers.length }} von {{ members.length }} Mitgliedern
      </div>
    </div>
    <p v-else class="no-data">
      {{ members.length > 0 ? 'Keine Mitglieder gefunden (Filter anpassen)' : 'Keine Mitglieder vorhanden.' }}
    </p>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import * as notify from '../notify'
import MemberForm from './MemberForm.vue'
import ExportButtons from './ExportButtons.vue'

export default {
  name: 'MemberList',
  components: {
    MemberForm,
    ExportButtons
  },
  data() {
    return {
      members: [],
      showAddForm: false,
      editingMember: null,
      searchQuery: '',
      roleFilter: ''
    }
  },
  computed: {
    filteredMembers() {
      let filtered = this.members

      // Filter by search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(member =>
          member.name.toLowerCase().includes(query) ||
          member.email.toLowerCase().includes(query)
        )
      }

      // Filter by role
      if (this.roleFilter) {
        filtered = filtered.filter(member => member.role === this.roleFilter)
      }

      return filtered
    }
  },
  mounted() {
    this.loadMembers()
  },
  methods: {
    async loadMembers() {
      try {
        const response = await axios.get(generateUrl('/apps/verein/api/members'))
        this.members = response.data
      } catch (error) {
        console.error('Error loading members:', error)
        this.showError('Fehler beim Laden der Mitglieder')
      }
    },
    async saveMember(memberData) {
      try {
        if (this.editingMember) {
          await axios.put(
            generateUrl(`/apps/verein/api/members/${this.editingMember.id}`),
            memberData
          )
          this.showSuccess('Mitglied erfolgreich aktualisiert')
        } else {
          await axios.post(generateUrl('/apps/verein/api/members'), memberData)
          this.showSuccess('Mitglied erfolgreich angelegt')
        }
        this.loadMembers()
        this.cancelEdit()
      } catch (error) {
        console.error('Error saving member:', error)
        this.showError('Fehler beim Speichern des Mitglieds')
      }
    },
    editMember(member) {
      this.editingMember = member
      this.showAddForm = true
    },
    async deleteMember(id) {
      if (!confirm('Mitglied wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) {
        return
      }
      
      try {
        await axios.delete(generateUrl(`/apps/verein/api/members/${id}`))
        this.showSuccess('Mitglied erfolgreich gelöscht')
        this.loadMembers()
      } catch (error) {
        console.error('Error deleting member:', error)
        this.showError('Fehler beim Löschen des Mitglieds')
      }
    },
    cancelEdit() {
      this.showAddForm = false
      this.editingMember = null
    },
    formatIban(iban) {
      if (!iban) return ''
      // Format IBAN in groups of 4: DE89 3704 0044 0532 0130 00
      return iban.match(/.{1,4}/g)?.join(' ') || iban
    },
    getRoleLabel(role) {
      const labels = {
        member: 'Mitglied',
        treasurer: 'Kassierer',
        board: 'Vorstand'
      }
      return labels[role] || role
    },
    showSuccess(message) { notify.success(message) },
    showError(message) { notify.error(message) },
    }
}
</script>

<style scoped>
.member-list {
  padding: 20px;
}

.controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  gap: 20px;
  flex-wrap: wrap;
}

.buttons {
  display: flex;
  gap: 10px;
  align-items: center;
}

.filters {
  display: flex;
  gap: 10px;
  flex: 1;
  justify-content: flex-end;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  width: 300px;
  font-size: 14px;
}

.search-input:focus {
  outline: none;
  border-color: #0082c9;
}

.role-filter {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  cursor: pointer;
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

.button-small {
  padding: 4px 8px;
  background-color: #f0f0f0;
  margin-right: 5px;
  font-size: 16px;
}

.button-small:hover {
  background-color: #e0e0e0;
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

.iban {
  font-family: monospace;
  font-size: 13px;
}

.role-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
}

.role-member {
  background-color: #e3f2fd;
  color: #1976d2;
}

.role-treasurer {
  background-color: #fff3e0;
  color: #f57c00;
}

.role-board {
  background-color: #f3e5f5;
  color: #7b1fa2;
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
</style>
