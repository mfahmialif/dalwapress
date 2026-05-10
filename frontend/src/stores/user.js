import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useUserStore = defineStore('user', () => {
  const users = ref([])
  const pagination = ref({})
  const loading = ref(false)

  async function fetchUsers(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/users', { params })
      users.value = data.data
      pagination.value = {
        currentPage: data.current_page,
        lastPage: data.last_page,
        total: data.total,
        perPage: data.per_page,
        from: data.from,
        to: data.to,
      }
    } finally {
      loading.value = false
    }
  }

  async function createUser(payload) {
    const { data } = await api.post('/users', payload)
    return data
  }

  async function updateUser(id, payload) {
    const { data } = await api.put(`/users/${id}`, payload)
    return data
  }

  async function deleteUser(id) {
    await api.delete(`/users/${id}`)
  }

  return { users, pagination, loading, fetchUsers, createUser, updateUser, deleteUser }
})
