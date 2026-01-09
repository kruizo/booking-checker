<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col sticky top-0 h-screen">
      <div class="p-6 shrink-0">
        <h1 class="text-2xl font-bold text-indigo-600">B.C.C Admin</h1>
      </div>

      <nav class="px-4 flex-1 overflow-y-auto">
        <div class="flex items-center gap-3 rounded-lg my-4 h-14 transition-colors">
          <RouterLink
            to="/admin/dashboard"
            class="flex items-center gap-3 px-4 w-full h-full rounded-lg transition-colors"
            :class="
              $route.name === 'admin-dashboard'
                ? 'bg-indigo-50 text-indigo-600 font-medium'
                : 'text-gray-700 hover:bg-gray-50'
            "
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
            Dashboard
          </RouterLink>
        </div>

        <div class="flex items-center gap-3 rounded-lg my-4 h-14 transition-colors">
          <RouterLink
            to="/admin/bookings"
            class="flex items-center gap-3 px-4 w-full h-full rounded-lg transition-colors"
            :class="
              $route.name === 'admin-bookings'
                ? 'bg-indigo-50 text-indigo-600 font-medium'
                : 'text-gray-700 hover:bg-gray-50'
            "
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
            Bookings
          </RouterLink>
        </div>

        <div class="flex items-center gap-3 rounded-lg my-4 h-14 transition-colors">
          <RouterLink
            to="/admin/users"
            class="flex items-center gap-3 px-4 w-full h-full rounded-lg transition-colors"
            :class="
              $route.name === 'admin-users'
                ? 'bg-indigo-50 text-indigo-600 font-medium'
                : 'text-gray-700 hover:bg-gray-50'
            "
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
            Users
          </RouterLink>
        </div>
      </nav>

      <!-- User Info at Bottom -->
      <div class="p-4 border-t border-gray-200 shrink-0">
        <div class="flex flex-col mb-3">
          <span class="text-sm font-medium text-gray-700 truncate">{{ auth.user?.name }}</span>
          <span class="text-xs text-gray-500 truncate">{{ auth.user?.email }}</span>
        </div>
        <div class="flex gap-2">
          <RouterLink
            to="/"
            class="flex-1 px-3 py-2 text-center text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            Exit
          </RouterLink>
          <button
            @click="handleLogout"
            class="flex-1 px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
          >
            Logout
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen">
      <!-- Top Bar -->
      <header class="bg-white border-b border-gray-200 sticky top-0 z-10 shrink-0">
        <div class="px-8 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ pageTitle }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ pageDescription }}</p>
          </div>
          <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-lg font-medium rounded-full">
              Admin
            </span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 bg-gray-50 overflow-y-auto">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const pageTitle = computed(() => {
  switch (route.name) {
    case 'admin-dashboard':
      return 'Dashboard'
    case 'admin-bookings':
      return 'Bookings'
    case 'admin-users':
      return 'Users'
    default:
      return 'Admin Panel'
  }
})

const pageDescription = computed(() => {
  switch (route.name) {
    case 'admin-dashboard':
      return 'Overview of bookings, conflicts, and user activity'
    case 'admin-bookings':
      return 'Manage all bookings in the system'
    case 'admin-users':
      return 'Manage user accounts and permissions'
    default:
      return ''
  }
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
