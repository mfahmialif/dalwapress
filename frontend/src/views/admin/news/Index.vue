<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <router-link :to="{ name: 'AdminNewsCreate' }" class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(37, 99, 235,0.3)] shrink-0 cursor-pointer active:scale-95" style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah News</span>
      </router-link>
    </div>

    <!-- ═══ STATS ROW ═══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <div v-for="stat in statsCards" :key="stat.label" class="stat-card rounded-xl p-4 flex items-center gap-4 border border-transparent">
        <div class="p-3 rounded-lg" :class="stat.iconBg">
          <span class="material-symbols-outlined text-[24px]" :class="stat.iconColor">{{ stat.icon }}</span>
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ stat.label }}</p>
          <p class="text-2xl font-bold" style="color: var(--text-heading)">{{ stat.value }}</p>
        </div>
      </div>
    </div>

    <!-- ═══ FILTERS BAR ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery" @input="debouncedFetch" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Cari news..." type="text" />
      </div>
      <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 lg:gap-4">
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Kategori:</span>
          <VueMultiselect v-model="filterCategory" :options="categoryOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" @select="fetchData" class="flex-1 sm:w-[160px] sm:flex-none" />
        </div>
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Status:</span>
          <VueMultiselect v-model="filterStatus" :options="statusOptions" :close-on-select="true" :searchable="false" :allow-empty="false" :show-labels="false" label="name" track-by="value" @select="fetchData" class="flex-1 sm:w-[150px] sm:flex-none" />
        </div>
      </div>
    </div>

    <!-- ═══ LOADING ═══ -->
    <div v-if="loading" class="flex justify-center py-12">
      <span class="material-symbols-outlined text-accent text-4xl animate-spin">progress_activity</span>
    </div>

    <!-- ═══ CONTENT TABLE ═══ -->
    <div v-else class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold w-16" style="color: var(--text-heading)">#</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Thumbnail</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Judul</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Kategori</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Waktu</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
            <th class="px-4 py-4 text-sm font-semibold text-right" style="color: var(--text-heading)">Actions</th>
          </tr></thead>
          <tbody class="table-body">
            <tr v-if="items.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Tidak ada data news</td>
            </tr>
            <tr v-for="(item, idx) in items" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4 text-sm font-mono" style="color: var(--text-muted)">{{ (currentPage - 1) * perPage + idx + 1 }}</td>
              <td class="px-4 py-4">
                <div class="w-16 h-10 rounded-lg overflow-hidden bg-cover bg-center border"
                     :style="{ backgroundImage: item.image_path ? `url('${storageUrl(item.image_path)}')` : `url('/img/default-news.png')`, borderColor: 'var(--border)' }">
                  <div v-if="item.category?.type === 'Video'" class="w-full h-full flex items-center justify-center bg-black/40">
                    <span class="material-symbols-outlined text-white text-[18px]">play_arrow</span>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4"><span class="text-sm font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</span></td>
              <td class="px-4 py-4"><span :class="categoryBadge(item.category?.type)">{{ item.category?.name || '-' }}</span></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-muted)">{{ timeAgo(item.created_at) }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <router-link :to="{ name: 'AdminNewsEdit', params: { id: item.id } }" class="action-btn p-2 rounded-lg cursor-pointer" title="Edit"><span class="material-symbols-outlined text-[20px] text-accent">edit</span></router-link>
                  <button @click="confirmDelete(item)" class="action-btn action-btn-delete p-2 rounded-lg cursor-pointer" title="Delete"><span class="material-symbols-outlined text-[20px] text-accent">delete</span></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="pagination-bar flex items-center justify-between px-6 py-4">
        <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ items.length }} of {{ totalItems }} items</span>
        <div class="flex items-center gap-1.5 ml-auto">
          <button @click="goPage(currentPage - 1)" :disabled="currentPage <= 1" class="page-btn p-2 rounded-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"><span class="material-symbols-outlined text-[20px]">chevron_left</span></button>
          <template v-for="p in totalPages" :key="p">
            <button @click="goPage(p)" :class="p === currentPage ? 'page-btn-active' : 'page-btn'" class="w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center cursor-pointer">{{ p }}</button>
          </template>
          <button @click="goPage(currentPage + 1)" :disabled="currentPage >= totalPages" class="page-btn p-2 rounded-lg cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"><span class="material-symbols-outlined text-[20px]">chevron_right</span></button>
        </div>
      </div>
    </div>

    <!-- ═══ DELETE CONFIRM ═══ -->
    <Transition name="modal">
      <div v-if="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showDeleteConfirm = false"></div>
        <div class="relative w-full max-w-md rounded-2xl p-6 shadow-2xl text-center" style="background: var(--bg-card); border: 1px solid var(--border)">
          <span class="material-symbols-outlined text-red-400 text-5xl mb-3">warning</span>
          <h3 class="text-lg font-bold mb-2" style="color: var(--text-heading)">Hapus News?</h3>
          <p class="text-sm mb-6" style="color: var(--text-muted)">{{ deletingItem?.title }}</p>
          <div class="flex justify-center gap-3">
            <button @click="showDeleteConfirm = false" class="px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer" style="color: var(--text-muted); background: var(--bg-input); border: 1px solid var(--border)">Batal</button>
            <button @click="deleteItem" :disabled="deleting" class="px-5 py-2.5 rounded-lg text-sm font-bold cursor-pointer bg-red-500 text-white disabled:opacity-50">{{ deleting ? 'Menghapus...' : 'Hapus' }}</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ═══ TOAST ═══ -->
    <Transition name="toast">
      <div v-if="toast.show" class="fixed top-6 right-6 z-60 max-w-sm" :class="toast.type === 'success' ? 'toast-success' : 'toast-error'">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg border">
          <span class="material-symbols-outlined text-lg">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
          <span class="text-sm font-medium">{{ toast.message }}</span>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

// ── State ──
const loading = ref(false)
const items = ref([])
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const perPage = 10

const searchQuery = ref('')
const categoryOptions = ref([{ name: 'Semua', value: 'all' }])
const statusOptions = [{ name: 'All Status', value: 'all' }, { name: 'Published', value: 'Published' }, { name: 'Draft', value: 'Draft' }]
const filterCategory = ref(categoryOptions[0])
const filterStatus = ref(statusOptions[0])

// ── Stats ──
const statsCards = ref([
  { label: 'Total News', value: 0, icon: 'article', iconBg: 'bg-accent/10', iconColor: 'text-accent' },
  { label: 'Kategori', value: 0, icon: 'category', iconBg: 'bg-blue-500/10', iconColor: 'text-blue-400' },
  { label: 'Published', value: 0, icon: 'task_alt', iconBg: 'bg-green-500/10', iconColor: 'text-green-400' },
  { label: 'Draft', value: 0, icon: 'draft', iconBg: 'bg-blue-500/10', iconColor: 'text-blue-400' },
])

// ── Delete ──
const showDeleteConfirm = ref(false)
const deletingItem = ref(null)
const deleting = ref(false)

// ── Toast ──
const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimeout = null
function showToast(msg, type = 'success') {
  clearTimeout(toastTimeout)
  toast.show = true; toast.message = msg; toast.type = type
  toastTimeout = setTimeout(() => { toast.show = false }, 4000)
}

// ── Fetch ──
let debounceTimer = null
function debouncedFetch() { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => fetchData(), 300) }

async function fetchData() {
  loading.value = true
  try {
    const params = { page: currentPage.value, per_page: perPage }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterCategory.value?.value !== 'all') params.category_id = filterCategory.value.value
    if (filterStatus.value?.value !== 'all') params.status = filterStatus.value.value

    const { data } = await api.get('/news', { params })
    const rows = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : [])
    items.value = rows
    totalPages.value = data?.last_page || 1
    totalItems.value = data?.total || rows.length
    currentPage.value = data?.current_page || currentPage.value
  } catch (e) {
    console.error('Failed to load news:', e)
    showToast(e.response?.data?.message || 'Gagal memuat data', 'error')
  }
  loading.value = false
}

async function fetchStats() {
  try {
    const [all, published, draft, categories] = await Promise.all([
      api.get('/news', { params: { per_page: 1 } }),
      api.get('/news', { params: { per_page: 1, status: 'Published' } }),
      api.get('/news', { params: { per_page: 1, status: 'Draft' } }),
      api.get('/news-categories'),
    ])
    statsCards.value[0].value = all.data.total
    statsCards.value[1].value = categories.data.length
    statsCards.value[2].value = published.data.total
    statsCards.value[3].value = draft.data.total
  } catch { /* silent */ }
}

async function fetchCategories() {
  try {
    const { data } = await api.get('/news-categories')
    categoryOptions.value = [
      { name: 'Semua', value: 'all' },
      ...(data || []).map((item) => ({ name: item.name, value: item.id, type: item.type })),
    ]
    filterCategory.value = categoryOptions.value[0]
  } catch (e) {
    console.error('Failed to load news categories:', e)
    categoryOptions.value = [{ name: 'Semua', value: 'all' }]
    filterCategory.value = categoryOptions.value[0]
  }
}

function goPage(p) { if (p < 1 || p > totalPages.value) return; currentPage.value = p; fetchData() }

// ── Delete ──
function confirmDelete(item) { deletingItem.value = item; showDeleteConfirm.value = true }
async function deleteItem() {
  deleting.value = true
  try {
    await api.delete(`/news/${deletingItem.value.id}`)
    showToast('News berhasil dihapus')
    showDeleteConfirm.value = false
    fetchData(); fetchStats()
  } catch (e) { showToast(e.response?.data?.message || 'Gagal menghapus', 'error') }
  deleting.value = false
}

// ── Helpers ──
function timeAgo(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr); const now = new Date(); const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`
  if (diff < 172800) return '1 hari lalu'
  return `${Math.floor(diff / 86400)} hari lalu`
}

function categoryBadge(c) {
  const b = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  if (c === 'Artikel') return `${b} bg-accent/20 text-accent border border-accent/30`
  if (c === 'Video') return `${b} bg-red-900/30 text-red-400 border border-red-800/40`
  if (c === 'Gambar') return `${b} bg-blue-900/30 text-blue-400 border border-blue-800/40`
  return `${b} bg-slate-800/50 text-slate-400 border border-slate-700/50`
}

function statusBadge(s) {
  const b = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  if (s === 'Published') return `${b} text-green-400 border border-green-500/30 bg-green-900/20 shadow-[0_0_10px_rgba(74,222,128,0.3)]`
  return `${b} text-blue-400 border border-blue-500/30 bg-blue-900/20 shadow-[0_0_10px_rgba(37,99,235,0.2)]`
}

onMounted(async () => { await fetchCategories(); fetchData(); fetchStats() })
</script>

<style scoped>
.filter-input { background: var(--bg-card); border: 1px solid var(--border); color: var(--text-heading); transition: box-shadow 0.3s ease; }
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(37, 99, 235, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(37, 99, 235, 0.3); }
.action-btn { color: var(--text-muted); }
.action-btn:hover { color: var(--color-accent); background: var(--bg-input); }
.action-btn-delete:hover { color: #f87171; background: var(--bg-input); }
.pagination-bar { border-top: 1px solid var(--border); background: var(--bg-card); }
.page-btn { color: var(--text-muted); border: 1px solid transparent; transition: all 0.2s ease; }
.page-btn:hover { background: var(--bg-input); color: var(--text-heading); }
.page-btn-active { background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 10px rgba(37, 99, 235, 0.4); }

.modal-enter-active { animation: modalIn 0.3s ease-out; }
.modal-leave-active { animation: modalOut 0.2s ease-in; }
@keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
@keyframes modalOut { from { opacity: 1; } to { opacity: 0; } }

.toast-success { background: #065f46; border-color: #10b981; color: #6ee7b7; }
.toast-error { background: #7f1d1d; border-color: #ef4444; color: #fca5a5; }
.toast-enter-active { animation: toastIn 0.3s ease-out; }
.toast-leave-active { animation: toastOut 0.2s ease-in; }
@keyframes toastIn { from { opacity: 0; transform: translateX(100px); } to { opacity: 1; transform: translateX(0); } }
@keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateX(100px); } }
</style>
