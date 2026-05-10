<template>
  <div class="grid gap-6 lg:grid-cols-[380px_1fr]">
    <form class="form-card rounded-2xl p-6" @submit.prevent="submit">
      <h2 class="mb-5 text-lg font-black" style="color: var(--text-heading)">{{ editing ? 'Edit Kategori' : 'Tambah Kategori Buku' }}</h2>
      <div class="grid gap-4">
        <label class="field">
          <span>Nama Kategori *</span>
          <input v-model="form.name" required class="input" placeholder="Buku Akademik" />
        </label>
        <label class="field">
          <span>Deskripsi</span>
          <textarea v-model="form.description" class="input min-h-[140px]" placeholder="Deskripsi kategori"></textarea>
        </label>
        <div class="flex gap-3">
          <button class="rounded-lg bg-accent px-5 py-2.5 text-sm font-black" style="color: var(--text-btn)">{{ editing ? 'Update' : 'Simpan' }}</button>
          <button v-if="editing" type="button" @click="resetForm" class="rounded-lg px-5 py-2.5 text-sm font-bold" style="border: 1px solid var(--border); color: var(--text-body)">Batal</button>
        </div>
      </div>
    </form>

    <div class="rounded-2xl p-2" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-black" style="color: var(--text-heading)">Kategori Buku</h2>
        <input v-model="search" @input="debouncedFetch" class="input sm:w-72" placeholder="Cari kategori..." />
      </div>
      <div v-if="loading" class="flex justify-center py-12">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
      </div>
      <div v-else class="divide-y" style="border-color: var(--border)">
        <div v-for="item in items" :key="item.id" class="flex items-center justify-between gap-4 p-4">
          <div>
            <p class="font-black" style="color: var(--text-heading)">{{ item.name }}</p>
            <p class="text-sm" style="color: var(--text-muted)">{{ item.description || 'Tanpa deskripsi' }}</p>
            <p class="mt-1 text-xs" style="color: var(--text-muted)">{{ item.books_count || 0 }} buku · /{{ item.slug }}</p>
          </div>
          <div class="flex gap-1">
            <button @click="edit(item)" class="rounded-lg p-2 text-accent hover:bg-white/5"><span class="material-symbols-outlined">edit</span></button>
            <button @click="remove(item)" class="rounded-lg p-2 text-red-400 hover:bg-white/5"><span class="material-symbols-outlined">delete</span></button>
          </div>
        </div>
        <p v-if="items.length === 0" class="p-8 text-center text-sm" style="color: var(--text-muted)">Belum ada kategori</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'

const items = ref([])
const loading = ref(false)
const search = ref('')
const editing = ref(null)
const form = ref({ name: '', description: '' })
let timer = null

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchData, 300)
}

async function fetchData() {
  loading.value = true
  const { data } = await api.get('/book-categories', { params: { search: search.value, per_page: 100 } })
  items.value = data.data || data || []
  loading.value = false
}

function edit(item) {
  editing.value = item
  form.value = { name: item.name, description: item.description || '' }
}

function resetForm() {
  editing.value = null
  form.value = { name: '', description: '' }
}

async function submit() {
  if (editing.value) await api.put(`/book-categories/${editing.value.id}`, form.value)
  else await api.post('/book-categories', form.value)
  resetForm()
  fetchData()
}

async function remove(item) {
  if (!confirm(`Hapus kategori "${item.name}"?`)) return
  await api.delete(`/book-categories/${item.id}`)
  fetchData()
}

onMounted(fetchData)
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.field { display: flex; flex-direction: column; gap: 0.4rem; color: var(--text-body); font-size: 0.875rem; font-weight: 700; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); }
</style>
