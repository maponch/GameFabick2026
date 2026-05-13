import { createRouter, createWebHistory } from 'vue-router'
import { api } from '../api'

import Landing from '../pages/Landing.vue'
import Dashboard from '../pages/Dashboard.vue'
import UserIndex from '../pages/user/index.vue'

const routes = [
  { path: '/', component: Landing },
  { path: '/login', component: () => import('../pages/auth/Login.vue') },
  { path: '/register', component: () => import('../pages/auth/Register.vue') },
  { path: '/forgot-password', component: () => import('../pages/auth/ForgotPassword.vue') },
  { path: '/reset-password', component: () => import('../pages/auth/ResetPassword.vue') },
  { path: '/verify-email', component: () => import('../pages/auth/VerifyEmail.vue') },
  { path: '/restore-account', component: () => import('../pages/auth/RestoreAccount.vue') },
  { path: '/2fa-verify', component: () => import('../pages/auth/TwoFactorVerify.vue') },
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
  {
    path: '/admin',
    component: () => import('../pages/admin/index.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  {
    path: '/admin/users/:id',
    component: () => import('../pages/admin/UserProfile.vue'),
    meta: { requiresAuth: true, requiresAdmin: true }
  },
  { path: '/:pathMatch(.*)*', redirect: '/' } // 404 → accueil
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Vérifie l'auth via l'API, pas le localStorage
let currentUser = null

async function getUser() {
  if (currentUser) return currentUser
  try {
    await api.get('/sanctum/csrf-cookie')
    const { data } = await api.get('/user')
    currentUser = data
    return currentUser
  } catch {
    currentUser = null
    return null
  }
}

// Export pour réutilisation dans les composants
export function clearUser() { currentUser = null }
export { getUser }

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true

  const user = await getUser()

  // Non connecté → login
  if (!user) return { path: '/login' }

  if (!user.email_verified_at && to.path !== '/verify-email') {
    return { path: '/verify-email' }
  }

  // Page admin → vérification du rôle
  if (to.meta.requiresAdmin && user.role !== 'admin') return { path: '/dashboard' }

  return true
})

export default router