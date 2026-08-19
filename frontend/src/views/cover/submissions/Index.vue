<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-xl font-black" style="color: var(--text-heading)">Assigned Cover Submissions</h2>
        <p class="text-sm" style="color: var(--text-muted)">Naskah yang ditugaskan kepada Anda sebagai desainer sampul.</p>
      </div>
      <div class="flex flex-col gap-3 sm:flex-row">
        <input v-model="search" @input="debouncedFetch" class="input sm:w-80" placeholder="Cari judul, author, email..." />
        <select v-model="status" @change="fetchData" class="input sm:w-52">
          <option value="">Semua Status</option>
          <option v-for="item in statuses" :key="item" :value="item">{{ statusLabel(item) }}</option>
        </select>
      </div>
    </div>

    <div v-if="errorMessage" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-400">
      {{ errorMessage }}
    </div>

    <div class="rounded-2xl p-2" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div v-if="loading" class="flex justify-center py-16">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr style="background: var(--bg-input)">
              <th class="th">Naskah</th>
              <th class="th">Author</th>
              <th class="th">Kategori</th>
              <th class="th">Status</th>
              <th class="th">Assignment</th>
              <th class="th">Hasil Cover</th>
              <th class="th text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada assignment cover</td>
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
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">Desainer Cover</td>
              <td class="px-4 py-4"><span :class="fileStatusBadge(hasCoverFile(item))">{{ hasCoverFile(item) ? 'Sudah diunggah' : 'Belum ada' }}</span></td>
              <td class="px-4 py-4 text-right">
                <router-link :to="{ name: 'CoverSubmissionDetail', params: { id: item.id } }" class="action-link">Detail</router-link>
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
const errorMessage = ref('')
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
  errorMessage.value = ''
  const params = { per_page: 100 }
  if (search.value) params.search = search.value
  if (status.value) params.status = status.value

  try {
    const { data } = await api.get('/cover/submissions', { params })
    items.value = data.data || []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat daftar submission.'
  } finally {
    loading.value = false
  }
}

function hasCoverFile(item) {
  return (item.revisions || []).some((revision) => {
    const role = String(revision.user?.role?.name || '').toLowerCase()
    const user = String(revision.user?.name || '').toLowerCase()
    const note = String(revision.note || '').toLowerCase()
    return role.includes('cover') || role.includes('sampul') || user.includes('cover') || user.includes('sampul') || note.includes('cover') || note.includes('sampul')
  })
}

function statusLabel(value) {
  return { submitted: 'Submitted', under_review: 'Under Review', revision: 'Revision', accepted: 'Accepted', rejected: 'Rejected', published: 'Published' }[value] || value
}

function statusBadge(value) {
  const base = 'app-badge'
  if (value === 'accepted' || value === 'published') return `${base} app-badge--success`
  if (value === 'rejected') return `${base} app-badge--red`
  if (value === 'revision') return `${base} app-badge--blue`
  return `${base} app-badge--sky`
}

function fileStatusBadge(value) {
  return value ? 'app-badge app-badge--success' : 'app-badge app-badge--muted'
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(fetchData)
</script>

<style scoped>
.input { border-radius: .85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: .7rem .9rem; outline: none; }
.th { padding: 1rem; font-size: .875rem; font-weight: 700; color: var(--text-heading); }
.table-row { border-top: 1px solid var(--border); transition: background .2s; }
.table-row:hover { background: var(--bg-input); }
.action-link { display: inline-flex; border-radius: .65rem; padding: .5rem .75rem; color: var(--color-accent); font-size: .875rem; font-weight: 800; }
.action-link:hover { background: var(--hover-nav-bg); }
</style>
