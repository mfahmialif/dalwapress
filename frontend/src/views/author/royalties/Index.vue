<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">Royalti Saya</h2>
      <p class="text-sm" style="color: var(--text-muted)">Rekap royalti dari buku published Anda.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <article v-for="item in summaryCards" :key="item.label" class="rounded-2xl border p-5" style="background: var(--bg-card); border-color: var(--border)">
        <span class="material-symbols-outlined text-accent">{{ item.icon }}</span>
        <p class="mt-2 text-sm font-bold" style="color: var(--text-muted)">{{ item.label }}</p>
        <strong class="mt-1 block text-xl font-black" style="color: var(--text-heading)">{{ item.value }}</strong>
      </article>
    </div>

    <div class="flex flex-col gap-3 rounded-2xl border p-4 lg:flex-row" style="background: var(--bg-card); border-color: var(--border)">
      <input v-model="search" @input="debouncedFetch" class="filter-input h-10 rounded-xl px-4 text-sm outline-none" placeholder="Cari buku atau ISBN..." />
      <select v-model="status" @change="fetchAll" class="filter-input h-10 rounded-xl px-4 text-sm outline-none">
        <option value="">Semua Status</option>
        <option value="draft">Draft</option>
        <option value="pending">Pending</option>
        <option value="paid">Paid</option>
      </select>
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
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Periode</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Penjualan</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Royalti</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="royalties.length === 0">
              <td colspan="5" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada data royalti.</td>
            </tr>
            <tr v-for="item in royalties" :key="item.id" class="table-row">
              <td class="px-4 py-4">
                <p class="line-clamp-1 text-sm font-black" style="color: var(--text-heading)">{{ item.book?.title || '-' }}</p>
                <p class="text-xs" style="color: var(--text-muted)">ISBN: {{ item.book?.isbn || '-' }}</p>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ monthLabel(item.period_month) }} {{ item.period_year }}</td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">
                {{ item.sold_qty }} pcs
                <p class="text-xs" style="color: var(--text-muted)">{{ formatCurrency(item.gross_amount) }}</p>
              </td>
              <td class="px-4 py-4 text-sm font-black text-accent">
                {{ formatCurrency(item.royalty_amount) }}
                <p class="text-xs font-bold" style="color: var(--text-muted)">{{ formatCurrency(item.royalty_per_unit) }} / pcs</p>
              </td>
              <td class="px-4 py-4">
                <span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span>
                <p v-if="item.paid_at" class="mt-1 text-xs" style="color: var(--text-muted)">Dibayar {{ formatDate(item.paid_at) }}</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t px-5 py-4" style="border-color: var(--border)">
        <p class="text-sm" style="color: var(--text-muted)">Total {{ total }} entry</p>
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
import { computed, onMounted, ref } from 'vue'
import api from '../../../axios'

const royalties = ref([])
const summary = ref({})
const loading = ref(false)
const search = ref('')
const status = ref('')
const page = ref(1)
const lastPage = ref(1)
const total = ref(0)
const perPage = 10
let timer = null

const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
].map((label, index) => ({ label, value: index + 1 }))

const summaryCards = computed(() => [
  { icon: 'menu_book', label: 'Buku', value: summary.value.total_books || 0 },
  { icon: 'inventory_2', label: 'Terjual', value: `${summary.value.total_sold_qty || 0} pcs` },
  { icon: 'payments', label: 'Total Royalti', value: formatCurrency(summary.value.royalty_amount) },
  { icon: 'schedule', label: 'Pending', value: formatCurrency(summary.value.pending_amount) },
  { icon: 'paid', label: 'Paid', value: formatCurrency(summary.value.paid_amount) },
])

function params() {
  const value = { page: page.value, per_page: perPage }
  if (search.value) value.search = search.value
  if (status.value) value.status = status.value
  return value
}

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(() => {
    page.value = 1
    fetchAll()
  }, 300)
}

async function fetchAll() {
  await Promise.all([fetchRoyalties(), fetchSummary()])
}

async function fetchRoyalties() {
  loading.value = true
  try {
    const { data } = await api.get('/author/royalties', { params: params() })
    royalties.value = data.data || []
    page.value = data.current_page || 1
    lastPage.value = data.last_page || 1
    total.value = data.total || 0
  } finally {
    loading.value = false
  }
}

async function fetchSummary() {
  const { data } = await api.get('/author/royalties/summary', { params: params() })
  summary.value = data
}

function goPage(next) {
  if (next < 1 || next > lastPage.value) return
  page.value = next
  fetchRoyalties()
}

function monthLabel(value) {
  return months.find((item) => Number(item.value) === Number(value))?.label || '-'
}

function statusLabel(value) {
  return { draft: 'Draft', pending: 'Pending', paid: 'Paid' }[value] || value
}

function statusBadge(value) {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-black'
  if (value === 'paid') return `${base} bg-green-500/10 text-green-400 border border-green-500/30`
  if (value === 'pending') return `${base} bg-amber-500/10 text-amber-400 border border-amber-500/30`
  return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0))
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString('id-ID') : '-'
}

onMounted(fetchAll)
</script>

<style scoped>
.filter-input { background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); }
.table-row { border-top: 1px solid var(--border); transition: background 0.2s ease; }
.table-row:hover { background: var(--bg-input); }
.page-btn { width: 34px; height: 34px; border-radius: 10px; color: var(--text-heading); background: var(--bg-input); border: 1px solid var(--border); }
.page-btn:disabled { opacity: 0.4; }
</style>
