export interface ApiResponse<T = any> {
  status: number
  errorCode: string | null
  message: string
  timestamp: string
  data: T
  errors?: Record<string, string[]>
}
