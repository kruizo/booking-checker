export interface Booking {
  id: number
  user_id: number
  date: string // YYYY-MM-DD
  start_time: string // HH:MM
  end_time: string // HH:MM
  created_at: string
  updated_at: string
  user?: {
    id: number
    name: string
    email: string
    is_admin: boolean
    created_at: string
  }
}

export interface BookingListResponse {
  status: number
  errorCode: string | null
  message: string
  timestamp: string
  data: Booking[]
  pagination: {
    current_page: number
    per_page: number
    total: number
    total_pages: number
    has_next_page: boolean
    has_prev_page: boolean
    from: number
    to: number
  }
}

export interface BookingCreateResponse {
  status: number
  errorCode: string | null
  message: string
  timestamp: string
  data: {
    booking: Booking
  }
}

export interface BookingErrorResponse {
  status: number
  errorCode: string
  message: string
  timestamp: string
  errors: Record<string, string[]>
}
