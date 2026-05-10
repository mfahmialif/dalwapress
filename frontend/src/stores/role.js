import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useRoleStore = defineStore('role', () => {
  const roles = ref([])
  const pagination = ref({})
  const loading = ref(false)

  async function fetchRoles(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/roles', { params })
      roles.value = data.data
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

  async function createRole(payload) {
    const { data } = await api.post('/roles', payload)
    return data
  }

  async function updateRole(id, payload) {
    const { data } = await api.put(`/roles/${id}`, payload)
    return data
  }

  async function deleteRole(id) {
    await api.delete(`/roles/${id}`)
  }

  return { roles, pagination, loading, fetchRoles, createRole, updateRole, deleteRole }
})
