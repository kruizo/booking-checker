<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useRoute } from 'vue-router'
import { computed, onMounted, ref } from 'vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

// Check if current route is admin
const isAdminRoute = computed(() => route.path.startsWith('/admin'))

// User menu state
const showUserMenu = ref(false)

onMounted(async () => {
  await auth.fetchUser()
})

const handleLogout = async () => {
  await auth.logout()
  router.push('/login')
}

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value
}

const closeUserMenu = () => {
  showUserMenu.value = false
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
            <nav class="hidden sm:flex flex-1 gap-6 w-full justify-end items-center"></nav>
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
              <!-- User Info Dropdown -->
              <div v-else class="relative">
                <button
                  @click="toggleUserMenu"
                  class="flex flex-col items-end hover:bg-gray-100 px-3 py-2 rounded-lg transition-colors cursor-pointer"
                >
                  <span class="text-lg font-bold text-gray-900">Hi {{ auth.user?.name }} 👋</span>
                  <span class="text-xs text-gray-500">{{ auth.user?.email }}</span>
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-if="showUserMenu"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
                  @click="closeUserMenu"
                >
                  <div class="px-4 py-2 text-sm text-gray-600 font-medium">Navigation</div>
                  <div
                    class="block w-full px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors text-sm text-left"
                  >
                    <RouterLink to="/bookings"> 📅 My Bookings </RouterLink>
                  </div>
                  <div class="border-t border-gray-200 my-1"></div>
                  <div class="px-4 py-2 text-sm text-gray-600 font-medium">Account</div>
                  <button
                    @click="auth.toggleAdmin"
                    :disabled="auth.loading"
                    class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50 transition-colors text-sm disabled:opacity-50"
                  >
                    {{ auth.isAdmin ? '👑 Remove Admin Access' : '👑 Make Admin' }}
                  </button>
                  <div class="border-t border-gray-200 my-1"></div>
                  <button
                    @click="handleLogout"
                    class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 transition-colors text-sm font-medium"
                  >
                    🚪 Logout
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
