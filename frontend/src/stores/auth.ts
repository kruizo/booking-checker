import { defineStore } from 'pinia'
import axiosInstance from '@/lib/axios'
import { ref, computed } from 'vue'
import axios from 'axios'
import Cookies from 'js-cookie'
import type { ApiResponse } from '@/types/api'
import { useApi } from '@/composables/useApi'

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
  const api = useApi()
  const cachedUser = sessionStorage.getItem('auth_user')
  const user = ref<AuthUser | null>(cachedUser ? JSON.parse(cachedUser) : null)
  const token = ref('')
  const loading = ref(false)
  const error = ref('')

  const isAuthenticated = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  async function fetchUser() {
    try {
      loading.value = true
      error.value = ''
      const res = await api.get<ApiResponse<AuthData>>('/user', {})
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
      const res = await api.post<ApiResponse<AuthData>>('/auth/login', payload)
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
      const res = await api.post<ApiResponse<AuthData>>('/auth/register', payload)
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
      await api.post('/logout', {})
      user.value = null
      token.value = ''
      sessionStorage.removeItem('auth_user')

      // Clear all cookies
      document.cookie.split(';').forEach((c) => {
        document.cookie = c
          .replace(/^ +/, '')
          .replace(/=.*/, '=;expires=' + new Date().toUTCString() + ';path=/')
      })

      console.log('[auth] logout: logged out')
    } catch (e: any) {
      // Even if logout fails on backend, clear local state
      user.value = null
      token.value = ''
      sessionStorage.removeItem('auth_user')
      error.value = 'Logout failed'
      console.log('[auth] logout failed:', error.value)
    } finally {
      loading.value = false
    }
  }

  async function toggleAdmin() {
    if (!user.value) return false

    loading.value = true
    error.value = ''
    try {
      const res = await api.patch<ApiResponse<AuthData>>(`/user/${user.value.id}/permission`, {})
      user.value = res.data.data.user
      sessionStorage.setItem('auth_user', JSON.stringify(user.value))
      console.log('[auth] toggleAdmin success:', user.value)
      return true
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Failed to toggle admin status'
      console.log('[auth] toggleAdmin failed:', error.value)
      return false
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
    toggleAdmin,
  }
})
