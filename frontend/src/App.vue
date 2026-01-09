<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useRoute } from 'vue-router'
import { computed, onMounted } from 'vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

// Check if current route is admin
const isAdminRoute = computed(() => route.path.startsWith('/admin'))

onMounted(async () => {
  await auth.fetchUser()
})

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <RouterView v-if="isAdminRoute" />

  <div v-else class="min-h-screen bg-gray-50 flex flex-col items-center">
    <header class="shadow-sm w-full bg-white border-gray-200 border-b place-items-center">
      <div class="max-w-dvw mx-auto px-4 sm:px-6 lg:px-8 flex flex-col gap-6 h-full w-full">
        <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
          <div class="flex justify-between gap-8 w-full">
            <RouterLink to="/">
              <h1 class="text-2xl font-bold text-gray-600">B.C.C</h1>
            </RouterLink>
            <nav class="hidden sm:flex flex-1 gap-6 w-full justify-end">
              <RouterLink
                v-if="auth.isAuthenticated"
                to="/bookings"
                class="text-gray-700 hover:text-indigo-600 transition-colors font-medium"
                >My Bookings</RouterLink
              >
            </nav>
            <div class="flex gap-4 items-center">
              <RouterLink
                v-if="!auth.isAuthenticated"
                to="/login"
                class="px-4 py-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors font-medium"
                >Login</RouterLink
              >
              <RouterLink
                v-if="!auth.isAuthenticated"
                to="/register"
                class="px-4 py-2 text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors font-medium"
                >Sign Up</RouterLink
              >
              <div v-else class="flex gap-4">
                <div class="flex items-center gap-4">
                  <div class="flex flex-col items-end">
                    <span class="text-gray-700 font-semibold">{{ auth.user?.name }}</span>
                    <span class="text-xs text-gray-500">{{ auth.user?.email }}</span>
                  </div>
                  <button
                    @click="auth.toggleAdmin"
                    class="px-4 py-2 text-indigo-600 hover:bg-indigo-50 cursor-pointer rounded-lg transition-colors font-medium"
                    :disabled="auth.loading"
                  >
                    {{ auth.isAdmin ? 'Remove Admin' : 'Make Admin' }}
                  </button>
                  <button
                    @click="handleLogout"
                    class="px-4 py-2 text-red-600 hover:bg-red-50 cursor-pointer rounded-lg transition-colors font-medium"
                  >
                    Logout
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col gap-6 h-full w-full">
      <div>
        <RouterView />
      </div>
    </main>
  </div>
</template>

<style scoped>
:global(body) {
  margin: 0;
  padding: 0;
}
</style>
