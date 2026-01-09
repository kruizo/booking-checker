import { ref } from 'vue'
import axios from 'axios'
import type { Booking, BookingListResponse } from '@/model/booking'

export function useBookings() {
  const bookings = ref<Booking[]>([])
  const loading = ref(false)
  const error = ref('')
  const pagination = ref({
    current_page: 1,
    per_page: 15,
    total: 0,
    total_pages: 1,
    has_next_page: false,
    has_prev_page: false,
    from: 1,
    to: 1,
  })

  function formatDate(dateStr: string) {
    const d = new Date(dateStr)
    return d.toLocaleString()
  }

  async function fetchBookings(page = 1) {
    loading.value = true
    error.value = ''
    try {
      const res = await axios.get<BookingListResponse>(`/api/v1/bookings?page=${page}`, {
        withCredentials: true,
      })
      bookings.value = res.data.data
      pagination.value = res.data.pagination
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load bookings.'
    } finally {
      loading.value = false
    }
  }

  return {
    bookings,
    loading,
    error,
    pagination,
    formatDate,
    fetchBookings,
  }
}
