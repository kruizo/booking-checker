<template>
  <div class="px-4 flex flex-col gap-12 justify-center items-center w-full">
    <!-- Hero Section -->
    <div class="rounded-2xl sm:p-10 mb-20">
      <div class="max-w-3xl mx-auto">
        <h1 class="text-5xl font-bold text-gray-900 mb-8 text-center">Booking Conflict Checker</h1>
        <p class="text-2xl text-gray-600 mb-12 leading-relaxed text-center">
          Manage your bookings with confidence. Automatically detect conflicts, overlaps, and gaps
          to ensure seamless scheduling.
        </p>
        <div v-if="!auth.isAuthenticated" class="flex justify-center gap-6 pt-10">
          <div
            class="px-10 py-3 bg-indigo-600 text-white rounded-full hover:bg-indigo-700 font-bold shadow-lg text-2xl text-center"
          >
            <RouterLink to="/register"> Get Started </RouterLink>
          </div>
          <div
            class="px-10 py-3 border-2 border-indigo-600 text-indigo-600 rounded-full hover:bg-indigo-50 font-bold text-2xl text-center"
          >
            <RouterLink to="/login"> Login </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12">
      <div
        class="bg-white rounded-xl shadow-md p-10 border border-gray-100 hover:shadow-lg transition-shadow mb-8"
      >
        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
          <span class="text-2xl">📅</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Smart Booking Management</h3>
        <p class="text-gray-600">
          Create, edit, and manage your bookings with an intuitive interface.
        </p>
      </div>

      <div
        class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow"
      >
        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-4">
          <span class="text-2xl">⚠️</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Conflict Detection</h3>
        <p class="text-gray-600">Automatically identify overlapping bookings and time conflicts.</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow"
      >
        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
          <span class="text-2xl">📊</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Gap Analysis</h3>
        <p class="text-gray-600">See empty time slots and gaps between your bookings.</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow"
      >
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
          <span class="text-2xl">👥</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Admin Dashboard</h3>
        <p class="text-gray-600">Admins can view and manage all users' bookings from one place.</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow"
      >
        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
          <span class="text-2xl">⚡</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Real-Time Updates</h3>
        <p class="text-gray-600">WebSocket support for live booking updates across devices.</p>
      </div>

      <div
        class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow"
      >
        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
          <span class="text-2xl">📱</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Fully Responsive</h3>
        <p class="text-gray-600">Works perfectly on mobile, tablet, and desktop devices.</p>
      </div>
    </div>

    <!-- Tech Stack -->
    <div class="bg-white rounded-2xl shadow-lg p-12 border border-gray-100 mt-20">
      <h2 class="text-3xl font-bold text-gray-900 mb-10 text-center">Built With Excellence</h2>
      <div class="grid md:grid-cols-2 gap-12">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <span class="text-2xl">🔙</span> Backend
          </h3>
          <ul class="space-y-2 text-gray-600">
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> Laravel 12
            </li>
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> PSR-12 Standards
            </li>
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> SOLID Principles
            </li>
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> Repository Pattern
            </li>
          </ul>
        </div>
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <span class="text-2xl">🎨</span> Frontend
          </h3>
          <ul class="space-y-2 text-gray-600">
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> Vue 3 Composition API
            </li>
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> Pinia State Management
            </li>
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> Tailwind CSS
            </li>
            <li class="flex items-center gap-2">
              <span class="text-indigo-600">✓</span> TypeScript
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { RouterLink } from 'vue-router'

const auth = useAuthStore()
</script>

<style scoped></style>
