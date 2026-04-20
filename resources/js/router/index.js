import { createRouter, createWebHistory } from 'vue-router'

import Landing from '../pages/Landing.vue'
import Dashboard from '../pages/Dashboard.vue'
import UserIndex from '../pages/user/index.vue'

const routes = [
  { path: '/', component: Landing },
  { path: '/dashboard', component: Dashboard },
  { path: '/user', component: UserIndex  },

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router