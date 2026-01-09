<template>
  <div class="p-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">All Bookings</h1>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          />
        </svg>
        Add Booking
      </button>
    </div>

    <div class="mb-4 flex gap-4">
      <input
        v-model="search"
        @keyup.enter="doSearch(1)"
        type="text"
        placeholder="Search by user name/email..."
        class="border rounded px-4 py-2 w-64"
      />
      <button @click="doSearch(1)" class="bg-indigo-600 text-white px-4 py-2 rounded">
        Search
      </button>
      <button
        v-if="search"
        @click="clearSearch"
        class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300"
      >
        Clear
      </button>
    </div>

    <div class="bg-white rounded-xl shadow p-4">
      <table v-if="bookings.length" class="w-full text-left">
        <thead>
          <tr class="bg-indigo-50">
            <th class="px-4 py-2">ID</th>
            <th class="px-4 py-2">User</th>
            <th class="px-4 py-2">Date</th>
            <th class="px-4 py-2">Start</th>
            <th class="px-4 py-2">End</th>
            <th class="px-4 py-2">Created</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in bookings" :key="b.id" class="border-b hover:bg-gray-50">
            <td class="px-4 py-2">{{ b.id }}</td>
            <td class="px-4 py-2">
              {{ b.user?.name || '-' }}<br /><span class="text-xs text-gray-500">{{
                b.user?.email
              }}</span>
            </td>
            <td class="px-4 py-2">{{ b.date }}</td>
            <td class="px-4 py-2">{{ b.start_time }}</td>
            <td class="px-4 py-2">{{ b.end_time }}</td>
            <td class="px-4 py-2 text-xs text-gray-500">{{ formatDate(b.created_at) }}</td>
            <td class="px-4 py-2">
              <div class="flex gap-2">
                <button
                  @click="openViewModal(b)"
                  class="px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 text-xs"
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
                </button>
                <button
                  @click="openEditModal(b)"
                  class="px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 text-xs"
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
                </button>
                <button
                  @click="confirmDelete(b)"
                  class="px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs"
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
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else class="text-gray-400 text-center py-12">No bookings found.</div>

      <div class="flex justify-between items-center mt-4">
        <span class="text-gray-600 text-sm">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} bookings
        </span>
        <div class="flex items-center gap-2">
          <button
            v-if="pagination.has_prev_page"
            @click="doSearch(pagination.current_page - 1)"
            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
          >
            ← Previous
          </button>
          <span class="px-3 py-2 text-gray-600">
            Page {{ pagination.current_page }} / {{ pagination.total_pages }}
          </span>
          <button
            v-if="pagination.has_next_page"
            @click="doSearch(pagination.current_page + 1)"
            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
          >
            Next →
          </button>
        </div>
      </div>
    </div>

    <!-- View Modal -->
    <div
      v-if="showViewModal"
      class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50"
      @click.self="closeViewModal"
    >
      <div
        class="bg-white rounded-xl shadow-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      >
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Booking Details</h3>

        <div v-if="loadingValidation" class="text-center py-8">
          <div
            class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-indigo-600 border-r-transparent"
          ></div>
          <p class="mt-2 text-gray-600">Loading details...</p>
        </div>

        <div v-else-if="validationData">
          <!-- Booking Info -->
          <div class="space-y-4 mb-6">
            <div>
              <label class="text-sm font-medium text-gray-600">Booking ID</label>
              <p class="text-lg text-gray-900">{{ validationData.booking.id }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">User</label>
              <p class="text-lg text-gray-900">{{ validationData.booking.user?.name }}</p>
              <p class="text-sm text-gray-500">{{ validationData.booking.user?.email }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Date</label>
              <p class="text-lg text-gray-900">{{ validationData.booking.date }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-600">Start Time</label>
                <p class="text-lg text-gray-900">{{ validationData.booking.start_time }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">End Time</label>
                <p class="text-lg text-gray-900">{{ validationData.booking.end_time }}</p>
              </div>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-600">Created At</label>
              <p class="text-lg text-gray-900">
                {{ formatDate(validationData.booking.created_at) }}
              </p>
            </div>
          </div>

          <!-- Conflict Status -->
          <div v-if="validationData.has_conflicts" class="space-y-4">
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
              <div class="flex">
                <div class="shrink-0">
                  <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path
                      fill-rule="evenodd"
                      d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-yellow-800">Conflicts Detected</h3>
                  <p class="mt-1 text-sm text-yellow-700">This booking has scheduling conflicts.</p>
                </div>
              </div>
            </div>

            <!-- Exact Conflicts -->
            <div v-if="validationData.conflicts.length > 0">
              <h4 class="text-lg font-semibold text-red-700 mb-3">Exact Conflicts</h4>
              <div class="space-y-3">
                <div
                  v-for="(conflict, idx) in validationData.conflicts"
                  :key="idx"
                  class="bg-red-50 border border-red-200 rounded-lg p-4"
                >
                  <p class="text-sm font-medium text-red-900 mb-2">
                    {{ conflict.conflict_type === 'exact_match' ? 'Exact Time Match' : 'Conflict' }}
                  </p>
                  <div class="text-xs text-red-800 space-y-1">
                    <p>
                      <strong>Booking #{{ conflict.booking_1.id }}:</strong>
                      {{ conflict.booking_1.user }} • {{ conflict.booking_1.date }} •
                      {{ conflict.booking_1.start_time }}-{{ conflict.booking_1.end_time }}
                    </p>
                    <p>
                      <strong>Booking #{{ conflict.booking_2.id }}:</strong>
                      {{ conflict.booking_2.user }} • {{ conflict.booking_2.date }} •
                      {{ conflict.booking_2.start_time }}-{{ conflict.booking_2.end_time }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Overlapping -->
            <div v-if="validationData.overlapping.length > 0">
              <h4 class="text-lg font-semibold text-orange-700 mb-3">Overlapping Bookings</h4>
              <div class="space-y-3">
                <div
                  v-for="(overlap, idx) in validationData.overlapping"
                  :key="idx"
                  class="bg-orange-50 border border-orange-200 rounded-lg p-4"
                >
                  <p class="text-sm font-medium text-orange-900 mb-2">
                    {{ overlap.overlap_type === 'partial' ? 'Partial Overlap' : 'Overlap' }}
                  </p>
                  <div class="text-xs text-orange-800 space-y-1">
                    <p>
                      <strong>Booking #{{ overlap.booking_1.id }}:</strong>
                      {{ overlap.booking_1.user }} • {{ overlap.booking_1.date }} •
                      {{ overlap.booking_1.start_time }}-{{ overlap.booking_1.end_time }}
                    </p>
                    <p>
                      <strong>Booking #{{ overlap.booking_2.id }}:</strong>
                      {{ overlap.booking_2.user }} • {{ overlap.booking_2.date }} •
                      {{ overlap.booking_2.start_time }}-{{ overlap.booking_2.end_time }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- No Conflicts -->
          <div v-else class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
            <div class="flex">
              <div class="shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">No Conflicts</h3>
                <p class="mt-1 text-sm text-green-700">This booking has no scheduling conflicts.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8 flex justify-end">
          <button
            @click="closeViewModal"
            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div
      v-if="showFormModal"
      class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50"
      @click.self="closeFormModal"
    >
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">
          {{ isEditing ? 'Edit Booking' : 'Create Booking' }}
        </h3>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input
              v-model="formData.date"
              type="date"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
            <input
              v-model="formData.start_time"
              type="time"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
            <input
              v-model="formData.end_time"
              type="time"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
          <div
            v-if="formError"
            class="bg-red-50 border border-red-200 rounded-lg p-3 text-red-700 text-sm"
          >
            {{ formError }}
          </div>
          <div class="flex justify-end gap-3 mt-6">
            <button
              type="button"
              @click="closeFormModal"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
            >
              {{ loading ? 'Saving...' : isEditing ? 'Update' : 'Create' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50"
      @click.self="closeDeleteModal"
    >
      <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">Confirm Delete</h3>
        <p class="text-gray-600 mb-6">
          Are you sure you want to delete this booking? This action cannot be undone.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="closeDeleteModal"
            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
          >
            Cancel
          </button>
          <button
            @click="handleDelete"
            :disabled="loading"
            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ loading ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useBookings, type BookingFormData } from '@/composables/useBookings'
import type { Booking } from '@/model/booking'

const search = ref('')
const {
  bookings,
  pagination,
  loading,
  error,
  formatDate,
  fetchBookings,
  createBooking,
  updateBooking,
  deleteBooking,
  validateBooking,
} = useBookings()

const showViewModal = ref(false)
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const selectedBooking = ref<Booking | null>(null)
const isEditing = ref(false)
const formData = ref<BookingFormData>({
  date: '',
  start_time: '',
  end_time: '',
})
const formError = ref('')
const loadingValidation = ref(false)
const validationData = ref<any>(null)

function doSearch(page = 1) {
  fetchBookings(page, { keyword: search.value })
}

function clearSearch() {
  search.value = ''
  doSearch(1)
}

async function openViewModal(booking: Booking) {
  selectedBooking.value = booking
  showViewModal.value = true
  loadingValidation.value = true
  validationData.value = null

  const result = await validateBooking(booking.id)
  if (result.success) {
    validationData.value = result.data
  }
  loadingValidation.value = false
}

function closeViewModal() {
  showViewModal.value = false
  selectedBooking.value = null
  validationData.value = null
}

function openCreateModal() {
  isEditing.value = false
  formData.value = {
    date: '',
    start_time: '',
    end_time: '',
  }
  formError.value = ''
  showFormModal.value = true
}

function openEditModal(booking: Booking) {
  isEditing.value = true
  selectedBooking.value = booking
  formData.value = {
    date: booking.date,
    start_time: booking.start_time,
    end_time: booking.end_time,
  }
  formError.value = ''
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
  selectedBooking.value = null
  formError.value = ''
}

async function handleSubmit() {
  formError.value = ''

  if (isEditing.value && selectedBooking.value) {
    const result = await updateBooking(selectedBooking.value.id, formData.value)
    if (result.success) {
      closeFormModal()
      await doSearch(pagination.value.current_page)
    } else {
      formError.value = result.error || 'Failed to update booking'
    }
  } else {
    const result = await createBooking(formData.value)
    if (result.success) {
      closeFormModal()
      await doSearch(1)
    } else {
      formError.value = result.error || 'Failed to create booking'
    }
  }
}

function confirmDelete(booking: Booking) {
  selectedBooking.value = booking
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  selectedBooking.value = null
}

async function handleDelete() {
  if (!selectedBooking.value) return

  const result = await deleteBooking(selectedBooking.value.id)
  if (result.success) {
    closeDeleteModal()
    await doSearch(pagination.value.current_page)
  }
}

onMounted(() => {
  fetchBookings(1, { keyword: search.value })
})
</script>
