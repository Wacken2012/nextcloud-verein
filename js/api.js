import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

const instance = axios.create({
  baseURL: generateUrl('/apps/verein/'),
  withCredentials: true
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

  // Finance
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
