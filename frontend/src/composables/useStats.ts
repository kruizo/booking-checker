import { ref } from 'vue'
import { useApi } from '@/composables/useApi'

export interface StatisticsInterval {
  date: string
  bookings: number
  signups: number
}

export interface RecentSignup {
  id: number
  name: string
  email: string
  created_at: string
}

export interface StatisticsResponse {
  status: number
  errorCode: string | null
  message: string
  timestamp: string
  data: {
    period: string
    intervals: StatisticsInterval[]
    recent_signups: RecentSignup[]
    summary: {
      total_bookings: number
      total_signups: number
    }
  }
}

export interface BookingInfo {
  id: number
  user: string
  date: string
  start_time: string
  end_time: string
}

export interface Overlap {
  booking_1: BookingInfo
  booking_2: BookingInfo
  overlap_type: string
}

export interface Conflict {
  booking_1: BookingInfo
  booking_2: BookingInfo
  conflict_type: string
}

export interface Gap {
  date: string
  between_bookings: Array<{
    id: number
    user: string
    start_time?: string
    end_time?: string
  }>
  gap_duration_minutes: number
  gap_start: string
  gap_end: string
}

export interface ConflictsResponse {
  status: number
  errorCode: string | null
  message: string
  timestamp: string
  data: {
    overlapping: Overlap[]
    conflicts: Conflict[]
    gaps: Gap[]
    summary: {
      total_bookings: number
      overlapping_count: number
      conflict_count: number
      gap_count: number
    }
  }
}

export function useStats() {
  const api = useApi()

  const statistics = ref<StatisticsResponse['data'] | null>(null)
  const conflicts = ref<ConflictsResponse['data'] | null>(null)
  const loading = ref(false)
  const error = ref('')

  async function fetchStatistics(period: 'daily' | 'weekly' | 'yearly' = 'daily') {
    loading.value = true
    error.value = ''
    try {
      const res = await api.get<StatisticsResponse>('/admin/statistics', { period })
      statistics.value = res.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load statistics.'
      statistics.value = null
    } finally {
      loading.value = false
    }
  }

  async function fetchConflicts() {
    loading.value = true
    error.value = ''
    try {
      const res = await api.get<ConflictsResponse>('/admin/conflicts')
      conflicts.value = res.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load conflicts.'
      conflicts.value = null
    } finally {
      loading.value = false
    }
  }

  function formatDuration(minutes: number): string {
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    if (hours > 0) {
      return `${hours}h ${mins}m`
    }
    return `${mins}m`
  }

  return {
    statistics,
    conflicts,
    loading,
    error,
    fetchStatistics,
    fetchConflicts,
    formatDuration,
  }
}
