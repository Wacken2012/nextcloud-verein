<template>
  <div class="roles-page">
    <h2>Rollenverwaltung</h2>

    <div class="controls">
      <button @click="openCreate" class="button primary">➕ Neue Rolle</button>
    </div>

    <div v-if="showForm" class="modal-overlay">
      <div class="modal">
      <h3>{{ editingRole ? 'Rolle bearbeiten' : 'Neue Rolle' }}</h3>
      <form @submit.prevent="saveRole">
        <label for="name">Name *</label>
        <input id="name" v-model="form.name" required />

        <label for="description">Beschreibung</label>
        <input id="description" v-model="form.description" />

        <label>Berechtigungen</label>
        <div class="permissions-list">
          <div v-if="permissionsList.length === 0">Lade Berechtigungen...</div>
          <label v-for="perm in permissionsList" :key="perm" class="perm-item">
            <input type="checkbox" :value="(perm.key || perm)" v-model="form.permissions" />
            <span class="perm-name">{{ (perm.label || perm.name || perm) }}</span>
          </label>
        </div>

        <div class="form-actions">
          <button type="submit" class="button primary">Speichern</button>
          <button type="button" class="button" @click="closeForm">Abbrechen</button>
        </div>
      </form>
    </div>
  </div>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Beschreibung</th>
            <th>Berechtigungen</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="role in roles" :key="role.id">
            <td>{{ role.name }}</td>
            <td>{{ role.description || '-' }}</td>
            <td class="permissions"><small>{{ (role.permissions || []).join(', ') }}</small></td>
            <td class="actions">
              <button @click="editRole(role)" class="button-small">✏️</button>
              <button @click="deleteRole(role.id)" class="button-small danger">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Assign Role To User -->
    <div class="form-card">
      <h3>Rolle einem Benutzer zuweisen</h3>
      <div class="assign-row">
        <label for="assign-user">Benutzer</label>
        <input id="assign-user" v-model="assign.userQuery" @input="onAssignQueryInput" placeholder="Name oder E-Mail eingeben" autocomplete="off" />
        <select id="assign-user-select" v-model="assign.userId" size="6">
          <option value="">-- Benutzer wählen --</option>
          <option v-for="m in assign.searchResults" :key="m.id" :value="m.id">{{ m.name }} ({{ m.email }})</option>
        </select>

        <label for="assign-role">Rolle</label>
        <select id="assign-role" v-model="assign.roleId">
          <option value="">-- Rolle wählen --</option>
          <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
        </select>

        <label for="assign-club">Club ID (optional)</label>
        <input id="assign-club" v-model="assign.clubId" placeholder="z.B. 1" />

        <div class="form-actions">
          <button class="button primary" @click="assignRoleToUser">Zuweisen</button>
        </div>
      </div>
    </div>
    <!-- toasts -->
    <div v-if="toast.show" :class="['toast', toast.type]">{{ toast.message }}</div>
  </div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export default {
  name: 'Roles',
  data() {
    return {
      roles: [],
      permissionsList: [],
      permissionTemplates: [],
      // members are searched remotely for autocomplete
      showForm: false,
      editingRole: null,
      // toast state
      toast: {
        show: false,
        message: '',
        type: 'info'
      },
      form: {
        name: '',
        description: '',
        permissions: []
      },
      // assign role form
      assign: {
        userId: '',
        roleId: null,
        clubId: '',
        userQuery: '',
        // remote search results
        searchResults: [],
        // debounce timer
        searchTimer: null
      }
    }
  },
  mounted() {
    this.loadRoles()
    this.loadPermissions()
  },
  methods: {
    async loadRoles() {
      try {
        const res = await axios.get(generateUrl('/apps/verein/api/roles'))
        this.roles = res.data || []
      } catch (e) {
        console.error('Error loading roles', e)
        this.showToast('Fehler beim Laden der Rollen', 'error')
      }
    },
    async loadPermissions() {
      try {
        const res = await axios.get(generateUrl('/apps/verein/api/permissions'))
        const data = res.data || {}
        // API returns { permissions: [...], templates: [...] }
        this.permissionsList = data.permissions || []
        this.permissionTemplates = data.templates || []
      } catch (e) {
        console.error('Error loading permissions', e)
      }
    },
    async loadMembers() {
      // kept for backwards compatibility; prefer using searchMembers
      try {
        const res = await axios.get(generateUrl('/apps/verein/api/members'))
        this.assign.searchResults = Array.isArray(res.data) ? res.data : (res.data.members || [])
      } catch (e) {
        console.error('Error loading members', e)
      }
    },
    async searchMembers(query) {
      try {
        if (!query || query.trim() === '') {
          this.assign.searchResults = []
          return
        }
        const res = await axios.get(generateUrl('/apps/verein/api/members'), { params: { query } })
        const payload = res.data
        this.assign.searchResults = Array.isArray(payload) ? payload : (payload.members || [])
      } catch (e) {
        console.error('Error searching members', e)
        this.assign.searchResults = []
      }
    },
    onAssignQueryInput() {
      // debounce remote calls
      if (this.assign.searchTimer) clearTimeout(this.assign.searchTimer)
      const q = this.assign.userQuery
      this.assign.searchTimer = setTimeout(() => {
        this.searchMembers(q)
      }, 300)
    },
    openCreate() {
      this.editingRole = null
      this.form = { name: '', description: '', permissions: [] }
      this.showForm = true
    },
    closeForm() {
      this.showForm = false
      this.editingRole = null
    },
    async saveRole() {
      try {
        const payload = {
          name: this.form.name,
          description: this.form.description,
          permissions: Array.isArray(this.form.permissions) ? this.form.permissions : []
        }

        if (this.editingRole) {
          await axios.put(generateUrl(`/apps/verein/api/roles/${this.editingRole.id}`), payload)
          this.showToast('Rolle aktualisiert', 'success')
        } else {
          await axios.post(generateUrl('/apps/verein/api/roles'), payload)
          this.showToast('Rolle angelegt', 'success')
        }

        this.loadRoles()
        this.closeForm()
      } catch (e) {
        console.error('Error saving role', e)
        this.showToast('Fehler beim Speichern der Rolle', 'error')
      }
    },
    editRole(role) {
      this.editingRole = role
      this.form = {
        name: role.name,
        description: role.description || '',
        permissions: role.permissions || []
      }
      this.showForm = true
    },
    async assignRoleToUser() {
      if (!this.assign.userId || !this.assign.roleId) {
        this.showToast('Benutzer und Rolle erforderlich', 'error')
        return
      }
      try {
        const payload = {
          userId: this.assign.userId,
          roleId: this.assign.roleId,
          clubId: this.assign.clubId ? parseInt(this.assign.clubId) : 0
        }
        await axios.post(generateUrl('/apps/verein/api/roles/users'), payload)
        this.showToast('Rolle zugewiesen', 'success')
        // clear selection but keep search results
        this.assign.userId = ''
        this.assign.roleId = null
        this.assign.clubId = ''
        this.assign.userQuery = ''
      } catch (e) {
        console.error('Error assigning role', e)
        this.showToast('Fehler beim Zuweisen der Rolle', 'error')
      }
    },
    async deleteRole(id) {
      if (!confirm('Rolle wirklich löschen?')) return
      try {
        await axios.delete(generateUrl(`/apps/verein/api/roles/${id}`))
        this.showToast('Rolle gelöscht', 'success')
        this.loadRoles()
      } catch (e) {
        console.error('Error deleting role', e)
        this.showToast('Fehler beim Löschen der Rolle', 'error')
      }
    }
    ,
    showToast(message, type = 'info', timeout = 3000) {
      this.toast = { show: true, message, type }
      setTimeout(() => {
        this.toast.show = false
      }, timeout)
    }
  ,
  computed: {
    filteredAssignMembers() {
      // kept for compatibility; prefer assign.searchResults
      const q = (this.assign.userQuery || '').toLowerCase().trim()
      if (!q) return this.assign.searchResults
      return this.assign.searchResults.filter(m => {
        return (m.name || '').toLowerCase().includes(q) || (m.email || '').toLowerCase().includes(q)
      })
    }
  }
}
</script>

<style scoped>
.roles-page { padding: 20px }
.controls { margin-bottom: 12px }
.form-card, .table-card { background: var(--color-main-background); border: 1px solid var(--color-border); padding: 16px; border-radius: 6px; margin-bottom: 16px }
.form-card input { width: 100%; padding: 8px 10px; margin-bottom: 8px; border: 1px solid var(--color-border); border-radius: 4px }
.form-actions { display:flex; gap:8px }
.permissions { max-width: 420px }
.actions { display:flex; gap:8px }
.button-small { padding:6px 8px; border-radius:4px; border:none; cursor:pointer }
.button-small.danger { background: #f44336; color: white }

/* modal */
.modal-overlay { position: fixed; inset: 0; display:flex; align-items:center; justify-content:center; background: rgba(0,0,0,0.35); z-index: 1200 }
.modal { background: var(--color-main-background); border-radius:8px; padding:18px; width: 720px; max-width: calc(100% - 32px); box-shadow: 0 10px 30px rgba(0,0,0,0.25) }

/* toast */
.toast { position: fixed; right: 20px; bottom: 20px; padding: 10px 14px; border-radius: 6px; color: white; z-index: 1300 }
.toast.success { background: #4caf50 }
.toast.error { background: #f44336 }
.toast.info { background: #2196f3 }
</style>
