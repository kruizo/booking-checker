<template>
  <form @submit.prevent="onSubmit" class="pt-2 pb-2">
    <div class="pb-4">
      <label class="block text-sm font-semibold text-gray-700 pb-2">Email Address</label>
      <input
        v-model="formData.email"
        type="email"
        placeholder="you@example.com"
        class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors"
        required
      />
    </div>

    <div class="pb-4">
      <label class="block text-sm font-semibold text-gray-700 pb-2">Password</label>
      <input
        v-model="formData.password"
        type="password"
        placeholder="••••••••"
        class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors"
        required
      />
    </div>

    <div v-if="error" class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-700 text-sm">
      {{ error }}
    </div>

    <button
      type="submit"
      class="w-full py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg pt-2"
      :disabled="loading"
    >
      <span v-if="loading" class="flex items-center justify-center gap-2">
        <span class="inline-block animate-spin">⟳</span> Logging in...
      </span>
      <span v-else>Sign In</span>
    </button>
  </form>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { useRouter } from 'vue-router'

const router = useRouter()
const { login, loading, error, isAdmin } = useAuth()

const formData = reactive({
  email: '',
  password: '',
})

async function onSubmit() {
  const success = await login({
    email: formData.email,
    password: formData.password,
  })
  if (success) {
    if (isAdmin.value) {
      router.push('/admin/dashboard')
    } else {
      router.push('/bookings')
    }
  }
}
</script>
