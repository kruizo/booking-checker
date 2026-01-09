<template>
  <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600">{{ label }}</p>
        <p class="text-3xl font-bold mt-1" :class="textColorClass">
          {{ value }}
        </p>
      </div>
      <div class="h-12 w-12 rounded-lg flex items-center justify-center" :class="bgColorClass">
        <svg
          class="h-6 w-6"
          :class="iconColorClass"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="iconPath" />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label: string
  value: number | string
  type: 'bookings' | 'signups' | 'conflicts' | 'gaps'
}

const props = defineProps<Props>()

const bgColorClass = computed(() => {
  const map = {
    bookings: 'bg-indigo-100',
    signups: 'bg-green-100',
    conflicts: 'bg-red-100',
    gaps: 'bg-blue-100',
  }
  return map[props.type]
})

const iconColorClass = computed(() => {
  const map = {
    bookings: 'text-indigo-600',
    signups: 'text-green-600',
    conflicts: 'text-red-600',
    gaps: 'text-blue-600',
  }
  return map[props.type]
})

const textColorClass = computed(() => {
  const map = {
    bookings: 'text-gray-900',
    signups: 'text-gray-900',
    conflicts: 'text-red-600',
    gaps: 'text-blue-600',
  }
  return map[props.type]
})

const iconPath = computed(() => {
  const map = {
    bookings:
      'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    signups:
      'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
    conflicts:
      'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    gaps: 'M13 10V3L4 14h7v7l9-11h-7z',
  }
  return map[props.type]
})
</script>
