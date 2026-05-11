import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../axios'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const hasCheckedSession = ref(false)

  // Getters
  const isAuthenticated = computed(() => !!user.value)

  function clearAuthState() {
    user.value = null
    hasCheckedSession.value = true
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
  }

  // Actions
  async function login(username, password, remember = false) {
    const { data } = await api.post('/login', { username, password, remember })

    user.value = data.user
    hasCheckedSession.value = true

    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')

    return data
  }

  async function logout(redirectToLogin = true) {
    try {
      await api.post('/logout')
    } catch {
      // Session sudah expired / invalid, tetap logout di frontend.
    }

    clearAuthState()

    if (redirectToLogin && typeof window !== 'undefined') {
      window.dispatchEvent(new Event('auth:redirect-login'))
    }
  }

  async function fetchUser(force = false) {
    if (hasCheckedSession.value && !force) {
      return user.value
    }

    try {
      const { data } = await api.get('/user', { skipAuthRedirect: true })
      user.value = data
      hasCheckedSession.value = true
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      return data
    } catch {
      clearAuthState()
      return null
    }
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('auth:unauthorized', clearAuthState)
  }

  return {
    user,
    hasCheckedSession,
    isAuthenticated,
    login,
    logout,
    fetchUser,
    clearAuthState,
  }
})
