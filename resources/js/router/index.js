import { createRouter, createWebHistory } from 'vue-router'

import Landing from '../pages/Landing.vue'
import Dashboard from '../pages/Dashboard.vue'
import UserIndex from '../pages/user/index.vue'

const routes = [
  { path: '/', component: Landing },
  {
    path: '/dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/user',
    name: 'profile',
    component: UserIndex,
    meta: { requiresAuth: true }
  },

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from) => {

  const isAuth = !!localStorage.getItem('user')

  if (to.meta.requiresAuth && !isAuth) {
    window.location.href = '/login'
    return
  }
})

export default router