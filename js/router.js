import { createRouter, createWebHistory } from 'vue-router'
import MemberList from './components/MemberList.vue'
import FeeList from './components/FeeList.vue'
import SepaExport from './components/SepaExport.vue'
import Roles from './components/Roles.vue'
import Settings from './components/Settings.vue'

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
  }
]

const router = createRouter({
  history: createWebHistory('/apps/verein'),
  routes
})

export default router
