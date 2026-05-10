<template>
  <div class="grid gap-6 lg:grid-cols-[420px_1fr]">
    <form class="form-card rounded-2xl p-6" @submit.prevent="submit">
      <h2 class="mb-5 text-lg font-black" style="color: var(--text-heading)">{{ editing ? 'Edit Author' : 'Tambah Author' }}</h2>
      <div class="grid gap-4">
        <label class="field"><span>Nama *</span><input v-model="form.name" required class="input" /></label>
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="field"><span>Email</span><input v-model="form.email" type="email" class="input" /></label>
          <label class="field"><span>Phone</span><input v-model="form.phone" class="input" /></label>
        </div>
        <label class="field"><span>Institusi</span><input v-model="form.institution" class="input" placeholder="UII Dalwa" /></label>
        <label class="field"><span>Foto</span><input class="input" type="file" accept="image/*" @change="photoFile = $event.target.files[0]" /></label>
        <label class="field"><span>Bio</span><textarea v-model="form.bio" class="input min-h-[120px]"></textarea></label>
        <div class="flex gap-3">
          <button class="rounded-lg bg-accent px-5 py-2.5 text-sm font-black" style="color: var(--text-btn)">{{ editing ? 'Update' : 'Simpan' }}</button>
          <button v-if="editing" type="button" @click="resetForm" class="rounded-lg px-5 py-2.5 text-sm font-bold" style="border: 1px solid var(--border); color: var(--text-body)">Batal</button>
        </div>
      </div>
    </form>

    <div class="rounded-2xl p-2" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-lg font-black" style="color: var(--text-heading)">Authors</h2>
        <input v-model="search" @input="debouncedFetch" class="input sm:w-72" placeholder="Cari author..." />
      </div>
      <div v-if="loading" class="flex justify-center py-12">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
      </div>
      <div v-else class="divide-y" style="border-color: var(--border)">
        <div v-for="item in items" :key="item.id" class="flex items-center justify-between gap-4 p-4">
          <div class="flex items-center gap-3">
            <img v-if="item.photo" :src="storageUrl(item.photo)" class="h-12 w-12 rounded-full object-cover" />
            <div v-else class="flex h-12 w-12 items-center justify-center rounded-full bg-sky-500/10 text-sky-300">
              <span class="material-symbols-outlined">person</span>
            </div>
            <div>
              <p class="font-black" style="color: var(--text-heading)">{{ item.name }}</p>
              <p class="text-sm" style="color: var(--text-muted)">{{ item.institution || '-' }} · {{ item.books_count || 0 }} buku</p>
              <p class="text-xs" style="color: var(--text-muted)">{{ item.email || '-' }}</p>
            </div>
          </div>
          <div class="flex gap-1">
            <button @click="edit(item)" class="rounded-lg p-2 text-accent hover:bg-white/5"><span class="material-symbols-outlined">edit</span></button>
            <button @click="remove(item)" class="rounded-lg p-2 text-red-400 hover:bg-white/5"><span class="material-symbols-outlined">delete</span></button>
          </div>
        </div>
        <p v-if="items.length === 0" class="p-8 text-center text-sm" style="color: var(--text-muted)">Belum ada author</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const items = ref([])
const loading = ref(false)
const search = ref('')
const editing = ref(null)
const photoFile = ref(null)
const form = ref({ name: '', email: '', phone: '', institution: '', bio: '' })
let timer = null

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchData, 300)
}

async function fetchData() {
  loading.value = true
  const { data } = await api.get('/authors', { params: { search: search.value, per_page: 100 } })
  items.value = data.data || data || []
  loading.value = false
}

function edit(item) {
  editing.value = item
  form.value = {
    name: item.name || '',
    email: item.email || '',
    phone: item.phone || '',
    institution: item.institution || '',
    bio: item.bio || '',
  }
}

function resetForm() {
  editing.value = null
  photoFile.value = null
  form.value = { name: '', email: '', phone: '', institution: '', bio: '' }
}

async function submit() {
  const fd = new FormData()
  Object.entries(form.value).forEach(([key, value]) => {
    if (value) fd.append(key, value)
  })
  if (photoFile.value) fd.append('photo', photoFile.value)
  if (editing.value) {
    fd.append('_method', 'PUT')
    await api.post(`/authors/${editing.value.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
  } else {
    await api.post('/authors', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
  }
  resetForm()
  fetchData()
}

async function remove(item) {
  if (!confirm(`Hapus author "${item.name}"?`)) return
  await api.delete(`/authors/${item.id}`)
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
