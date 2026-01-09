import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/bookings',
      name: 'bookings',
      component: () => import('../views/MyBookingsView.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  // Always try to fetch user if not loaded
  if (!auth.user && !auth.loading) {
    await auth.fetchUser()
  }

  // If route requires guest and user is authenticated, redirect to home
  if (to.meta.requiresGuest && auth.isAuthenticated) {
    return next('/')
  }

  // If route requires auth and user is not authenticated, redirect to login
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  // Otherwise, proceed
  return next()
})

export default router
