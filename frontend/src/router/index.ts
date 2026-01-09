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
    {
      path: '/admin',
      component: () => import('@/layouts/AdminLayout.vue'),
      meta: { requiresAdmin: true },
      children: [
        {
          path: 'dashboard',
          name: 'admin-dashboard',
          component: () => import('@/views/admin/AdminDashboardView.vue'),
        },
        {
          path: 'bookings',
          name: 'admin-bookings',
          component: () => import('@/views/admin/AdminBookingsView.vue'),
        },
        {
          path: 'users',
          name: 'admin-users',
          component: () => import('@/views/admin/AdminUsersView.vue'),
        },
        {
          path: '',
          redirect: { name: 'admin-dashboard' },
        },
      ],
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

  // If route requires admin and user is not admin, redirect to home
  if (to.matched.some((r) => r.meta.requiresAdmin)) {
    if (!auth.isAuthenticated) {
      return next('/login')
    }
    if (!auth.isAdmin) {
      return next('/')
    }
  }

  // Otherwise, proceed
  return next()
})

export default router
