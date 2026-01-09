<template>
  <div class="bg-white rounded-2xl shadow-lg p-12 border border-gray-100 mx-auto mt-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-10">
      <h2 class="text-4xl font-bold text-indigo-700">My Bookings</h2>
      <button
        @click="openCreateModal"
        class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium flex items-center gap-2"
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

    <!-- Filters Component -->
    <BookingFilters @apply="handleApplyFilters" @clear="handleClearFilters" />

    <!-- Loading/Error States -->
    <div v-if="loading" class="text-center py-12 text-gray-500 text-lg">Loading bookings...</div>
    <div
      v-else-if="error"
      class="bg-red-50 border-l-4 border-red-500 p-6 rounded text-red-700 mb-10 text-lg"
    >
      {{ error }}
    </div>

    <!-- Bookings Content -->
    <div v-else>
      <!-- Booking Table Component -->
      <BookingTable
        :bookings="bookings"
        @view="openViewModal"
        @edit="openEditModal"
        @delete="confirmDelete"
      />

      <!-- Pagination -->
      <div v-if="bookings.length > 0" class="mt-8 flex justify-between items-center">
        <span class="text-gray-600">
          Page {{ pagination.current_page }} of {{ pagination.total_pages }}
        </span>
        <div class="flex gap-4">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 font-medium"
          >
            Previous
          </button>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.total_pages"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 font-medium"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Components -->
    <BookingFormModal
      :is-open="showFormModal"
      :is-editing="isEditing"
      :initial-data="
        selectedBooking
          ? {
              date: selectedBooking.date,
              start_time: selectedBooking.start_time,
              end_time: selectedBooking.end_time,
            }
          : undefined
      "
      :submitting="formSubmitting"
      :error="formError"
      @close="closeFormModal"
      @submit="handleSubmit"
    />

    <BookingDetailsModal
      :is-open="showViewModal"
      :booking="selectedBooking"
      :validation-data="validationData"
      :loading="loadingValidation"
      @close="closeViewModal"
    />

    <ConfirmDeleteModal
      :is-open="showDeleteModal"
      :deleting="deleting"
      @close="closeDeleteModal"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useBookings, type BookingFormData } from '@/composables/useBookings'
import type { Booking } from '@/model/booking'
import BookingFilters from '@/components/Bookings/BookingFilters.vue'
import BookingTable from '@/components/Bookings/BookingTable.vue'
import BookingFormModal from '@/components/Bookings/BookingFormModal.vue'
import BookingDetailsModal from '@/components/Bookings/BookingDetailsModal.vue'
import ConfirmDeleteModal from '@/components/Bookings/ConfirmDeleteModal.vue'

const {
  bookings,
  loading,
  error,
  pagination,
  fetchBookings,
  createBooking,
  updateBooking,
  deleteBooking,
  validateBooking,
} = useBookings()

// Modal states
const showViewModal = ref(false)
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const selectedBooking = ref<Booking | null>(null)
const isEditing = ref(false)
const formSubmitting = ref(false)
const formError = ref('')
const loadingValidation = ref(false)
const validationData = ref<any>(null)
const deleting = ref(false)

// Filters state
const filters = reactive({
  date: '',
  date_from: '',
  date_to: '',
  start_time: '',
})

// Handlers
function handleApplyFilters(appliedFilters: any) {
  Object.assign(filters, appliedFilters)
  fetchBookings(1, filters)
}

function handleClearFilters() {
  filters.date = ''
  filters.date_from = ''
  filters.date_to = ''
  filters.start_time = ''
  fetchBookings(1)
}

function changePage(page: number) {
  fetchBookings(page, filters)
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
  selectedBooking.value = null
  formError.value = ''
  showFormModal.value = true
}

function openEditModal(booking: Booking) {
  isEditing.value = true
  selectedBooking.value = booking
  formError.value = ''
  showFormModal.value = true
}

function closeFormModal() {
  showFormModal.value = false
  selectedBooking.value = null
  formError.value = ''
  formSubmitting.value = false
}

async function handleSubmit(formData: BookingFormData) {
  formSubmitting.value = true
  formError.value = ''

  if (isEditing.value && selectedBooking.value) {
    const result = await updateBooking(selectedBooking.value.id, formData)
    if (result.success) {
      closeFormModal()
      await fetchBookings(pagination.value.current_page, filters)
    } else {
      formError.value = result.error || 'Failed to update booking'
    }
  } else {
    const result = await createBooking(formData)
    if (result.success) {
      closeFormModal()
      await fetchBookings(1, filters)
    } else {
      formError.value = result.error || 'Failed to create booking'
    }
  }
  formSubmitting.value = false
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

  deleting.value = true
  const result = await deleteBooking(selectedBooking.value.id)
  if (result.success) {
    closeDeleteModal()
    await fetchBookings(pagination.value.current_page, filters)
  }
  deleting.value = false
}

onMounted(() => {
  fetchBookings()
})
</script>
