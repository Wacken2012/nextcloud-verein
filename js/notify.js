// Centralized notification utility for Nextcloud UI
// Prefers OCP.Toast (Nextcloud 26+) then OCP.notify/OC.Notification, with console fallback

function useToast() {
  return typeof window !== 'undefined' && window.OCP && window.OCP.Toast
}

function useNotify() {
  return typeof window !== 'undefined' && window.OCP && typeof window.OCP.notify === 'function'
}

function useLegacyOcNotification() {
  return typeof window !== 'undefined' && window.OC && window.OC.Notification
}

export function success(message) {
  if (useToast()) return window.OCP.Toast.success(message)
  if (useNotify()) return window.OCP.notify({ message, type: 'success' })
  if (useLegacyOcNotification()) return window.OC.Notification.showTemporary(message)
  // Fallback
  console.log('[SUCCESS]', message)
}

export function error(message) {
  if (useToast()) return window.OCP.Toast.error(message)
  if (useNotify()) return window.OCP.notify({ message, type: 'error' })
  if (useLegacyOcNotification()) return window.OC.Notification.showTemporary(message)
  // Fallback
  console.error('[ERROR]', message)
}

export function info(message) {
  if (useToast()) return window.OCP.Toast.show(message)
  if (useNotify()) return window.OCP.notify({ message, type: 'info' })
  if (useLegacyOcNotification()) return window.OC.Notification.showTemporary(message)
  // Fallback
  console.log('[INFO]', message)
}
