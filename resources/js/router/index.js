import { createRouter, createWebHistory } from 'vue-router'

import Landing from '../pages/Landing.vue'
import Dashboard from '../pages/Dashboard.vue'
import UserIndex from '../pages/user/index.vue'

const routes = [
  { path: '/', component: Landing },
  { path: '/dashboard', component: Dashboard },
  {
    path: '/user',
    name: 'profile',
    component: UserIndex,
    meta: { requiresAuth: true }
  }

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const user = window.user

  if (to.meta.requiresAuth && !user) {
    return next('/login')
  }

  next()
})

export default router