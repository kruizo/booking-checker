<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50"
    @click.self="closeModal"
  >
    <div
      class="bg-white rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
    >
      <h3 class="text-2xl font-bold text-gray-900 mb-6">Booking Details</h3>

      <div v-if="loading" class="text-center py-8">
        <div
          class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-indigo-600 border-r-transparent"
        ></div>
        <p class="mt-2 text-gray-600">Loading details...</p>
      </div>

      <div v-else-if="validationData">
        <!-- Booking Info -->
        <div class="space-y-4 mb-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600">Date</p>
              <p class="text-lg font-semibold text-gray-900">{{ formatDate(booking.date) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Time</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ booking.start_time }} - {{ booking.end_time }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Duration</p>
              <p class="text-lg font-semibold text-gray-900">
                {{ calculateDuration(booking.start_time, booking.end_time) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Conflict Status -->
        <div v-if="validationData.has_conflicts" class="space-y-4">
          <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
            <h4 class="font-bold text-red-800 mb-3">Conflicts Found</h4>
            <div class="space-y-3">
              <div
                v-for="conflict in validationData.conflicts"
                :key="conflict.id"
                class="bg-white p-3 rounded border border-red-200"
              >
                <p class="font-semibold text-gray-900">{{ conflict.type }}</p>
                <p class="text-sm text-gray-600">
                  Overlaps with: {{ conflict.overlapping_booking_id }}
                </p>
                <p class="text-sm text-gray-600">{{ conflict.description }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- No Conflicts -->
        <div v-else class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
          <p class="text-green-800 font-semibold">✓ No conflicts detected</p>
          <p class="text-sm text-green-700">This booking doesn't overlap with any others.</p>
        </div>
      </div>

      <div class="mt-8 flex justify-end">
        <button
          @click="closeModal"
          class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import type { Booking } from '@/model/booking'

interface Props {
  isOpen: boolean
  booking?: Booking
  validationData?: any
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false,
})

const emit = defineEmits<{
  close: []
}>()

function closeModal() {
  emit('close')
}

function formatDate(dateStr: string) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

function calculateDuration(startTime: string, endTime: string) {
  const [startHour, startMin] = startTime.split(':').map(Number)
  const [endHour, endMin] = endTime.split(':').map(Number)

  const start = startHour * 60 + startMin
  const end = endHour * 60 + endMin
  const diff = end - start

  const hours = Math.floor(diff / 60)
  const mins = diff % 60

  return `${hours}h ${mins}m`
}
</script>
