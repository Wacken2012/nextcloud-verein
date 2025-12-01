<template>
  <div class="roles-manager">
    <div class="roles-header">
      <h3>{{ $t('roles.title', 'Rollenverwaltung') }}</h3>
      <p class="description">{{ $t('roles.description', 'Verwalten Sie Rollen und deren Berechtigungen') }}</p>
    </div>

    <!-- Tabs: Roles & Permissions -->
    <div class="roles-tabs">
      <button 
        :class="['tab', { active: activeTab === 'roles' }]"
        @click="activeTab = 'roles'"
      >
        {{ $t('roles.tabs.roles', 'Rollen') }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'permissions' }]"
        @click="activeTab = 'permissions'"
      >
        {{ $t('roles.tabs.permissions', 'Berechtigungen') }}
      </button>
      <button 
        :class="['tab', { active: activeTab === 'members' }]"
        @click="activeTab = 'members'"
      >
        {{ $t('roles.tabs.members', 'Mitgliedszuordnung') }}
      </button>
    </div>

    <!-- Rollen-Übersicht Tab -->
    <div v-show="activeTab === 'roles'" class="tab-content roles-tab">
      <div class="roles-actions">
        <button class="btn btn-primary" @click="showNewRoleDialog = true">
          + {{ $t('roles.addRole', 'Neue Rolle hinzufügen') }}
        </button>
      </div>

      <div class="roles-grid">
        <div v-for="role in roles" :key="role.id" class="role-card">
          <div class="role-header">
            <h4>{{ role.name }}</h4>
            <div class="role-actions">
              <button 
                class="btn-icon" 
                @click="editRole(role)"
                :title="$t('common.edit', 'Bearbeiten')"
              >
                ✎
              </button>
              <button 
                class="btn-icon btn-danger" 
                @click="deleteRole(role.id)"
                :title="$t('common.delete', 'Löschen')"
                v-if="!role.isSystem"
              >
                ✕
              </button>
            </div>
          </div>
          <p class="role-description">{{ role.description }}</p>
          <div class="role-meta">
            <span class="badge" v-if="role.isSystem">{{ $t('roles.system', 'System') }}</span>
            <span class="badge badge-secondary">
              {{ role.memberCount || 0 }} {{ $t('roles.members', 'Mitglieder') }}
            </span>
          </div>
          <div class="role-permissions-preview">
            <span v-for="perm in role.permissions.slice(0, 3)" :key="perm" class="perm-badge">
              {{ getPermissionLabel(perm) }}
            </span>
            <span v-if="role.permissions.length > 3" class="perm-badge more">
              +{{ role.permissions.length - 3 }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Permissions Matrix Tab -->
    <div v-show="activeTab === 'permissions'" class="tab-content permissions-tab">
      <div class="permissions-controls">
        <div class="search-box">
          <input 
            v-model="permissionFilter"
            type="text"
            :placeholder="$t('roles.filterPermissions', 'Berechtigungen filtern...')"
            class="filter-input"
          />
        </div>
      </div>

      <div class="permissions-matrix">
        <table>
          <thead>
            <tr>
              <th class="role-col">{{ $t('roles.role', 'Rolle') }}</th>
              <th v-for="perm in filteredPermissions" :key="perm.id" class="perm-col">
                <span class="perm-name" :title="perm.description">{{ perm.shortName }}</span>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles" :key="role.id">
              <td class="role-col">
                <strong>{{ role.name }}</strong>
              </td>
              <td v-for="perm in filteredPermissions" :key="perm.id" class="perm-cell">
                <input 
                  type="checkbox"
                  :checked="hasPermission(role.id, perm.id)"
                  @change="togglePermission(role.id, perm.id, $event)"
                  :disabled="role.isSystem"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="permissions-legend">
        <h4>{{ $t('roles.legend', 'Erklärung der Berechtigungen') }}</h4>
        <div class="legend-items">
          <div v-for="perm in allPermissions" :key="perm.id" class="legend-item">
            <strong>{{ perm.name }}</strong>
            <p>{{ perm.description }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Mitgliedszuordnung Tab -->
    <div v-show="activeTab === 'members'" class="tab-content members-tab">
      <div class="bulk-actions">
        <div class="action-group">
          <label>{{ $t('roles.bulkAssign.title', 'Mehrfachzuweisung') }}</label>
          <div class="bulk-controls">
            <select v-model="bulkRole" class="select-input">
              <option value="">{{ $t('roles.selectRole', 'Rolle wählen...') }}</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.name }}
              </option>
            </select>
            <button 
              class="btn btn-secondary"
              @click="showBulkMemberSelection = true"
              :disabled="!bulkRole"
            >
              {{ $t('roles.selectMembers', 'Mitglieder wählen...') }}
            </button>
          </div>
        </div>
      </div>

      <div class="members-list">
        <h4>{{ $t('roles.memberRoles', 'Mitgliederzuordnung') }}</h4>
        <table class="members-table">
          <thead>
            <tr>
              <th>{{ $t('common.member', 'Mitglied') }}</th>
              <th>{{ $t('roles.roles', 'Rollen') }}</th>
              <th class="actions-col">{{ $t('common.actions', 'Aktionen') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="member in members" :key="member.id" class="member-row">
              <td>
                <div class="member-info">
                  <strong>{{ member.firstName }} {{ member.lastName }}</strong>
                  <small>{{ member.email }}</small>
                </div>
              </td>
              <td>
                <div class="member-roles">
                  <span v-for="role in getMemberRoles(member.id)" :key="role.id" class="role-badge">
                    {{ role.name }}
                    <button 
                      class="remove-btn"
                      @click="removeMemberRole(member.id, role.id)"
                      :title="$t('common.remove', 'Entfernen')"
                    >
                      ✕
                    </button>
                  </span>
                  <button 
                    class="btn-add-role"
                    @click="showAddRoleDialog(member.id)"
                  >
                    + {{ $t('roles.addRole', 'Rolle hinzufügen') }}
                  </button>
                </div>
              </td>
              <td class="actions-col">
                <button 
                  class="btn-icon"
                  @click="editMember(member)"
                  :title="$t('common.edit', 'Bearbeiten')"
                >
                  ✎
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Dialoge -->
    <div v-if="showNewRoleDialog" class="dialog-overlay" @click.self="showNewRoleDialog = false">
      <div class="dialog">
        <h3>{{ $t('roles.newRole', 'Neue Rolle') }}</h3>
        <form @submit.prevent="saveRole">
          <div class="form-group">
            <label>{{ $t('roles.name', 'Name') }}</label>
            <input v-model="newRole.name" type="text" required class="form-input" />
          </div>
          <div class="form-group">
            <label>{{ $t('roles.description', 'Beschreibung') }}</label>
            <textarea v-model="newRole.description" class="form-textarea"></textarea>
          </div>
          <div class="dialog-actions">
            <button type="button" class="btn btn-secondary" @click="showNewRoleDialog = false">
              {{ $t('common.cancel', 'Abbrechen') }}
            </button>
            <button type="submit" class="btn btn-primary">
              {{ $t('common.save', 'Speichern') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Erfolgs/Fehler Meldungen -->
    <div v-if="message" :class="['message', message.type]">
      {{ message.text }}
    </div>
  </div>
</template>

<script>
import { api } from '@/api';

export default {
  name: 'RolesManager',
  data() {
    return {
      activeTab: 'roles',
      roles: [],
      members: [],
      allPermissions: [],
      permissionFilter: '',
      showNewRoleDialog: false,
      showBulkMemberSelection: false,
      showAddRoleDialog: false,
      currentMemberId: null,
      bulkRole: '',
      newRole: {
        name: '',
        description: '',
      },
      message: null,
      loading: true,
    };
  },

  computed: {
    filteredPermissions() {
      return this.allPermissions.filter(p =>
        p.name.toLowerCase().includes(this.permissionFilter.toLowerCase()) ||
        p.description.toLowerCase().includes(this.permissionFilter.toLowerCase())
      );
    },
  },

  mounted() {
    this.loadData();
  },

  methods: {
    async loadData() {
      try {
        this.loading = true;
        const [rolesRes, membersRes, permsRes] = await Promise.all([
          api.get('/roles'),
          api.get('/members'),
          api.get('/permissions'),
        ]);
        this.roles = rolesRes.data || [];
        this.members = membersRes.data || [];
        this.allPermissions = permsRes.data || [];
      } catch (error) {
        console.error('Error loading data:', error);
        this.showMessage(this.$t('common.error.load', 'Fehler beim Laden'), 'error');
      } finally {
        this.loading = false;
      }
    },

    async saveRole() {
      try {
        await api.post('/roles', this.newRole);
        this.newRole = { name: '', description: '' };
        this.showNewRoleDialog = false;
        this.showMessage(this.$t('roles.saved', 'Rolle gespeichert'), 'success');
        await this.loadData();
      } catch (error) {
        console.error('Error saving role:', error);
        this.showMessage(this.$t('common.error.save', 'Fehler beim Speichern'), 'error');
      }
    },

    async deleteRole(roleId) {
      if (!confirm(this.$t('roles.confirmDelete', 'Wirklich löschen?'))) return;
      try {
        await api.delete(`/roles/${roleId}`);
        this.showMessage(this.$t('roles.deleted', 'Rolle gelöscht'), 'success');
        await this.loadData();
      } catch (error) {
        console.error('Error deleting role:', error);
        this.showMessage(this.$t('common.error.delete', 'Fehler beim Löschen'), 'error');
      }
    },

    editRole(role) {
      this.newRole = { ...role };
      this.showNewRoleDialog = true;
    },

    hasPermission(roleId, permId) {
      const role = this.roles.find(r => r.id === roleId);
      return role && role.permissions.includes(permId);
    },

    async togglePermission(roleId, permId, event) {
      const hasIt = event.target.checked;
      try {
        const action = hasIt ? 'grant' : 'revoke';
        await api.post(`/roles/${roleId}/permissions/${permId}/${action}`);
        await this.loadData();
      } catch (error) {
        console.error('Error toggling permission:', error);
        this.showMessage(this.$t('common.error.update', 'Fehler beim Aktualisieren'), 'error');
      }
    },

    getMemberRoles(memberId) {
      const member = this.members.find(m => m.id === memberId);
      return member ? member.roles || [] : [];
    },

    showAddRoleDialog(memberId) {
      this.currentMemberId = memberId;
      this.showAddRoleDialog = true;
    },

    async removeMemberRole(memberId, roleId) {
      try {
        await api.delete(`/members/${memberId}/roles/${roleId}`);
        this.showMessage(this.$t('roles.removed', 'Rolle entfernt'), 'success');
        await this.loadData();
      } catch (error) {
        console.error('Error removing role:', error);
        this.showMessage(this.$t('common.error.update', 'Fehler beim Aktualisieren'), 'error');
      }
    },

    editMember(member) {
      this.$emit('edit-member', member);
    },

    getPermissionLabel(permId) {
      const perm = this.allPermissions.find(p => p.id === permId);
      return perm ? perm.shortName : permId;
    },

    showMessage(text, type = 'info') {
      this.message = { text, type };
      setTimeout(() => {
        this.message = null;
      }, 3000);
    },
  },
};
</script>

<style scoped lang="scss">
.roles-manager {
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  margin: 20px 0;

  .roles-header {
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

  .roles-tabs {
    display: flex;
    gap: 10px;
    border-bottom: 2px solid #ddd;
    margin-bottom: 20px;

    .tab {
      padding: 12px 20px;
      border: none;
      background: none;
      color: #666;
      font-weight: 500;
      cursor: pointer;
      border-bottom: 3px solid transparent;
      transition: all 0.2s;

      &:hover {
        color: #333;
        background: rgba(0, 0, 0, 0.02);
      }

      &.active {
        color: #1e88e5;
        border-bottom-color: #1e88e5;
      }
    }
  }

  .tab-content {
    animation: fadeIn 0.2s;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;

    .role-card {
      background: white;
      padding: 20px;
      border-radius: 6px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      transition: box-shadow 0.2s;

      &:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      }

      .role-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;

        h4 {
          font-size: 1.1em;
          font-weight: 600;
          color: #333;
          margin: 0;
          flex: 1;
        }

        .role-actions {
          display: flex;
          gap: 5px;

          .btn-icon {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 1.2em;
            padding: 5px;

            &:hover {
              color: #333;
            }

            &.btn-danger:hover {
              color: #d32f2f;
            }
          }
        }
      }

      .role-description {
        color: #666;
        margin: 0 0 15px 0;
        font-size: 0.9em;
      }

      .role-meta {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;

        .badge {
          display: inline-block;
          padding: 4px 12px;
          background: #e3f2fd;
          color: #1565c0;
          border-radius: 12px;
          font-size: 0.8em;
          font-weight: 500;

          &.badge-secondary {
            background: #f5f5f5;
            color: #666;
          }
        }
      }

      .role-permissions-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;

        .perm-badge {
          display: inline-block;
          padding: 3px 10px;
          background: #f5f5f5;
          color: #666;
          border-radius: 4px;
          font-size: 0.8em;

          &.more {
            background: #fff3e0;
            color: #e65100;
            font-weight: 500;
          }
        }
      }
    }
  }

  .permissions-matrix {
    background: white;
    border-radius: 6px;
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    table {
      width: 100%;
      border-collapse: collapse;

      thead {
        background: #f5f5f5;
        border-bottom: 2px solid #ddd;

        th {
          padding: 15px 10px;
          text-align: left;
          font-weight: 600;
          color: #333;
          font-size: 0.9em;

          &.role-col {
            min-width: 150px;
          }

          &.perm-col {
            min-width: 60px;
            text-align: center;

            .perm-name {
              writing-mode: vertical-rl;
              transform: rotate(180deg);
              display: inline-block;
              font-size: 0.8em;
            }
          }
        }
      }

      tbody {
        tr {
          border-bottom: 1px solid #eee;

          &:hover {
            background: #f9f9f9;
          }

          td {
            padding: 15px 10px;
            font-size: 0.9em;

            &.role-col {
              font-weight: 500;
              color: #333;
            }

            &.perm-cell {
              text-align: center;

              input[type="checkbox"] {
                cursor: pointer;

                &:disabled {
                  opacity: 0.5;
                  cursor: not-allowed;
                }
              }
            }
          }
        }
      }
    }
  }

  .permissions-legend {
    margin-top: 30px;
    background: white;
    padding: 20px;
    border-radius: 6px;

    h4 {
      font-weight: 600;
      color: #333;
      margin: 0 0 15px 0;
    }

    .legend-items {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;

      .legend-item {
        p {
          margin: 5px 0 0 0;
          color: #666;
          font-size: 0.9em;
        }
      }
    }
  }

  .members-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    thead {
      background: #f5f5f5;

      th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;

        &.actions-col {
          text-align: center;
          width: 60px;
        }
      }
    }

    tbody {
      tr {
        border-bottom: 1px solid #eee;

        &:hover {
          background: #f9f9f9;
        }

        td {
          padding: 15px;

          .member-info {
            display: flex;
            flex-direction: column;

            strong {
              color: #333;
            }

            small {
              color: #999;
              font-size: 0.85em;
              margin-top: 3px;
            }
          }

          .member-roles {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;

            .role-badge {
              display: inline-flex;
              align-items: center;
              gap: 8px;
              padding: 4px 12px;
              background: #e3f2fd;
              color: #1565c0;
              border-radius: 12px;
              font-size: 0.9em;
              font-weight: 500;

              .remove-btn {
                background: none;
                border: none;
                color: inherit;
                cursor: pointer;
                font-size: 1.1em;
                padding: 0;
                margin-left: 4px;

                &:hover {
                  opacity: 0.7;
                }
              }
            }

            .btn-add-role {
              background: none;
              border: 1px dashed #ddd;
              color: #666;
              padding: 4px 12px;
              border-radius: 4px;
              cursor: pointer;
              font-size: 0.9em;

              &:hover {
                border-color: #1e88e5;
                color: #1e88e5;
              }
            }
          }

          &.actions-col {
            text-align: center;
          }
        }
      }
    }
  }

  .dialog-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;

    .dialog {
      background: white;
      padding: 30px;
      border-radius: 6px;
      max-width: 500px;
      width: 90%;

      h3 {
        margin: 0 0 20px 0;
        color: #333;
      }

      .form-group {
        margin-bottom: 20px;

        label {
          display: block;
          margin-bottom: 8px;
          font-weight: 500;
          color: #333;
        }

        .form-input,
        .form-textarea {
          width: 100%;
          padding: 10px;
          border: 1px solid #ddd;
          border-radius: 4px;
          font-size: 0.95em;

          &:focus {
            outline: none;
            border-color: #1e88e5;
            box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
          }
        }

        .form-textarea {
          resize: vertical;
          min-height: 100px;
        }
      }

      .dialog-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 30px;
      }
    }
  }

  .message {
    padding: 15px;
    border-radius: 4px;
    margin-top: 20px;
    animation: slideIn 0.3s;

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

    &.info {
      background: #e3f2fd;
      color: #1565c0;
      border-left: 4px solid #2196f3;
    }
  }

  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    font-size: 0.95em;
    transition: all 0.2s;

    &.btn-primary {
      background: #1e88e5;
      color: white;

      &:hover:not(:disabled) {
        background: #1565c0;
      }
    }

    &.btn-secondary {
      background: #f5f5f5;
      color: #333;
      border: 1px solid #ddd;

      &:hover:not(:disabled) {
        background: #e0e0e0;
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }
    }
  }

  .select-input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95em;

    &:focus {
      outline: none;
      border-color: #1e88e5;
      box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
    }
  }

  .bulk-actions {
    background: white;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    .action-group {
      label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
      }

      .bulk-controls {
        display: flex;
        gap: 10px;

        .select-input {
          flex: 1;
          min-width: 200px;
        }
      }
    }
  }

  .permissions-controls {
    background: white;
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    .search-box {
      .filter-input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.95em;

        &:focus {
          outline: none;
          border-color: #1e88e5;
          box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
        }
      }
    }
  }

  .roles-actions {
    margin-bottom: 20px;

    .btn {
      &.btn-primary {
        background: #4caf50;

        &:hover {
          background: #388e3c;
        }
      }
    }
  }
}

@media (max-width: 768px) {
  .roles-manager {
    padding: 15px;

    .roles-grid {
      grid-template-columns: 1fr;
    }

    .permissions-matrix table {
      font-size: 0.85em;

      th, td {
        padding: 10px 5px;
      }
    }

    .members-table th, .members-table td {
      padding: 12px;
    }
  }
}
</style>
