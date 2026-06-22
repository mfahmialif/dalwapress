<template>
  <div class="grid gap-6 lg:grid-cols-[400px_1fr]">
    <form class="form-card rounded-2xl p-6" @submit.prevent="submit">
      <h2 class="mb-5 text-lg font-black" style="color: var(--text-heading)">{{ editing ? 'Edit Kategori News' : 'Tambah Kategori News' }}</h2>
      <div class="grid gap-4">
        <label class="field">
          <span>Nama Kategori *</span>
          <input v-model="form.name" required class="input" placeholder="Opini Pesantren" />
        </label>

        <label class="field">
          <span>Tipe *</span>
          <select v-model="form.type" required class="input">
            <option v-for="item in typeOptions" :key="item" :value="item">{{ item }}</option>
          </select>
        </label>

        <label class="field">
          <span>Deskripsi</span>
          <textarea v-model="form.description" class="input min-h-[130px]" placeholder="Deskripsi singkat kategori"></textarea>
        </label>

        <label class="flex items-center justify-between gap-4 rounded-xl px-4 py-3" style="background: var(--bg-input); border: 1px solid var(--border)">
          <span class="text-sm font-bold" style="color: var(--text-body)">Aktif</span>
          <input v-model="form.is_active" type="checkbox" class="h-5 w-5 accent-blue-600" />
        </label>

        <div v-if="error" class="rounded-lg border px-3 py-2 text-sm font-bold text-red-400" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.3)">
          {{ error }}
        </div>

        <div class="flex gap-3">
          <button :disabled="saving" class="inline-flex items-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-black disabled:opacity-60" style="color: var(--text-btn)">
            <span v-if="saving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
            {{ editing ? 'Update' : 'Simpan' }}
          </button>
          <button v-if="editing" type="button" @click="resetForm" class="rounded-lg px-5 py-2.5 text-sm font-bold" style="border: 1px solid var(--border); color: var(--text-body)">Batal</button>
        </div>
      </div>
    </form>

    <div class="rounded-2xl p-2" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="flex flex-col gap-3 p-4 xl:flex-row xl:items-center xl:justify-between">
        <div>
          <h2 class="text-lg font-black" style="color: var(--text-heading)">Kategori News</h2>
          <p class="text-sm" style="color: var(--text-muted)">Kelola kategori untuk artikel, video, dan gambar.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
          <input v-model="search" @input="debouncedFetch" class="input sm:w-64" placeholder="Cari kategori..." />
          <select v-model="typeFilter" @change="fetchData" class="input sm:w-40">
            <option value="all">Semua Tipe</option>
            <option v-for="item in typeOptions" :key="item" :value="item">{{ item }}</option>
          </select>
          <select v-model="activeFilter" @change="fetchData" class="input sm:w-36">
            <option value="all">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr style="background: var(--bg-input)">
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Kategori</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Tipe</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Status</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">News</th>
              <th class="px-4 py-4 text-right text-sm font-bold" style="color: var(--text-heading)">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="5" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada kategori news</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="table-row">
              <td class="px-4 py-4">
                <p class="font-black" style="color: var(--text-heading)">{{ item.name }}</p>
                <p class="text-sm" style="color: var(--text-muted)">{{ item.description || 'Tanpa deskripsi' }}</p>
                <p class="mt-1 text-xs" style="color: var(--text-muted)">/{{ item.slug }}</p>
              </td>
              <td class="px-4 py-4"><span :class="typeBadge(item.type)">{{ item.type }}</span></td>
              <td class="px-4 py-4"><span :class="statusBadge(item.is_active)">{{ item.is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
              <td class="px-4 py-4 text-sm font-bold" style="color: var(--text-body)">{{ item.news_count || 0 }} news</td>
              <td class="px-4 py-4 text-right">
                <button @click="edit(item)" class="rounded-lg p-2 text-accent hover:bg-white/5" title="Edit">
                  <span class="material-symbols-outlined text-[20px]">edit</span>
                </button>
                <button @click="remove(item)" class="rounded-lg p-2 text-red-400 hover:bg-white/5" title="Hapus">
                  <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'

const typeOptions = ['Artikel', 'Video', 'Gambar']
const items = ref([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const search = ref('')
const typeFilter = ref('all')
const activeFilter = ref('all')
const editing = ref(null)
const form = ref({ name: '', type: 'Artikel', description: '', is_active: true })
let timer = null

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchData, 300)
}

async function fetchData() {
  loading.value = true
  try {
    const params = { search: search.value, type: typeFilter.value, is_active: activeFilter.value, per_page: 100 }
    const { data } = await api.get('/admin-news-categories', { params })
    items.value = data.data || data || []
  } finally {
    loading.value = false
  }
}

function edit(item) {
  editing.value = item
  form.value = {
    name: item.name,
    type: item.type || 'Artikel',
    description: item.description || '',
    is_active: Boolean(item.is_active),
  }
  error.value = ''
}

function resetForm() {
  editing.value = null
  form.value = { name: '', type: 'Artikel', description: '', is_active: true }
  error.value = ''
}

async function submit() {
  saving.value = true
  error.value = ''
  try {
    if (editing.value) await api.put(`/admin-news-categories/${editing.value.id}`, form.value)
    else await api.post('/admin-news-categories', form.value)
    resetForm()
    await fetchData()
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Gagal menyimpan kategori.')
  } finally {
    saving.value = false
  }
}

async function remove(item) {
  if (!confirm(`Hapus kategori "${item.name}"?`)) return
  try {
    await api.delete(`/admin-news-categories/${item.id}`)
    await fetchData()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus kategori.')
  }
}

function typeBadge(type) {
  const base = 'app-badge'
  if (type === 'Video') return `${base} app-badge--red`
  if (type === 'Gambar') return `${base} app-badge--sky`
  return `${base} app-badge--blue`
}

function statusBadge(active) {
  return active ? 'app-badge app-badge--success' : 'app-badge app-badge--muted'
}

onMounted(fetchData)
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.field { display: flex; flex-direction: column; gap: 0.4rem; color: var(--text-body); font-size: 0.875rem; font-weight: 700; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(37, 99, 235, 0.18); }
.table-row { border-top: 1px solid var(--border); transition: background 0.2s ease; }
.table-row:hover { background: var(--hover-nav-bg); }
</style>
