import { createRouter, createWebHistory } from 'vue-router'
import MemberList from './components/MemberList.vue'
import FeeList from './components/FeeList.vue'
import SepaExport from './components/SepaExport.vue'
import Roles from './components/Roles.vue'
import Settings from './components/Settings.vue'
import RolesManager from './components/RolesManager.vue'
import ReminderSettings from './components/ReminderSettings.vue'
import ReminderLog from './components/ReminderLog.vue'
import PrivacySettings from './components/PrivacySettings.vue'

const routes = [
  {
    path: '/',
    redirect: '/members'
  },
  {
    path: '/members',
    name: 'Members',
    component: MemberList
  },
  {
    path: '/fees',
    name: 'Fees',
    component: FeeList
  },
  {
    path: '/sepa',
    name: 'Sepa',
    component: SepaExport
  }
  ,
  {
    path: '/roles',
    name: 'Roles',
    component: Roles
  }
  ,
  {
    path: '/settings',
    name: 'Settings',
    component: Settings
  },
  {
    path: '/settings/roles',
    name: 'RolesManager',
    component: RolesManager
  },
  {
    path: '/settings/reminders',
    name: 'ReminderSettings',
    component: ReminderSettings
  },
  {
    path: '/settings/reminders/log',
    name: 'ReminderLog',
    component: ReminderLog
  },
  {
    path: '/settings/privacy',
    name: 'PrivacySettings',
    component: PrivacySettings
  }
]

const router = createRouter({
  history: createWebHistory('/apps/verein'),
  routes
})

export default router
