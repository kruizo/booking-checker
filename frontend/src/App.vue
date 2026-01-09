<script setup lang="ts">
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useRoute } from 'vue-router'
import { computed, onMounted } from 'vue'
import Header from '@/components/Header.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

// Check if current route is admin
const isAdminRoute = computed(() => route.path.startsWith('/admin'))

onMounted(async () => {
  await auth.fetchUser()
})
</script>

<template>
  <RouterView v-if="isAdminRoute" />

  <div v-else class="min-h-screen bg-gray-50 flex flex-col items-center">
    <Header />

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
