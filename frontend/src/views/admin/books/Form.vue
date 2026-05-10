<template>
  <div class="flex flex-col gap-6">
    <div class="flex items-center gap-4">
      <router-link to="/administrator/books" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-black" style="color: var(--text-heading)">{{ isEdit ? 'Edit Buku' : 'Tambah Buku' }}</h2>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <form v-else class="form-card grid gap-6 rounded-2xl p-6" @submit.prevent="submit">
      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field">
          <span>Judul Buku *</span>
          <input v-model="form.title" required class="input" placeholder="Judul buku" />
        </label>
        <label class="field">
          <span>ISBN</span>
          <input v-model="form.isbn" class="input" placeholder="978-..." />
        </label>
      </div>

      <div class="grid gap-4 lg:grid-cols-3">
        <label class="field">
          <span>Kategori *</span>
          <select v-model="form.category_id" required class="input">
            <option value="">Pilih kategori</option>
            <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </label>
        <label class="field">
          <span>Author *</span>
          <select v-model="form.author_id" required class="input">
            <option value="">Pilih author</option>
            <option v-for="item in authors" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </label>
        <label class="field">
          <span>Status *</span>
          <select v-model="form.status" required class="input">
            <option value="draft">Draft</option>
            <option value="review">Review</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </label>
      </div>

      <div class="grid gap-4 lg:grid-cols-4">
        <label class="field">
          <span>Tahun</span>
          <input v-model="form.year" class="input" type="number" min="1000" max="9999" placeholder="2026" />
        </label>
        <label class="field">
          <span>Penerbit</span>
          <input v-model="form.publisher" class="input" placeholder="UII Dalwa Press" />
        </label>
        <label class="field">
          <span>Halaman</span>
          <input v-model="form.pages" class="input" type="number" min="1" placeholder="240" />
        </label>
        <label class="field">
          <span>Bahasa</span>
          <input v-model="form.language" class="input" placeholder="Indonesia" />
        </label>
      </div>

      <div class="grid gap-4 lg:grid-cols-3">
        <label class="field">
          <span>Cover</span>
          <input class="input file-input" type="file" accept="image/*" @change="coverFile = $event.target.files[0]" />
        </label>
        <label class="field">
          <span>Preview PDF</span>
          <input class="input file-input" type="file" accept="application/pdf" @change="previewFile = $event.target.files[0]" />
        </label>
        <label class="field">
          <span>Full PDF</span>
          <input class="input file-input" type="file" accept="application/pdf" @change="fullFile = $event.target.files[0]" />
        </label>
      </div>

      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field">
          <span>Deskripsi</span>
          <textarea v-model="form.description" class="input min-h-[150px]" placeholder="Ringkasan buku"></textarea>
        </label>
        <label class="field">
          <span>Daftar Isi</span>
          <textarea v-model="form.table_of_contents" class="input min-h-[150px]" placeholder="Bab 1..."></textarea>
        </label>
      </div>

      <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <label class="field">
          <span>Tags</span>
          <input v-model="form.tags" class="input" placeholder="akademik, pesantren, riset" />
        </label>
        <label class="flex h-11 items-center gap-3 rounded-xl border px-4" style="border-color: var(--border); color: var(--text-body)">
          <input v-model="form.featured" type="checkbox" />
          Featured
        </label>
      </div>

      <div v-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ error }}</div>

      <div class="flex justify-end gap-3">
        <router-link to="/administrator/books" class="rounded-lg px-5 py-2.5 text-sm font-bold" style="border: 1px solid var(--border); color: var(--text-body)">Batal</router-link>
        <button :disabled="saving" class="rounded-lg bg-accent px-6 py-2.5 text-sm font-black disabled:opacity-60" style="color: var(--text-btn)">
          {{ saving ? 'Menyimpan...' : 'Simpan Buku' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../../axios'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const categories = ref([])
const authors = ref([])
const coverFile = ref(null)
const previewFile = ref(null)
const fullFile = ref(null)
const form = ref({
  category_id: '',
  author_id: '',
  title: '',
  isbn: '',
  year: '',
  publisher: 'UII Dalwa Press',
  pages: '',
  language: 'Indonesia',
  description: '',
  table_of_contents: '',
  tags: '',
  featured: false,
  status: 'draft',
})

async function loadOptions() {
  const [categoryRes, authorRes] = await Promise.all([
    api.get('/book-categories', { params: { all: 1 } }),
    api.get('/authors', { params: { all: 1 } }),
  ])
  categories.value = categoryRes.data || []
  authors.value = authorRes.data || []
}

async function loadBook() {
  const { data } = await api.get(`/books/${route.params.id}`)
  form.value = {
    category_id: data.category_id || '',
    author_id: data.author_id || '',
    title: data.title || '',
    isbn: data.isbn || '',
    year: data.year || '',
    publisher: data.publisher || '',
    pages: data.pages || '',
    language: data.language || '',
    description: data.description || '',
    table_of_contents: data.table_of_contents || '',
    tags: data.tags || '',
    featured: !!data.featured,
    status: data.status || 'draft',
  }
}

async function submit() {
  saving.value = true
  error.value = ''
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
      if (value !== null && value !== '') fd.append(key, key === 'featured' ? (value ? '1' : '0') : value)
    })
    if (coverFile.value) fd.append('cover', coverFile.value)
    if (previewFile.value) fd.append('preview_file', previewFile.value)
    if (fullFile.value) fd.append('full_file', fullFile.value)

    if (isEdit.value) {
      fd.append('_method', 'PUT')
      await api.post(`/books/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/books', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    router.push({ name: 'AdminBooks' })
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Gagal menyimpan buku.')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  loading.value = true
  await loadOptions()
  if (isEdit.value) await loadBook()
  loading.value = false
})
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.field { display: flex; flex-direction: column; gap: 0.4rem; color: var(--text-body); font-size: 0.875rem; font-weight: 700; }
.input { width: 100%; border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 1px var(--color-accent); }
.file-input { padding: 0.55rem; }
</style>
