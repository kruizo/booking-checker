<template>
  <div v-if="bookings.length === 0" class="text-gray-500 py-12 text-center text-lg">
    No bookings found.
  </div>

  <div v-else>
    <table class="w-full border-collapse mb-8">
      <thead>
        <tr class="bg-indigo-50">
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">End</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Booked At</th>
          <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="booking in bookings" :key="booking.id" class="border-b hover:bg-indigo-50">
          <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(booking.date) }}</td>
          <td class="px-4 py-3 text-sm text-gray-600">
            {{ booking.start_time }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-600">
            {{ booking.end_time }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-600">
            {{ calculateDuration(booking.start_time, booking.end_time) }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-500">
            {{ formatDateTime(booking.created_at) }}
          </td>
          <td class="px-4 py-3 text-sm">
            <div class="flex gap-2">
              <button
                @click="$emit('view', booking)"
                class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-sm flex items-center gap-1"
                title="View"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
                View
              </button>
              <button
                @click="$emit('edit', booking)"
                class="px-3 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-sm flex items-center gap-1"
                title="Edit"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
                Edit
              </button>
              <button
                @click="$emit('delete', booking)"
                class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm flex items-center gap-1"
                title="Delete"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
                Delete
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import type { Booking } from '@/model/booking'

defineProps<{
  bookings: Booking[]
}>()

defineEmits<{
  view: [booking: Booking]
  edit: [booking: Booking]
  delete: [booking: Booking]
}>()

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

function formatDateTime(dateStr: string) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>

<style scoped>
table {
  border-radius: 12px;
  overflow: hidden;
}
th,
td {
  border-bottom: 1px solid #e5e7eb;
}
</style>
