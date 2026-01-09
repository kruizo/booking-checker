<template>
  <div class="min-h-[80vh] flex items-center justify-center px-2 sm:px-6 lg:px-0 py-8">
    <div class="w-full max-w-lg">
      <div class="bg-white rounded-2xl shadow-xl p-12 border border-gray-100">
        <h2 class="text-4xl font-bold text-gray-900 pb-2">Welcome Back</h2>
        <p class="text-lg text-gray-600 pb-6">Sign in to access your bookings</p>

        <form @submit.prevent="onLogin" class="pt-2 pb-2">
          <div class="pb-4">
            <label class="block text-sm font-semibold text-gray-700 pb-2">Email Address</label>
            <input
              v-model="email"
              type="email"
              placeholder="you@example.com"
              class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors"
              required
            />
          </div>

          <div class="pb-4">
            <label class="block text-sm font-semibold text-gray-700 pb-2">Password</label>
            <input
              v-model="password"
              type="password"
              placeholder="••••••••"
              class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-indigo-500 transition-colors"
              required
            />
          </div>

          <div
            v-if="error"
            class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-red-700 text-sm"
          >
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

        <div class="pt-10 border-t border-gray-200">
          <p class="text-center text-gray-600">
            Don't have an account?
            <RouterLink to="/register" class="text-indigo-600 font-semibold hover:text-indigo-700"
              >Create one</RouterLink
            >
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { useRouter, RouterLink } from 'vue-router'

const email = ref('')
const password = ref('')
const router = useRouter()
const { login, loading, error, isAdmin } = useAuth()

async function onLogin() {
  const success = await login({ email: email.value, password: password.value })
  if (success) {
    if (isAdmin.value) {
      router.push('/admin/dashboard')
    } else {
      router.push('/bookings')
    }
  }
}
</script>
