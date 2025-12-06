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
    return instance.get('api/members')
  },
  getMember(id) {
    return instance.get(`api/members/${id}`)
  },
  createMember(data) {
    return instance.post('api/members', data)
  },
  updateMember(id, data) {
    return instance.put(`api/members/${id}`, data)
  },
  deleteMember(id) {
    return instance.delete(`api/members/${id}`)
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
    return instance.get('api/finance')
  },
  getFee(id) {
    return instance.get(`api/finance/${id}`)
  },
  createFee(data) {
    return instance.post('api/finance', data)
  },
  updateFee(id, data) {
    return instance.put(`api/finance/${id}`, data)
  },
  deleteFee(id) {
    return instance.delete(`api/finance/${id}`)
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
  getPrivacyPolicy() {
    return instance.get('api/v1/privacy/policy')
  },
  savePrivacyPolicy(policyText) {
    return instance.put('api/v1/privacy/policy', { policy: policyText })
  },
  exportMemberData(memberId) {
    return instance.get(`api/v1/privacy/export/${memberId}`)
  },
  deleteMemberData(memberId, mode = 'soft') {
    return instance.delete(`api/v1/privacy/member/${memberId}`, { data: { mode } })
  },
  canDelete(memberId) {
    return instance.get(`api/v1/privacy/can-delete/${memberId}`)
  },
  saveConsent(memberId, consentType, agreed) {
    return instance.post('api/v1/privacy/consent', { memberId, consentType, agreed })
  },
  getConsentStatus(memberId) {
    return instance.get(`api/v1/privacy/consent/${memberId}`)
  },
  getConsentTypes() {
    return instance.get('api/v1/privacy/consent-types')
  },
  saveConsentsBulk(memberId, consents) {
    return instance.post(`api/v1/privacy/consent/${memberId}/bulk`, { consents })
  },
  getAuditLog(memberId, limit = 100) {
    return instance.get(`api/v1/privacy/audit-log/${memberId}`, { params: { limit } })
  },
  getAuditStatistics() {
    return instance.get('api/v1/privacy/audit-statistics')
  },

  // Calendar API (v0.3.0)
  getEvents(params = {}) {
    return instance.get('api/v1/calendar/events', { params })
  },
  getEvent(id) {
    return instance.get(`api/v1/calendar/events/${id}`)
  },
  createEvent(data) {
    return instance.post('api/v1/calendar/events', data)
  },
  updateEvent(id, data) {
    return instance.put(`api/v1/calendar/events/${id}`, data)
  },
  deleteEvent(id) {
    return instance.delete(`api/v1/calendar/events/${id}`)
  },
  getEventRsvp(eventId) {
    return instance.get(`api/v1/calendar/events/${eventId}/rsvp`)
  },
  getMyRsvp(eventId) {
    return instance.get(`api/v1/calendar/events/${eventId}/my-rsvp`)
  },
  getPendingRsvp() {
    return instance.get('api/v1/calendar/pending-rsvp')
  },
  setEventRsvp(eventId, response, comment = '') {
    return instance.post(`api/v1/calendar/events/${eventId}/rsvp`, { response, comment })
  },
  getUpcomingEvents(limit = 10) {
    return instance.get('api/v1/calendar/upcoming', { params: { limit } })
  },
  getEventTypes() {
    return instance.get('api/v1/calendar/types')
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
