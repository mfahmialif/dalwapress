import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../axios'

export const useNewsStore = defineStore('news', () => {
  const newsList = ref([])
  const pagination = ref({})
  const loading = ref(false)

  async function fetchNews(params = {}) {
    loading.value = true
    try {
      const { data } = await api.get('/news', { params })
      newsList.value = data.data
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

  async function fetchNewsItem(id) {
    const { data } = await api.get(`/news/${id}`)
    return data
  }

  async function createNews(formData) {
    const { data } = await api.post('/news', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function updateNews(id, formData) {
    formData.append('_method', 'PUT')
    const { data } = await api.post(`/news/${id}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  }

  async function deleteNews(id) {
    await api.delete(`/news/${id}`)
  }

  return { newsList, pagination, loading, fetchNews, fetchNewsItem, createNews, updateNews, deleteNews }
})
