<template>
  <div class="flex flex-col gap-6">
    <div class="flex items-center gap-4">
      <router-link :to="{ name: 'AdminRoyalties' }" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        Kembali
      </router-link>
      <h2 class="text-lg font-black" style="color: var(--text-heading)">{{ isEdit ? 'Edit Royalti' : 'Tambah Royalti' }}</h2>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <form v-else class="form-card grid gap-5 rounded-2xl p-6" @submit.prevent="submit">
      <div>
        <h3 class="text-lg font-black" style="color: var(--text-heading)">Data Royalti</h3>
        <p class="text-sm" style="color: var(--text-muted)">Pilih buku published, lalu isi jumlah terjual dan nominal per pcs.</p>
      </div>

      <p v-if="error" class="rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm font-bold text-red-400">{{ error }}</p>

      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field lg:col-span-2">
          <span>Buku Published</span>
          <select v-model="form.book_id" required class="field-input">
            <option value="">Pilih buku</option>
            <option v-for="book in publishedBooks" :key="book.id" :value="book.id">
              {{ book.title }} - {{ book.author?.name || '-' }}
            </option>
          </select>
        </label>

        <label class="field">
          <span>Bulan</span>
          <select v-model.number="form.period_month" required class="field-input">
            <option v-for="month in months" :key="month.value" :value="month.value">{{ month.label }}</option>
          </select>
        </label>

        <label class="field">
          <span>Tahun</span>
          <input v-model.number="form.period_year" required min="2000" max="2100" type="number" class="field-input" />
        </label>

        <label class="field">
          <span>Jumlah Terjual</span>
          <input v-model.number="form.sold_qty" required min="0" type="number" class="field-input" />
        </label>

        <label class="field">
          <span>Harga Jual per Pcs</span>
          <input v-model.number="form.sale_price_per_unit" required min="0" step="100" type="number" class="field-input" />
        </label>

        <label class="field">
          <span>Royalti per Pcs</span>
          <input v-model.number="form.royalty_per_unit" required min="0" step="100" type="number" class="field-input" />
        </label>

        <label class="field">
          <span>Status</span>
          <select v-model="form.status" required class="field-input">
            <option value="draft">Draft</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
          </select>
        </label>

        <label class="field">
          <span>Tanggal Bayar</span>
          <input v-model="form.paid_at" :disabled="form.status !== 'paid'" type="date" class="field-input disabled:opacity-50" />
        </label>

        <label class="field lg:col-span-2">
          <span>Catatan</span>
          <textarea v-model="form.notes" rows="3" class="field-input resize-none"></textarea>
        </label>
      </div>

      <div class="grid gap-3 rounded-xl border p-4 sm:grid-cols-2" style="border-color: var(--border); background: var(--bg-input)">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Total Penjualan</p>
          <p class="text-xl font-black" style="color: var(--text-heading)">{{ formatCurrency(calculatedGross) }}</p>
        </div>
        <div>
          <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Total Royalti</p>
          <p class="text-xl font-black text-accent">{{ formatCurrency(calculatedRoyalty) }}</p>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <router-link :to="{ name: 'AdminRoyalties' }" class="btn-muted">Batal</router-link>
        <button :disabled="saving" class="btn-primary h-10 px-5">
          <span v-if="saving" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
          {{ saving ? 'Menyimpan...' : 'Simpan Royalti' }}
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
const publishedBooks = ref([])
const loading = ref(false)
const saving = ref(false)
const error = ref('')

const months = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
].map((label, index) => ({ label, value: index + 1 }))

const form = ref({
  book_id: '',
  period_month: new Date().getMonth() + 1,
  period_year: new Date().getFullYear(),
  sold_qty: 0,
  sale_price_per_unit: 0,
  royalty_per_unit: 0,
  status: 'draft',
  paid_at: '',
  notes: '',
})

const calculatedGross = computed(() => Number(form.value.sold_qty || 0) * Number(form.value.sale_price_per_unit || 0))
const calculatedRoyalty = computed(() => Number(form.value.sold_qty || 0) * Number(form.value.royalty_per_unit || 0))

async function fetchPublishedBooks() {
  const { data } = await api.get('/books', { params: { status: 'published', per_page: 100, sort_by: 'title', sort_dir: 'asc' } })
  publishedBooks.value = data.data || []
}

async function loadRoyalty() {
  const { data } = await api.get(`/royalties/${route.params.id}`)
  form.value = {
    book_id: data.book_id || '',
    period_month: data.period_month || new Date().getMonth() + 1,
    period_year: data.period_year || new Date().getFullYear(),
    sold_qty: data.sold_qty || 0,
    sale_price_per_unit: Number(data.sale_price_per_unit || 0),
    royalty_per_unit: Number(data.royalty_per_unit || 0),
    status: data.status || 'draft',
    paid_at: data.paid_at ? data.paid_at.slice(0, 10) : '',
    notes: data.notes || '',
  }
}

async function submit() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    if (payload.status !== 'paid') payload.paid_at = null
    if (isEdit.value) await api.put(`/royalties/${route.params.id}`, payload)
    else await api.post('/royalties', payload)
    router.push({ name: 'AdminRoyalties' })
  } catch (err) {
    const errors = err.response?.data?.errors
    error.value = errors ? Object.values(errors).flat().join(' ') : (err.response?.data?.message || 'Gagal menyimpan royalti.')
  } finally {
    saving.value = false
  }
}

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0))
}

onMounted(async () => {
  loading.value = true
  try {
    await fetchPublishedBooks()
    if (isEdit.value) await loadRoyalty()
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.form-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: var(--shadow-card); }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.75rem; font-weight: 900; background: var(--color-accent); color: var(--text-btn); transition: opacity 0.2s ease; }
.btn-primary:disabled { opacity: 0.6; }
.btn-muted { display: inline-flex; align-items: center; justify-content: center; border-radius: 0.75rem; padding: 0.65rem 1rem; font-weight: 900; color: var(--text-heading); background: var(--bg-input); border: 1px solid var(--border); }
.field { display: flex; flex-direction: column; gap: 0.45rem; font-size: 0.85rem; font-weight: 800; color: var(--text-heading); }
.field-input { min-height: 2.6rem; border-radius: 0.8rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.65rem 0.85rem; outline: none; font-weight: 700; }
.field-input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 1px var(--color-accent); }
</style>
