<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-xl font-black" style="color: var(--text-heading)">Submission Naskah</h2>
        <p class="text-sm" style="color: var(--text-muted)">Kelola naskah masuk, review, dan keputusan editorial.</p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row">
        <input v-model="search" @input="debouncedFetch" class="input sm:w-80" placeholder="Cari judul, author, email..." />
        <select v-model="status" @change="fetchData" class="input sm:w-52">
          <option value="">Semua Status</option>
          <option v-for="item in statuses" :key="item" :value="item">{{ statusLabel(item) }}</option>
        </select>
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
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Naskah</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Author</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Kategori</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Status</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Editor</th>
              <th class="px-4 py-4 text-sm font-bold" style="color: var(--text-heading)">Review</th>
              <th class="px-4 py-4 text-right text-sm font-bold" style="color: var(--text-heading)">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada submission</td>
            </tr>
            <tr v-for="item in items" :key="item.id" class="table-row">
              <td class="px-4 py-4">
                <p class="font-black" style="color: var(--text-heading)">{{ item.title }}</p>
                <p class="text-xs" style="color: var(--text-muted)">Masuk: {{ formatDate(item.submitted_at || item.created_at) }}</p>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">
                {{ item.author_name }}
                <p class="text-xs" style="color: var(--text-muted)">{{ item.email || '-' }}</p>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.category?.name || '-' }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">
                {{ primaryEditor(item)?.editor?.name || '-' }}
                <p v-if="coEditors(item).length" class="text-xs" style="color: var(--text-muted)">+{{ coEditors(item).length }} co-editor</p>
              </td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-muted)">{{ item.reviews?.length || 0 }} catatan</td>
              <td class="px-4 py-4 text-right">
                <router-link :to="{ name: 'AdminSubmissionDetail', params: { id: item.id } }" class="rounded-lg px-3 py-2 text-sm font-bold text-accent hover:bg-white/5">Detail</router-link>
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

const items = ref([])
const loading = ref(false)
const search = ref('')
const status = ref('')
const statuses = ['submitted', 'under_review', 'revision', 'accepted', 'rejected', 'published']
let timer = null

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchData, 300)
}

async function fetchData() {
  loading.value = true
  const params = { per_page: 100 }
  if (search.value) params.search = search.value
  if (status.value) params.status = status.value
  const { data } = await api.get('/submissions', { params })
  items.value = data.data || []
  loading.value = false
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
  if (value === 'revision') return `${base} bg-yellow-500/10 text-yellow-400 border border-yellow-500/30`
  return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
}

function primaryEditor(item) {
  return item.editor_assignments?.find((assignment) => assignment.role === 'primary')
}

function coEditors(item) {
  return item.editor_assignments?.filter((assignment) => assignment.role === 'co_editor') || []
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(fetchData)
</script>

<style scoped>
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.table-row { border-top: 1px solid var(--border); transition: background 0.2s ease; }
.table-row:hover { background: var(--bg-input); }
</style>
