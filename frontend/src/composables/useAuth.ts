import { useAuthStore } from '@/stores/auth'
import { storeToRefs } from 'pinia'

export function useAuth() {
  const auth = useAuthStore()
  const { user, token, loading, error, isAuthenticated, isAdmin } = storeToRefs(auth)
  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login: auth.login,
    register: auth.register,
    logout: auth.logout,
    fetchUser: auth.fetchUser,
  }
}
