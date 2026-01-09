<template>
  <div class="min-h-[80vh] flex items-center justify-center px-2 sm:px-6 lg:px-0 py-8">
    <div class="w-full max-w-lg">
      <div class="bg-white rounded-2xl shadow-xl p-12 border border-gray-100">
        <h2 class="text-4xl font-bold text-gray-900 pb-2">Welcome Back</h2>
        <p class="text-lg text-gray-600 pb-6">Sign in to access your bookings</p>

        <LoginForm />

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
import LoginForm from '@/components/Forms/LoginForm.vue'
import { RouterLink } from 'vue-router'
</script>
