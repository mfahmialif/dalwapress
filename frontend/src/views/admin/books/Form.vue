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

      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field">
          <span>Kategori *</span>
          <select v-model="form.category_id" required class="input">
            <option value="">Pilih kategori</option>
            <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
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

      <!-- Multi-Author Section -->
      <div class="rounded-xl p-4 border" style="border-color: var(--border); background: var(--bg-input)">
        <div class="flex items-center justify-between mb-3">
          <div>
            <span class="text-sm font-black" style="color: var(--text-heading)">Daftar Penulis / Authors *</span>
            <p class="text-xs" style="color: var(--text-muted)">Buku dapat memiliki lebih dari satu penulis (Multi-Author).</p>
          </div>
          <button type="button" @click="addAuthor" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold bg-accent text-[var(--text-btn)]">
            <span class="material-symbols-outlined text-[16px]">add</span>
            Tambah Penulis
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(item, idx) in selectedAuthors" :key="idx" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 rounded-lg p-2.5 bg-[var(--bg-card)] border" style="border-color: var(--border)">
            <span class="text-xs font-black px-2 py-1 rounded bg-[var(--bg-input)] text-center sm:text-left shrink-0" style="color: var(--text-muted)">
              #{{ idx + 1 }}
            </span>
            <div class="flex-1">
              <select v-model="item.author_id" required class="input py-2 text-sm">
                <option value="">-- Pilih Author --</option>
                <option v-for="auth in authors" :key="auth.id" :value="auth.id">
                  {{ auth.name }} {{ auth.institution ? `(${auth.institution})` : '' }}
                </option>
              </select>
            </div>
            <div class="w-full sm:w-48">
              <select v-model="item.role" class="input py-2 text-sm">
                <option value="Penulis Utama">Penulis Utama</option>
                <option value="Penulis Pendamping">Penulis Pendamping</option>
                <option value="Editor / Penyusun">Editor / Penyusun</option>
                <option value="Kontributor">Kontributor</option>
              </select>
            </div>
            <button
              v-if="selectedAuthors.length > 1"
              type="button"
              @click="removeAuthor(idx)"
              class="inline-flex items-center justify-center p-2 rounded-lg text-red-400 hover:bg-red-500/10 shrink-0"
              title="Hapus Penulis"
            >
              <span class="material-symbols-outlined text-[18px]">delete</span>
            </button>
          </div>
        </div>
      </div>

      <div class="grid gap-4 lg:grid-cols-5">
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
          <input v-model="form.pages" class="input" type="text" placeholder="xii + 250 hlm" />
        </label>
        <label class="field">
          <span>Ukuran (Dimensi)</span>
          <input v-model="form.size" class="input" placeholder="14.8 x 21 cm (A5)" />
        </label>
        <label class="field">
          <span>Bahasa</span>
          <input v-model="form.language" class="input" placeholder="Indonesia" />
        </label>
      </div>

      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field">
          <span>Cover (Sampul Buku)</span>
          <input class="input file-input" type="file" accept="image/*" @change="coverFile = $event.target.files[0]" />
        </label>
        <label class="field">
          <span>Full PDF (Naskah Lengkap)</span>
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
const selectedAuthors = ref([
  { author_id: '', role: 'Penulis Utama' }
])
const coverFile = ref(null)
const fullFile = ref(null)
const form = ref({
  category_id: '',
  author_id: '',
  title: '',
  isbn: '',
  year: '',
  publisher: 'UII Dalwa Press',
  pages: '',
  size: '',
  language: 'Indonesia',
  description: '',
  table_of_contents: '',
  tags: '',
  featured: false,
  status: 'draft',
})

function addAuthor() {
  selectedAuthors.value.push({
    author_id: '',
    role: selectedAuthors.value.length === 0 ? 'Penulis Utama' : 'Penulis Pendamping'
  })
}

function removeAuthor(index) {
  if (selectedAuthors.value.length > 1) {
    selectedAuthors.value.splice(index, 1)
  }
}

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
    size: data.size || '',
    language: data.language || '',
    description: data.description || '',
    table_of_contents: data.table_of_contents || '',
    tags: data.tags || '',
    featured: !!data.featured,
    status: data.status || 'draft',
  }

  if (data.authors && data.authors.length > 0) {
    selectedAuthors.value = data.authors.map((a, idx) => ({
      author_id: a.id,
      role: a.pivot?.role || (idx === 0 ? 'Penulis Utama' : 'Penulis Pendamping')
    }))
  } else if (data.author_id) {
    selectedAuthors.value = [
      { author_id: data.author_id, role: 'Penulis Utama' }
    ]
  }
}

async function submit() {
  saving.value = true
  error.value = ''
  try {
    const validAuthors = selectedAuthors.value.filter(a => a.author_id)
    if (!validAuthors.length) {
      error.value = 'Silakan pilih minimal 1 penulis untuk buku ini.'
      saving.value = false
      return
    }

    form.value.author_id = validAuthors[0].author_id

    const fd = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
      if (value !== null && value !== '') fd.append(key, key === 'featured' ? (value ? '1' : '0') : value)
    })

    validAuthors.forEach((item, index) => {
      fd.append(`authors_data[${index}][author_id]`, item.author_id)
      fd.append(`authors_data[${index}][role]`, item.role || (index === 0 ? 'Penulis Utama' : 'Penulis Pendamping'))
      fd.append(`author_ids[${index}]`, item.author_id)
    })

    if (coverFile.value) fd.append('cover', coverFile.value)
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
