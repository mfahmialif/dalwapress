import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../axios'
import router from '../router'

export const useAuthStore = defineStore('auth', () => {
  // ── State ──
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || '')

  // ── Getters ──
  const isAuthenticated = computed(() => !!token.value)

  // ── Actions ──
  async function login(username, password) {
    const { data } = await api.post('/login', { username, password })

    token.value = data.token
    user.value = data.user

    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))

    return data
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // Token sudah expired / invalid, tetap logout di frontend
    }

    token.value = ''
    user.value = null
    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')

    router.push({ name: 'Login' })
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/user')
      user.value = data
      localStorage.setItem('auth_user', JSON.stringify(data))
      return data
    } catch {
      await logout()
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    login,
    logout,
    fetchUser,
  }
})
