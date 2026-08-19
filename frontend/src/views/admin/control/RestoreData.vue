<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-black tracking-tight" style="color: var(--text-heading)">
          Pusat Pemulihan Data (Soft Delete)
        </h1>
        <p class="text-sm font-medium" style="color: var(--text-muted)">
          Kelola dan pulihkan data yang dihapus dari sistem DalwaPress.
        </p>
      </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl p-6 shadow-sm" style="background: var(--bg-card); border: 1px solid var(--border)">
      <!-- Controls -->
      <div class="flex flex-col gap-4 border-b pb-6 md:flex-row md:items-center md:justify-between" style="border-color: var(--border)">
        <!-- Tabs / Pill Selector -->
        <div class="flex flex-wrap gap-1.5 overflow-x-auto pb-2 md:pb-0">
          <button v-for="tab in tabs" :key="tab.value"
                  @click="selectTab(tab.value)"
                  class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-black transition cursor-pointer"
                  :class="activeTab === tab.value
                    ? 'bg-accent text-white shadow-md shadow-accent/20'
                    : 'hover:bg-white/5 font-semibold'"
                  :style="activeTab === tab.value ? '' : 'color: var(--text-body)'">
            <span class="material-symbols-outlined text-[18px]">{{ tab.icon }}</span>
            <span>{{ tab.label }}</span>
          </button>
        </div>

        <!-- Search input -->
        <div class="relative shrink-0 md:w-80">
          <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px]" style="color: var(--text-muted)">search</span>
          <input v-model="search" @input="debouncedFetch"
                 class="input w-full pl-10"
                 placeholder="Cari data terhapus..." />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 gap-3">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
        <span class="text-sm font-bold" style="color: var(--text-muted)">Memuat data sampah...</span>
      </div>

      <!-- Content Table -->
      <div v-else-if="items.length > 0" class="overflow-x-auto mt-6">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="text-xs uppercase font-black" style="color: var(--text-muted); border-bottom: 2px solid var(--border)">
              <th class="pb-3 pl-4">Informasi Data</th>
              <th class="pb-3">Dihapus Pada</th>
              <th class="pb-3">Dihapus Oleh</th>
              <th class="pb-3 text-right pr-4">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y" style="border-color: var(--border)">
            <tr v-for="item in items" :key="item.id" class="hover:bg-white/[0.02] transition-colors">
              <!-- Item Info -->
              <td class="py-4 pl-4">
                <div class="flex flex-col">
                  <span class="font-black text-base" style="color: var(--text-heading)">
                    {{ getDisplayName(item) }}
                  </span>
                  <span class="text-xs font-semibold mt-1" style="color: var(--text-muted)">
                    ID: {{ item.id }} <span v-if="item.slug">· /{{ item.slug }}</span>
                  </span>
                </div>
              </td>
              <!-- Deleted At -->
              <td class="py-4 text-sm font-semibold" style="color: var(--text-body)">
                {{ formatDateTime(item.deleted_at) }}
              </td>
              <!-- Deleted By -->
              <td class="py-4 text-sm font-semibold">
                <div class="flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[18px]" style="color: var(--text-muted)">person_search</span>
                  <span v-if="item.deleter" style="color: var(--text-heading)">{{ item.deleter.name }}</span>
                  <span v-else style="color: var(--text-muted)">Sistem / Tidak Diketahui</span>
                </div>
              </td>
              <!-- Actions -->
              <td class="py-4 text-right pr-4">
                <div class="flex items-center justify-end gap-2">
                  <button @click="restoreItem(item)"
                          class="inline-flex items-center gap-1 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20 rounded-xl px-3 py-1.5 text-xs font-bold transition cursor-pointer active:scale-95"
                          title="Pulihkan Data">
                    <span class="material-symbols-outlined text-[16px]">settings_backup_restore</span>
                    <span>Restore</span>
                  </button>
                  <button @click="forceDelete(item)"
                          class="inline-flex items-center gap-1 bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 rounded-xl px-3 py-1.5 text-xs font-bold transition cursor-pointer active:scale-95"
                          title="Hapus Permanen">
                    <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                    <span>Hapus</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t pt-6 mt-6" style="border-color: var(--border)">
          <span class="text-sm font-bold" style="color: var(--text-muted)">
            Menampilkan {{ items.length }} data terhapus
          </span>
          <div class="flex gap-2">
            <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
                    class="rounded-xl border px-3 py-2 text-sm font-black cursor-pointer transition disabled:opacity-40"
                    style="border-color: var(--border); color: var(--text-body)">
              Sebelumnya
            </button>
            <button @click="changePage(currentPage + 1)" :disabled="currentPage >= lastPage"
                    class="rounded-xl border px-3 py-2 text-sm font-black cursor-pointer transition disabled:opacity-40"
                    style="border-color: var(--border); color: var(--text-body)">
              Berikutnya
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="flex flex-col items-center justify-center py-20 text-center gap-4">
        <div class="w-16 h-16 rounded-full flex items-center justify-center bg-white/5">
          <span class="material-symbols-outlined text-[36px]" style="color: var(--text-muted)">delete_outline</span>
        </div>
        <div>
          <h3 class="text-lg font-black" style="color: var(--text-heading)">Kondisi Bersih</h3>
          <p class="text-sm mt-1 max-w-sm" style="color: var(--text-muted)">
            Tidak ada data {{ getTabLabel(activeTab) }} yang terhapus sementara (soft delete) saat ini.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'
import { useToastStore } from '../../../stores/toast'

const toast = useToastStore()
const activeTab = ref('books')
const search = ref('')
const loading = ref(false)
const items = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
let timer = null

const tabs = [
  { value: 'books', label: 'Buku', icon: 'menu_book' },
  { value: 'submissions', label: 'Submission', icon: 'assignment' },
  { value: 'news', label: 'Berita/News', icon: 'newspaper' },
  { value: 'authors', label: 'Author', icon: 'person' },
  { value: 'book-categories', label: 'Kategori Buku', icon: 'category' },
  { value: 'news-categories', label: 'Kategori News', icon: 'category' },
  { value: 'users', label: 'User', icon: 'group' },
  { value: 'roles', label: 'Role', icon: 'admin_panel_settings' },
  { value: 'royalties', label: 'Royalti', icon: 'payments' },
]

function getTabLabel(val) {
  return tabs.find(t => t.value === val)?.label || ''
}

function selectTab(val) {
  activeTab.value = val
  search.value = ''
  currentPage.value = 1
  fetchData()
}

function getDisplayName(item) {
  return item.title || item.name || item.username || `Record #${item.id}`
}

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(() => {
    currentPage.value = 1
    fetchData()
  }, 400)
}

async function fetchData() {
  loading.value = true
  try {
    const { data } = await api.get('/restore-data', {
      params: {
        type: activeTab.value,
        search: search.value,
        page: currentPage.value,
      }
    })
    items.value = data.data || []
    lastPage.value = data.last_page || 1
  } catch (err) {
    console.error('Gagal mengambil data trash:', err)
  } finally {
    loading.value = false
  }
}

async function restoreItem(item, overwrite = false) {
  if (!overwrite && !confirm(`Pulihkan data "${getDisplayName(item)}"?`)) return
  const toastId = toast.loading('Sedang memulihkan data...')
  try {
    const { data } = await api.post(`/restore-data/${activeTab.value}/${item.id}/restore`, {
      overwrite: overwrite
    })
    toast.dismiss(toastId)
    toast.success(data.message || 'Data berhasil dipulihkan.')
    fetchData()
  } catch (err) {
    toast.dismiss(toastId)
    if (err.response?.status === 409 && err.response?.data?.error === 'duplicate') {
      const details = err.response.data.duplicate_details || ''
      if (confirm(`${err.response.data.message}\n\n${details}\n\nApakah Anda ingin menimpa data tersebut?\n(Catatan: Data aktif saat ini akan dihapus permanen, dan digantikan oleh data yang dipulihkan).`)) {
        restoreItem(item, true)
      }
    } else {
      toast.error(err.response?.data?.message || 'Gagal memulihkan data.')
    }
  }
}

async function forceDelete(item) {
  const display = getDisplayName(item)
  if (!confirm(`PERINGATAN: Hapus permanen "${display}"?\nTindakan ini tidak dapat dibatalkan.`)) return
  if (!confirm(`Konfirmasi kedua: Hapus permanen "${display}" sekarang?`)) return
  const toastId = toast.loading('Sedang menghapus data secara permanen...')
  try {
    const { data } = await api.delete(`/restore-data/${activeTab.value}/${item.id}/force-delete`)
    toast.dismiss(toastId)
    toast.success(data.message || 'Data berhasil dihapus permanen.')
    fetchData()
  } catch (err) {
    toast.dismiss(toastId)
    toast.error(err.response?.data?.message || 'Gagal menghapus permanen.')
  }
}

function changePage(page) {
  if (page < 1 || page > lastPage.value) return
  currentPage.value = page
  fetchData()
}

function formatDateTime(val) {
  if (!val) return '-'
  const d = new Date(val)
  return d.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(fetchData)
</script>

<style scoped>
.input {
  border-radius: 0.85rem;
  background: var(--bg-input);
  border: 1px solid var(--border);
  color: var(--text-heading);
  padding: 0.7rem 0.9rem;
  outline: none;
  font-weight: 600;
  transition: all 0.2s;
}
.input:focus {
  border-color: var(--color-accent);
}
</style>
