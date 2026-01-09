<template>
  <div class="bg-white rounded-2xl shadow-lg p-12 border border-gray-100 mx-auto mt-8">
    <h2 class="text-4xl font-bold text-indigo-700 mb-10">My Bookings</h2>
    <div v-if="loading" class="text-center py-12 text-gray-500 text-lg">Loading bookings...</div>
    <div
      v-else-if="error"
      class="bg-red-50 border-l-4 border-red-500 p-6 rounded text-red-700 mb-10 text-lg"
    >
      {{ error }}
    </div>
    <div v-else>
      <div v-if="bookings.length === 0" class="text-gray-500 py-12 text-center text-lg">
        No bookings found.
      </div>
      <div v-else>
        <table class="w-full border-collapse mb-8">
          <thead>
            <tr class="bg-indigo-50">
              <th class="px-6 py-4 text-left text-gray-700 font-semibold text-lg">Date</th>
              <th class="px-6 py-4 text-left text-gray-700 font-semibold text-lg">Start Time</th>
              <th class="px-6 py-4 text-left text-gray-700 font-semibold text-lg">End Time</th>
              <th class="px-6 py-4 text-left text-gray-700 font-semibold text-lg">Created</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="booking in bookings" :key="booking.id" class="border-b hover:bg-indigo-50">
              <td class="px-6 py-4">{{ booking.date }}</td>
              <td class="px-6 py-4">{{ booking.start_time }}</td>
              <td class="px-6 py-4">{{ booking.end_time }}</td>
              <td class="px-6 py-4 text-gray-500">{{ formatDate(booking.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        <div class="mt-8 flex justify-end gap-4">
          <button
            v-if="pagination.has_prev_page"
            @click="fetchBookings(pagination.current_page - 1)"
            class="px-6 py-3 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-lg"
          >
            Prev
          </button>
          <button
            v-if="pagination.has_next_page"
            @click="fetchBookings(pagination.current_page + 1)"
            class="px-6 py-3 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 text-lg"
          >
            Next
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useBookings } from '@/composables/useBookings'

const { bookings, loading, error, pagination, formatDate, fetchBookings } = useBookings()

onMounted(() => {
  fetchBookings()
})
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
