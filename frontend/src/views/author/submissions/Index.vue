<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-xl font-black" style="color: var(--text-heading)">My Submissions</h2>
        <p class="text-sm" style="color: var(--text-muted)">Kelola naskah milik Anda, revisi, dan status review.</p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row">
        <input v-model="filters.search" class="input sm:w-72" placeholder="Cari judul..." @input="debouncedFetch" />
        <select v-model="filters.status" class="input sm:w-52" @change="fetchItems">
          <option value="">Semua Status</option>
          <option v-for="status in statuses" :key="status" :value="status">{{ statusLabel(status) }}</option>
        </select>
        <router-link to="/author/submissions/create" class="btn-primary">
          <span class="material-symbols-outlined text-[18px]">add</span>
          Create
        </router-link>
      </div>
    </div>

    <div class="rounded-2xl p-2" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div v-if="loading" class="flex justify-center py-16">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr style="background: var(--bg-input)">
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Judul</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Kategori</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Status</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Tanggal Submit</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Reviewer Terakhir</th>
              <th class="px-4 py-4 text-right text-sm font-bold" style="color: var(--text-heading)">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada submission</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="table-row">
              <td class="px-4 py-4">
                <p class="font-black" style="color: var(--text-heading)">{{ item.title }}</p>
                <p class="text-xs" style="color: var(--text-muted)">{{ item.note || '-' }}</p>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.category?.name || '-' }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ formatDate(item.submitted_at) }}</td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.reviews?.[0]?.reviewer_name || '-' }}</td>
              <td class="px-4 py-4">
                <div class="flex flex-wrap justify-end gap-2">
                  <router-link class="action-link" :to="`/author/submissions/${item.id}`">Detail</router-link>
                  <router-link v-if="!['accepted', 'published'].includes(item.status)" class="action-link" :to="`/author/submissions/${item.id}/edit`">Edit</router-link>
                  <router-link v-if="item.status === 'revision'" class="action-link accent" :to="`/author/submissions/${item.id}/revision`">Revisi</router-link>
                  <button v-if="item.status === 'submitted'" class="action-link danger" @click="remove(item.id)">Hapus</button>
                </div>
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

const statuses = ['submitted', 'under_review', 'revision', 'accepted', 'rejected', 'published']
const filters = ref({ search: '', status: '' })
const items = ref([])
const loading = ref(false)
let timer = null

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchItems, 300)
}

async function fetchItems() {
  loading.value = true
  const { data } = await api.get('/author/submissions', { params: filters.value })
  items.value = data.data || []
  loading.value = false
}

async function remove(id) {
  if (!confirm('Hapus draft submission ini?')) return
  await api.delete(`/author/submissions/${id}`)
  await fetchItems()
}

function statusLabel(value) {
  return {
    submitted: 'Submitted',
    under_review: 'Under Review',
    revision: 'Revision',
    accepted: 'Accepted',
    rejected: 'Rejected',
    published: 'Published',
  }[value] || value
}

function statusBadge(value) {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-black'
  if (value === 'accepted' || value === 'published') return `${base} bg-green-500/10 text-green-400 border border-green-500/30`
  if (value === 'rejected') return `${base} bg-red-500/10 text-red-400 border border-red-500/30`
  if (value === 'revision') return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`
  return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
}

onMounted(fetchItems)
</script>

<style scoped>
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; border-radius: 0.85rem; background: var(--color-accent); color: var(--text-btn); padding: 0.7rem 0.9rem; font-weight: 900; }
.table-row { border-top: 1px solid var(--border); transition: background 0.2s ease; }
.table-row:hover { background: var(--bg-input); }
.action-link { border-radius: 0.65rem; padding: 0.45rem 0.65rem; color: var(--text-muted); font-size: 0.82rem; font-weight: 900; transition: background 0.2s ease, color 0.2s ease; }
.action-link:hover, .action-link.accent { background: var(--hover-nav-bg); color: var(--color-accent); }
.action-link.danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>
