import { createRouter, createWebHistory } from 'vue-router'

import Landing from '../pages/Landing.vue'
import Dashboard from '../pages/Dashboard.vue'

const routes = [
  { path: '/', component: Landing },
  { path: '/dashboard', component: Dashboard },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router