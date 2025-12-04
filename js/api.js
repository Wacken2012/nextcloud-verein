import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const instance = axios.create({
  baseURL: generateUrl('/apps/verein/'),
  withCredentials: true
})

// Transform plain objects to appropriate format based on endpoint
instance.interceptors.request.use(config => {
  if (config.data && typeof config.data === 'object' && !FormData.prototype.isPrototypeOf(config.data)) {
    // Für /api/v1/ Endpoints: JSON verwenden
    if (config.url && config.url.startsWith('api/v1/')) {
      config.headers['Content-Type'] = 'application/json'
      config.data = JSON.stringify(config.data)
    } else {
      // Für alte Endpoints: URL-encoded verwenden
      config.headers['Content-Type'] = 'application/x-www-form-urlencoded'
      const params = new URLSearchParams()
      for (const [key, value] of Object.entries(config.data)) {
        params.append(key, value ?? '')
      }
      config.data = params
    }
  }
  return config
})

// Add error handler
instance.interceptors.response.use(
  response => response,
  error => {
    console.error('API Error:', error.response?.data || error.message)
    return Promise.reject(error)
  }
)

export const api = {
  // Members
  getMembers() {
    return instance.get('members')
  },
  getMember(id) {
    return instance.get(`members/${id}`)
  },
  createMember(data) {
    return instance.post('members', data)
  },
  updateMember(id, data) {
    return instance.put(`members/${id}`, data)
  },
  deleteMember(id) {
    return instance.delete(`members/${id}`)
  },

  // Statistics
  getMemberStatistics() {
    return instance.get('statistics/members')
  },
  getFeeStatistics() {
    return instance.get('statistics/fees')
  },

  // Fees
  getFees() {
    return instance.get('finance')
  },
  getFee(id) {
    return instance.get(`finance/${id}`)
  },
  createFee(data) {
    return instance.post('finance', data)
  },
  updateFee(id, data) {
    return instance.put(`finance/${id}`, data)
  },
  deleteFee(id) {
    return instance.delete(`finance/${id}`)
  },

  // Reminders
  getReminderConfig() {
    return instance.get('api/v1/reminders/config')
  },
  saveReminderConfig(config) {
    return instance.post('api/v1/reminders/config', config)
  },
  getReminderLog() {
    return instance.get('api/v1/reminders/log')
  },
  processDueReminders() {
    return instance.post('api/v1/reminders/process', {})
  },

  // Roles
  getRoles() {
    return instance.get('api/v1/roles')
  },
  createRole(data) {
    return instance.post('api/v1/roles', data)
  },
  updateRole(id, data) {
    return instance.put(`api/v1/roles/${id}`, data)
  },
  deleteRole(id) {
    return instance.delete(`api/v1/roles/${id}`)
  },
  getPermissions() {
    return instance.get('api/v1/permissions')
  },
  updatePermissions(roleId, permissions) {
    return instance.put(`api/v1/roles/${roleId}/permissions`, { permissions })
  },

  // Privacy/GDPR
  exportMemberData(memberId) {
    return instance.get(`api/v1/privacy/export/${memberId}`)
  },
  deleteMemberData(memberId, mode = 'soft') {
    return instance.delete(`api/v1/privacy/member/${memberId}`, { data: { mode } })
  },
  saveConsent(memberId, consentType, agreed) {
    return instance.post('api/v1/privacy/consent', { memberId, consentType, agreed })
  },
  getConsentStatus(memberId) {
    return instance.get(`api/v1/privacy/consent/${memberId}`)
  },

  // Generic methods for component flexibility
  get(endpoint) {
    return instance.get(endpoint)
  },
  post(endpoint, data) {
    return instance.post(endpoint, data)
  },
  put(endpoint, data) {
    return instance.put(endpoint, data)
  },
  delete(endpoint) {
    return instance.delete(endpoint)
  }
}

export default api
