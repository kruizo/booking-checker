<template>
  <div class="mb-6 bg-gray-50 p-4 rounded-lg">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input
          v-model="filters.date"
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
        <input
          v-model="filters.date_from"
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
        <input
          v-model="filters.date_to"
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
        <input
          v-model="filters.start_time"
          type="time"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
        />
      </div>
    </div>
    <div class="mt-4 flex gap-2">
      <button
        @click="handleApply"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium"
      >
        Apply Filters
      </button>
      <button
        @click="handleClear"
        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium"
      >
        Clear
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive } from 'vue'

interface Filters {
  date: string
  date_from: string
  date_to: string
  start_time: string
  end_time?: string
}

const emit = defineEmits<{
  apply: [filters: Filters]
  clear: []
}>()

const filters = reactive<Filters>({
  date: '',
  date_from: '',
  date_to: '',
  start_time: '',
})

function handleApply() {
  emit('apply', filters)
}

function handleClear() {
  filters.date = ''
  filters.date_from = ''
  filters.date_to = ''
  filters.start_time = ''
  emit('clear')
}
</script>
