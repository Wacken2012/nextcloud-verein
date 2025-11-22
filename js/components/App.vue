<template>
  <div id="verein-app" class="verein-app">
    <!-- Tabs Navigation -->
    <nav id="verein-navigation" class="verein-tabs" role="navigation" aria-label="Hauptnavigation" tabindex="-1">
      <div class="verein-tabs-container" role="tablist" aria-label="Hauptnavigation">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="['verein-tab', { active: activeTab === tab.id }]"
          :id="'verein-tab-' + tab.id"
          :aria-current="activeTab === tab.id ? 'page' : false"
          @click="activeTab = tab.id"
          role="tab"
          aria-controls="app-content"
          :aria-selected="activeTab === tab.id"
          :tabindex="activeTab === tab.id ? 0 : -1"
          @keydown="onKeyDown($event, tab)"
        >
          <span :class="['verein-tab-icon', 'icon-' + tab.icon]"></span>
          <span class="verein-tab-label">{{ tab.label }}</span>
        </button>
      </div>
    </nav>

    <!-- Tab Content -->
    <main class="verein-content-wrapper">
      <div id="verein-tab-panel" role="region" :aria-labelledby="'verein-tab-' + activeTab">
      <div class="verein-container">
        <component
          :is="currentComponent"
          :key="activeTab"
          @navigate="(tab) => { activeTab = tab }"
        />
      </div>
      </div>
    </main>

    <!-- Notifications -->
    <div v-if="notification" :class="['verein-notification', notification.type]" role="alert">
      {{ notification.message }}
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import Members from './Members.vue'
import Finance from './Finance.vue'
import Statistics from './Statistics.vue'
import Calendar from './Calendar.vue'
import Deck from './Deck.vue'
import Documents from './Documents.vue'

export default {
  name: 'App',
  components: {
    Members,
    Finance,
    Statistics,
    Calendar,
    Deck,
    Documents
  },
  setup() {
    const activeTab = ref('dashboard')
    const notification = ref(null)

    const tabs = [
      { id: 'dashboard', label: 'Dashboard', icon: 'dashboard' },
      { id: 'members', label: 'Mitglieder', icon: 'users' },
      { id: 'finance', label: 'Finanzen', icon: 'finance' },
      { id: 'calendar', label: 'Termine', icon: 'calendar' },
      { id: 'deck', label: 'Aufgaben', icon: 'deck' },
      { id: 'documents', label: 'Dokumente', icon: 'documents' }
    ]

    const componentMap = {
      dashboard: 'Statistics',
      members: 'Members',
      finance: 'Finance',
      calendar: 'Calendar',
      deck: 'Deck',
      documents: 'Documents'
    }

    const showNotification = (message, type = 'success') => {
      notification.value = { message, type }
      setTimeout(() => {
        notification.value = null
      }, 3000)
    }

    const currentComponent = computed(() => {
      return componentMap[activeTab.value]
    })

    const onKeyDown = (event, tab) => {
      const index = tabs.findIndex(t => t.id === tab.id)
      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
        const next = (index + 1) % tabs.length
        activeTab.value = tabs[next].id
        setTimeout(() => {
          const nodes = document.querySelectorAll('.verein-tab')
          if (nodes[next]) nodes[next].focus()
        }, 0)
        event.preventDefault()
      } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
        const prev = (index - 1 + tabs.length) % tabs.length
        activeTab.value = tabs[prev].id
        setTimeout(() => {
          const nodes = document.querySelectorAll('.verein-tab')
          if (nodes[prev]) nodes[prev].focus()
        }, 0)
        event.preventDefault()
      } else if (event.key === 'Enter' || event.key === ' ') {
        activeTab.value = tab.id
        event.preventDefault()
      }
    }

    return {
      activeTab,
      tabs,
      notification,
      showNotification,
      currentComponent
      ,onKeyDown
    }
  }
}
</script>

<style scoped lang="scss">
// Responsive Breakpoints
$breakpoint-tablet: 768px;
$breakpoint-desktop: 1024px;
$max-container-width: 1200px; // retained for fallback but not enforced for full-width layout

::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--color-background);
}

::-webkit-scrollbar-thumb {
  background: var(--color-border);
  border-radius: 4px;

  &:hover {
    background: var(--color-text-secondary);
  }
}

.verein-app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--color-main-background);
  color: var(--color-text);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.verein-tabs {
  background: var(--color-main-background);
  border-bottom: 1px solid var(--color-border);
  position: sticky;
  top: var(--header-height, 50px);
  z-index: 100;
  z-index: 2100; /* place tabs above header area so they remain clickable */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.verein-tabs-container {
  display: flex;
  gap: 0;
  /* allow the tab bar to use the full available width inside Nextcloud's content area
     but keep a small horizontal padding so it doesn't touch browser edges */
  max-width: none;
  margin: 0 20px;
  width: calc(100% - 40px);
  padding: 0;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;

  @media (max-width: $breakpoint-tablet) {
    margin: 0;
    width: 100%;
    overflow-x: auto;
    scroll-behavior: smooth;
  }
}

.verein-tab {
  flex: 0 0 auto;
  padding: 14px 16px;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  color: var(--color-text-secondary);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
  transition: all 0.2s ease;
  -webkit-user-select: none;
  user-select: none;

  @media (max-width: $breakpoint-tablet) {
    padding: 12px 12px;
    font-size: 13px;
    gap: 6px;
  }

  @media (max-width: 480px) {
    padding: 10px 8px;
    font-size: 12px;
    gap: 4px;

    .verein-tab-label {
      display: none;
    }
  }

  .verein-tab-icon {
    font-size: 16px;
    display: inline-flex;
    align-items: center;

    @media (max-width: $breakpoint-tablet) {
      font-size: 15px;
    }
  }

  &:hover {
    color: var(--color-text);
    background: var(--color-background-hover);
  }

  &:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: -2px;
  }

  &.active {
    color: var(--color-primary);
    border-bottom-color: var(--color-primary);
    background: var(--color-background-hover);
  }
}

.verein-content-wrapper {
  flex: 1;
  display: flex;
  width: 100%;
  background: var(--color-main-background);
}

.verein-container {
  /* expand to use the available content width inside Nextcloud while
     keeping a comfortable gutter */
  width: calc(100% - 48px);
  max-width: none;
  margin: 0 24px;
  padding: 1rem;
  display: flex;
  flex-direction: column;

  @media (max-width: $breakpoint-tablet) {
    width: 100%;
    margin: 0;
    padding: 1rem 0.75rem;
  }

  @media (max-width: 480px) {
    padding: 0.75rem 0.5rem;
  }
}

.verein-notification {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 12px 16px;
  border-radius: 4px;
  font-size: 14px;
  z-index: 1000;
  animation: slideIn 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  max-width: calc(100% - 40px);

  @media (max-width: 480px) {
    bottom: 16px;
    right: 16px;
    left: 16px;
    max-width: none;
  }

  &.success {
    background: var(--color-success, #388e3c);
    color: white;
  }

  &.error {
    background: var(--color-error, #d32f2f);
    color: white;
  }

  &.warning {
    background: var(--color-warning, #f57f17);
    color: white;
  }

  &.info {
    background: var(--color-info, #1976d2);
    color: white;
  }
}

@keyframes slideIn {
  from {
    transform: translateX(20px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

// Dark Mode Support - Nextcloud CSS Variables
@media (prefers-color-scheme: dark) {
  .verein-app {
    background: var(--color-main-background, #222);
    color: var(--color-text, #fff);
  }

  .verein-tabs {
    background: var(--color-main-background, #222);
  }

  .verein-tab {
    &:hover {
      background: var(--color-background-hover, rgba(255, 255, 255, 0.05));
    }

    &.active {
      background: var(--color-background-hover, rgba(255, 255, 255, 0.05));
    }
  }
}
</style>

