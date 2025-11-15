<template>
  <div id="verein-app" class="verein-app">
    <!-- Tabs Navigation -->
    <div class="verein-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="['verein-tab', { active: activeTab === tab.id }]"
        @click="activeTab = tab.id"
      >
        <i :class="`icon icon-${tab.icon}`"></i>
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab Content -->
    <div class="verein-content">
      <component
        :is="currentComponent"
        :key="activeTab"
      />
    </div>

    <!-- Notifications -->
    <div v-if="notification" :class="['verein-notification', notification.type]">
      {{ notification.message }}
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import Members from './Members.vue'
import Finance from './Finance.vue'
import Calendar from './Calendar.vue'
import Deck from './Deck.vue'
import Documents from './Documents.vue'

export default {
  name: 'App',
  components: {
    Members,
    Finance,
    Calendar,
    Deck,
    Documents
  },
  setup() {
    const activeTab = ref('members')
    const notification = ref(null)

    const tabs = [
      { id: 'members', label: 'Mitglieder', icon: 'users' },
      { id: 'finance', label: 'Finanzen', icon: 'finance' },
      { id: 'calendar', label: 'Termine', icon: 'calendar' },
      { id: 'deck', label: 'Aufgaben', icon: 'deck' },
      { id: 'documents', label: 'Dokumente', icon: 'documents' }
    ]

    const componentMap = {
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

    return {
      activeTab,
      tabs,
      notification,
      showNotification,
      get currentComponent() {
        return componentMap[activeTab.value]
      }
    }
  }
}
</script>

<style scoped lang="scss">
.verein-app {
  padding: 0;
  background: var(--color-main-background);
}

.verein-tabs {
  display: flex;
  gap: 0;
  border-bottom: 1px solid var(--color-border);
  background: var(--color-main-background);
  padding: 0;
  margin: 0;
  overflow-x: auto;

  .verein-tab {
    flex: 1;
    padding: 12px 16px;
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

    i {
      font-size: 16px;
    }

    &:hover {
      color: var(--color-text);
      background: var(--color-background-hover);
    }

    &.active {
      color: var(--color-primary);
      border-bottom-color: var(--color-primary);
    }
  }
}

.verein-content {
  padding: 20px;
  animation: fadeIn 0.2s ease;
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

  &.success {
    background: var(--color-success);
    color: white;
  }

  &.error {
    background: var(--color-error);
    color: white;
  }

  &.warning {
    background: var(--color-warning);
    color: white;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
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
</style>
