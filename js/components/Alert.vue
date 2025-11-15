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

<style scoped lang="scss">
// Responsive Breakpoints
$breakpoint-mobile: 480px;

.fade-enter-active,
.fade-leave-active {
  transition: opacity var(--transition-base), transform var(--transition-base);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.alert {
  margin-bottom: 16px;
  border-radius: var(--border-radius-lg);
  box-shadow: var(--shadow-md);
  border-left: 4px solid transparent;
  overflow: hidden;

  @media (max-width: $breakpoint-mobile) {
    margin-bottom: 12px;
  }
}

.alert-content {
  display: flex;
  align-items: flex-start;
  padding: 14px 16px;
  gap: 12px;

  @media (max-width: $breakpoint-mobile) {
    padding: 12px 12px;
    gap: 10px;
  }
}

.alert-icon {
  flex-shrink: 0;
  font-size: 20px;
  line-height: 1.4;
  display: flex;
  align-items: center;
  justify-content: center;

  @media (max-width: $breakpoint-mobile) {
    font-size: 18px;
  }
}

.alert-text {
  flex: 1;
  min-width: 0; // Ermöglicht korrektes Wrapping
}

.alert-title {
  margin: 0 0 4px 0;
  font-weight: 600;
  font-size: 14px;
  line-height: 1.4;

  @media (max-width: $breakpoint-mobile) {
    font-size: 13px;
  }
}

.alert-message {
  margin: 0;
  font-size: 13px;
  line-height: 1.5;
  word-break: break-word;

  @media (max-width: $breakpoint-mobile) {
    font-size: 12px;
  }
}

.alert-errors {
  margin: 8px 0 0 0;
  padding-left: 20px;
  font-size: 12px;
  line-height: 1.4;

  @media (max-width: $breakpoint-mobile) {
    padding-left: 16px;
    font-size: 11px;
  }

  li {
    margin-bottom: 4px;
    word-break: break-word;

    &:last-child {
      margin-bottom: 0;
    }
  }
}

.alert-close {
  flex-shrink: 0;
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 0;
  color: currentColor;
  opacity: 0.6;
  transition: opacity var(--transition-fast);
  line-height: 1;

  @media (max-width: $breakpoint-mobile) {
    font-size: 16px;
  }

  &:hover {
    opacity: 1;
  }

  &:focus-visible {
    outline: 2px solid currentColor;
    outline-offset: 2px;
    opacity: 1;
  }
}

/* Typ-spezifische Styles - Light Mode */
.alert-error {
  background-color: var(--color-error-light, rgba(244, 67, 54, 0.15));
  border-left-color: var(--color-error, #f44336);
  color: var(--color-error, #c62828);

  .alert-title {
    color: var(--color-error, #c62828);
  }
}

.alert-success {
  background-color: var(--color-success-light, rgba(76, 175, 80, 0.15));
  border-left-color: var(--color-success, #4caf50);
  color: var(--color-success, #2e7d32);

  .alert-title {
    color: var(--color-success, #2e7d32);
  }
}

.alert-info {
  background-color: var(--color-info-light, rgba(33, 150, 243, 0.15));
  border-left-color: var(--color-info, #2196f3);
  color: var(--color-info, #1565c0);

  .alert-title {
    color: var(--color-info, #1565c0);
  }
}

.alert-warning {
  background-color: var(--color-warning-light, rgba(255, 193, 7, 0.15));
  border-left-color: var(--color-warning, #ffc107);
  color: var(--color-warning, #e65100);

  .alert-title {
    color: var(--color-warning, #e65100);
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .alert {
    background: var(--color-background, #2a2a2a);
  }

  .alert-error {
    background-color: var(--color-error-light, rgba(244, 67, 54, 0.25));
    color: #ff6b6b;

    .alert-title {
      color: #ff6b6b;
    }
  }

  .alert-success {
    background-color: var(--color-success-light, rgba(76, 175, 80, 0.25));
    color: #51cf66;

    .alert-title {
      color: #51cf66;
    }
  }

  .alert-info {
    background-color: var(--color-info-light, rgba(33, 150, 243, 0.25));
    color: #74c0fc;

    .alert-title {
      color: #74c0fc;
    }
  }

  .alert-warning {
    background-color: var(--color-warning-light, rgba(255, 193, 7, 0.25));
    color: #ffd43b;

    .alert-title {
      color: #ffd43b;
    }
  }

  .alert-close {
    color: var(--color-text-secondary, #b0b0b0);
  }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 150ms ease-in-out;
  }

  .alert-close {
    transition: none;
  }
}
</style>
