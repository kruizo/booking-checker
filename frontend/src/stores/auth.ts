import { defineStore } from 'pinia'

import { ref, computed } from 'vue'

import axios from 'axios'
import Cookies from 'js-cookie'
import type { ApiResponse } from '@/types/api'

interface AuthUser {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at?: string
  token?: string
  token_type?: string
}

interface AuthData {
  user: AuthUser
}

export const useAuthStore = defineStore('auth', () => {
  // Try to restore user from sessionStorage
  const cachedUser = sessionStorage.getItem('auth_user')
  const user = ref<AuthUser | null>(cachedUser ? JSON.parse(cachedUser) : null)
  const token = ref('')
  const loading = ref(false)
  const error = ref('')

  // Set axios baseURL to backend server
  axios.defaults.baseURL = 'http://localhost:8000'
  axios.defaults.withCredentials = true

  // Add X-XSRF-TOKEN header from cookie for all requests
  axios.interceptors.request.use((config) => {
    const xsrfToken = Cookies.get('XSRF-TOKEN')
    if (xsrfToken) {
      config.headers['X-XSRF-TOKEN'] = xsrfToken
    }
    return config
  })

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  async function fetchUser() {
    // Only fetch if user is not cached
    try {
      loading.value = true
      error.value = ''
      const res = await axios.get<ApiResponse<AuthData>>('/api/v1/user')
      user.value = res.data.data.user
      sessionStorage.setItem('auth_user', JSON.stringify(user.value))
      console.log('[auth] fetchUser:', {
        isAuthenticated: !!user.value,
        user: user.value,
        token: token.value,
      })
    } catch (e: any) {
      user.value = null
      sessionStorage.removeItem('auth_user')
      console.log('[auth] fetchUser: not authenticated')
    } finally {
      loading.value = false
    }
  }

  async function login(payload: { email: string; password: string }) {
    loading.value = true
    error.value = ''
    try {
      await axios.get('/sanctum/csrf-cookie')
      const res = await axios.post<ApiResponse<AuthData>>('/api/v1/auth/login', payload)
      user.value = res.data.data.user
      token.value = res.data.data.user.token || ''
      sessionStorage.setItem('auth_user', JSON.stringify(user.value))
      console.log('[auth] login success:', {
        isAuthenticated: !!user.value,
        user: user.value,
        token: token.value,
      })
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Login failed'
      console.log('[auth] login failed:', error.value)
      return false
    } finally {
      loading.value = false
    }
  }

  async function register(payload: {
    name: string
    email: string
    password: string
    password_confirmation: string
  }) {
    loading.value = true
    error.value = ''
    try {
      await axios.get('/sanctum/csrf-cookie')
      const res = await axios.post<ApiResponse<AuthData>>('/api/v1/auth/register', payload)
      user.value = res.data.data.user
      token.value = res.data.data.user.token || ''
      sessionStorage.setItem('auth_user', JSON.stringify(user.value))
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Registration failed'
      return false
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    loading.value = true
    error.value = ''
    try {
      await axios.post('/api/v1/logout')
      user.value = null
      token.value = ''
      sessionStorage.removeItem('auth_user')
      console.log('[auth] logout: logged out')
    } catch (e: any) {
      error.value = 'Logout failed'
      console.log('[auth] logout failed:', error.value)
    } finally {
      loading.value = false
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchUser,
  }
})
