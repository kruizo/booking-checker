import { ref } from 'vue'
import { useApi } from './useApi'
import type { Booking, BookingListResponse } from '@/model/booking'

export interface BookingFormData {
  date: string
  start_time: string
  end_time: string
}

export interface BookingFilters {
  keyword?: string
  date?: string
  date_from?: string
  date_to?: string
  start_time?: string
  end_time?: string
  sort_by?: string
  sort_direction?: string
}

export interface ValidationResult {
  booking: Booking
  has_conflicts: boolean
  overlapping: Array<{
    booking_1: {
      id: number
      user: string
      date: string
      start_time: string
      end_time: string
    }
    booking_2: {
      id: number
      user: string
      date: string
      start_time: string
      end_time: string
    }
    overlap_type: string
  }>
  conflicts: Array<{
    booking_1: {
      id: number
      user: string
      date: string
      start_time: string
      end_time: string
    }
    booking_2: {
      id: number
      user: string
      date: string
      start_time: string
      end_time: string
    }
    conflict_type: string
  }>
}

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
  const { get, post, put, del } = useApi()

  function formatDate(dateStr: string) {
    const d = new Date(dateStr)
    return d.toLocaleString()
  }

  async function fetchBookings(page = 1, filters?: BookingFilters) {
    loading.value = true
    error.value = ''
    try {
      const params: any = { page }
      if (filters) {
        if (filters.keyword) params.keyword = filters.keyword
        if (filters.date) params.date = filters.date
        if (filters.date_from) params.date_from = filters.date_from
        if (filters.date_to) params.date_to = filters.date_to
        if (filters.start_time) params.start_time = filters.start_time
        if (filters.end_time) params.end_time = filters.end_time
        if (filters.sort_by) params.sort_by = filters.sort_by
        if (filters.sort_direction) params.sort_direction = filters.sort_direction
      }
      const url = '/bookings'
      const res = await get<BookingListResponse>(url, params)
      const data = res.data as BookingListResponse
      bookings.value = data.data
      pagination.value = data.pagination
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load bookings.'
    } finally {
      loading.value = false
    }
  }

  async function createBooking(formData: BookingFormData) {
    loading.value = true
    error.value = ''
    try {
      await post('/bookings', formData)
      return { success: true }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to create booking.'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function updateBooking(id: number, formData: BookingFormData) {
    loading.value = true
    error.value = ''
    try {
      await put(`/bookings/${id}`, formData)
      return { success: true }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to update booking.'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function deleteBooking(id: number) {
    loading.value = true
    error.value = ''
    try {
      await del(`/bookings/${id}`)
      return { success: true }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to delete booking.'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function getBooking(id: number) {
    loading.value = true
    error.value = ''
    try {
      const res = await get(`/bookings/${id}`)
      return { success: true, booking: res.data.data.booking }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to fetch booking.'
      return { success: false, error: error.value }
    } finally {
      loading.value = false
    }
  }

  async function validateBooking(id: number) {
    loading.value = true
    error.value = ''
    try {
      const res = await get<{ data: ValidationResult }>(`/bookings/${id}/validate`)
      return {
        success: true,
        data: res.data.data,
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to validate booking.'
      return { success: false, error: error.value }
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
    createBooking,
    updateBooking,
    deleteBooking,
    getBooking,
    validateBooking,
  }
}
