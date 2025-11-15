<template>
  <transition name="fade">
    <div v-if="show" :class="['alert', `alert-${type}`]">
      <div class="alert-content">
        <span class="alert-icon">
          <span v-if="type === 'error'" class="icon-error">⚠️</span>
          <span v-else-if="type === 'success'" class="icon-success">✓</span>
          <span v-else-if="type === 'info'" class="icon-info">ℹ️</span>
          <span v-else class="icon-warning">⚡</span>
        </span>
        <div class="alert-text">
          <p class="alert-title">{{ title }}</p>
          <p v-if="message" class="alert-message">{{ message }}</p>
          <ul v-if="errors.length > 0" class="alert-errors">
            <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
          </ul>
        </div>
        <button
          v-if="closeable"
          class="alert-close"
          @click="close"
          aria-label="Schließen"
        >
          ✕
        </button>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Props {
  type?: 'error' | 'success' | 'info' | 'warning'
  title?: string
  message?: string
  errors?: string[]
  duration?: number | null
  closeable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  title: 'Nachricht',
  message: '',
  errors: () => [],
  duration: 5000,
  closeable: true,
})

const show = ref(false)
let timeout: NodeJS.Timeout | null = null

const titleComputed = computed(() => {
  if (props.title) return props.title
  switch (props.type) {
    case 'error':
      return 'Fehler'
    case 'success':
      return 'Erfolg'
    case 'warning':
      return 'Warnung'
    default:
      return 'Information'
  }
})

const close = () => {
  show.value = false
  if (timeout) {
    clearTimeout(timeout)
    timeout = null
  }
}

const open = () => {
  show.value = true
  if (props.duration && props.duration > 0) {
    if (timeout) clearTimeout(timeout)
    timeout = setTimeout(() => {
      show.value = false
    }, props.duration)
  }
}

watch(
  () => props.message || props.errors.length,
  () => {
    if ((props.message || props.errors.length > 0) && !show.value) {
      open()
    }
  }
)

defineExpose({
  open,
  close,
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.alert {
  margin-bottom: 16px;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.alert-content {
  display: flex;
  align-items: flex-start;
  padding: 12px 16px;
  gap: 12px;
}

.alert-icon {
  flex-shrink: 0;
  font-size: 20px;
  line-height: 1.4;
}

.alert-text {
  flex: 1;
}

.alert-title {
  margin: 0 0 4px 0;
  font-weight: 600;
  font-size: 14px;
}

.alert-message {
  margin: 0;
  font-size: 13px;
  line-height: 1.5;
}

.alert-errors {
  margin: 8px 0 0 0;
  padding-left: 20px;
  font-size: 12px;
  line-height: 1.4;
}

.alert-errors li {
  margin-bottom: 4px;
}

.alert-close {
  flex-shrink: 0;
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.alert-close:hover {
  opacity: 1;
}

/* Typ-spezifische Styles */
.alert-error {
  background-color: #fee;
  border-left: 4px solid #d32f2f;
  color: #c62828;
}

.alert-success {
  background-color: #efe;
  border-left: 4px solid #388e3c;
  color: #2e7d32;
}

.alert-info {
  background-color: #eef;
  border-left: 4px solid #1976d2;
  color: #1565c0;
}

.alert-warning {
  background-color: #ffd;
  border-left: 4px solid #f57f17;
  color: #e65100;
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .alert-error {
    background-color: #3a1f1f;
    color: #ff6b6b;
  }

  .alert-success {
    background-color: #1f3a1f;
    color: #51cf66;
  }

  .alert-info {
    background-color: #1f2a3a;
    color: #74c0fc;
  }

  .alert-warning {
    background-color: #3a3a1f;
    color: #ffd43b;
  }
}
</style>
