<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <router-link :to="{ name: 'AdminBookCreate' }" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-accent px-5 font-bold shadow-[0_0_15px_rgba(37, 99, 235,0.3)] transition hover:bg-accent/90" style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        Tambah Buku
      </router-link>
      <div class="flex flex-col gap-3 sm:flex-row">
        <input v-model="search" @input="debouncedFetch" class="filter-input h-10 rounded-xl px-4 text-sm outline-none" placeholder="Cari judul, ISBN, author..." />
        <select v-model="status" @change="fetchData" class="filter-input h-10 rounded-xl px-4 text-sm outline-none">
          <option value="">Semua Status</option>
          <option v-for="item in statusOptions" :key="item" :value="item">{{ statusLabel(item) }}</option>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div v-for="stat in stats" :key="stat.label" class="rounded-xl border p-4" style="background: var(--bg-card); border-color: var(--border)">
        <span class="material-symbols-outlined text-accent">{{ stat.icon }}</span>
        <p class="mt-2 text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ stat.label }}</p>
        <p class="text-2xl font-black" style="color: var(--text-heading)">{{ stat.value }}</p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <div v-else class="overflow-hidden rounded-xl shadow-2xl" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left">
          <thead>
            <tr style="background: var(--bg-input)">
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Buku</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Kategori</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Author</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Tahun</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Status</th>
              <th class="px-4 py-4 text-right text-sm font-bold" style="color: var(--text-heading)">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="books.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada data buku</td>
            </tr>
            <tr v-for="book in books" :key="book.id" class="table-row">
              <td class="px-4 py-4">
                <div class="flex items-center gap-3">
                  <div class="h-16 w-11 overflow-hidden rounded-lg bg-slate-800 bg-cover bg-center" :style="{ backgroundImage: book.cover ? `url('${storageUrl(book.cover)}')` : '' }">
                    <div v-if="!book.cover" class="flex h-full items-center justify-center text-accent">
                      <span class="material-symbols-outlined text-[20px]">menu_book</span>
                    </div>
                  </div>
                  <div>
                    <p class="line-clamp-1 text-sm font-black" style="color: var(--text-heading)">{{ book.title }}</p>
                    <p class="text-xs" style="color: var(--text-muted)">ISBN: {{ book.isbn || '-' }}</p>
                    <p class="text-xs" style="color: var(--text-muted)">{{ book.views }} views · {{ book.downloads }} downloads</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ book.category?.name || '-' }}</td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ book.author?.name || '-' }}</td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-muted)">{{ book.year || '-' }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(book.status)">{{ statusLabel(book.status) }}</span></td>
              <td class="px-4 py-4 text-right">
                <router-link :to="{ name: 'AdminBookEdit', params: { id: book.id } }" class="inline-flex rounded-lg p-2 text-accent hover:bg-white/5">
                  <span class="material-symbols-outlined text-[20px]">edit</span>
                </router-link>
                <button @click="removeBook(book)" class="inline-flex rounded-lg p-2 text-red-400 hover:bg-white/5">
                  <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t px-5 py-4" style="border-color: var(--border)">
        <p class="text-sm" style="color: var(--text-muted)">Total {{ total }} buku</p>
        <div class="flex items-center gap-2">
          <button @click="goPage(page - 1)" :disabled="page <= 1" class="page-btn">‹</button>
          <span class="text-sm font-bold" style="color: var(--text-heading)">{{ page }} / {{ lastPage }}</span>
          <button @click="goPage(page + 1)" :disabled="page >= lastPage" class="page-btn">›</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const books = ref([])
const loading = ref(false)
const search = ref('')
const status = ref('')
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const perPage = 10
const statusOptions = ['draft', 'review', 'published', 'archived']
const stats = ref([
  { label: 'Total Buku', value: 0, icon: 'menu_book' },
  { label: 'Published', value: 0, icon: 'verified' },
  { label: 'Featured', value: 0, icon: 'stars' },
  { label: 'Review', value: 0, icon: 'rate_review' },
])

let timer = null
function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(() => {
    page.value = 1
    fetchData()
  }, 300)
}

async function fetchData() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage }
    if (search.value) params.search = search.value
    if (status.value) params.status = status.value
    const { data } = await api.get('/books', { params })
    books.value = data.data || []
    page.value = data.current_page || 1
    lastPage.value = data.last_page || 1
    total.value = data.total || 0
  } finally {
    loading.value = false
  }
}

async function fetchStats() {
  const [all, published, featured, review] = await Promise.all([
    api.get('/books', { params: { per_page: 1 } }),
    api.get('/books', { params: { per_page: 1, status: 'published' } }),
    api.get('/books', { params: { per_page: 1, featured: 1 } }),
    api.get('/books', { params: { per_page: 1, status: 'review' } }),
  ])
  stats.value[0].value = all.data.total
  stats.value[1].value = published.data.total
  stats.value[2].value = featured.data.total
  stats.value[3].value = review.data.total
}

function goPage(next) {
  if (next < 1 || next > lastPage.value) return
  page.value = next
  fetchData()
}

async function removeBook(book) {
  if (!confirm(`Hapus buku "${book.title}"?`)) return
  await api.delete(`/books/${book.id}`)
  await fetchData()
  await fetchStats()
}

function statusLabel(value) {
  return {
    draft: 'Draft',
    review: 'Review',
    published: 'Published',
    archived: 'Archived',
  }[value] || value
}

function statusBadge(value) {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-black'
  if (value === 'published') return `${base} bg-green-500/10 text-green-400 border border-green-500/30`
  if (value === 'review') return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
  if (value === 'archived') return `${base} bg-slate-500/10 text-slate-400 border border-slate-500/30`
  return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`
}

onMounted(() => {
  fetchData()
  fetchStats()
})
</script>

<style scoped>
.filter-input { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-heading); }
.table-row { border-top: 1px solid var(--border); transition: background 0.2s ease; }
.table-row:hover { background: var(--bg-input); }
.page-btn { width: 34px; height: 34px; border-radius: 10px; color: var(--text-heading); background: var(--bg-input); border: 1px solid var(--border); }
.page-btn:disabled { opacity: 0.4; }
</style>
