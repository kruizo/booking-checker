import { ref } from 'vue'
import axiosInstance from '@/lib/axios'
import { useApi } from '@/composables/useApi'

export interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at: string
}

export interface UserListResponse {
  status: number
  errorCode: string | null
  message: string
  timestamp: string
  data: User[]
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

export function useUser() {
  const api = useApi()
  const users = ref<User[]>([])
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

  async function fetchUsers(page = 1, keyword = '') {
    loading.value = true
    error.value = ''
    try {
      const params: any = { page }
      if (keyword) params.keyword = keyword
      const res = await api.get<UserListResponse>('/admin/users', { params })
      console.log('fetchUsers response:', res)
      const data = res.data as UserListResponse
      users.value = data.data
      pagination.value = data.pagination
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to load users.'
    } finally {
      loading.value = false
    }
  }

  return {
    users,
    loading,
    error,
    pagination,
    formatDate,
    fetchUsers,
  }
}
