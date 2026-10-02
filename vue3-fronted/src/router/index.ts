import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/auth/Login.vue'
import Register from '../views/auth/Register.vue'
import ForgotPassword from '../views/auth/ForgotPassword.vue'
import DashboardView from '../views/DashboardView.vue'
import VerifyEmailView from '../views/VerifyEmailView.vue'
import { useAuthStore } from '../store/Users/authStore'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/signin', name: 'signin', component: Login, meta: { guestOnly: true } },
  { path: '/login', redirect: '/signin' },
  { path: '/register', name: 'register', component: Register, meta: { guestOnly: true } },
  { path: '/forgot', name: 'forgot', component: ForgotPassword },
  { path: '/verify', name: 'verify', component: VerifyEmailView },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to: any, from: any, next: any) => {
  const authStore = useAuthStore()

  // If page requires auth and user is not authenticated
  if (to.meta?.requiresAuth && !authStore.isAuthenticated) {
    return next({ path: '/signin', query: { redirect: to.fullPath } })
  }

  // If route is guest-only and user is already authenticated
  if (to.meta?.guestOnly && authStore.isAuthenticated) {
    return next({ path: '/dashboard' })
  }

  next()
})

export default router
