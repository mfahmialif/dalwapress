<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <router-link to="/kepala-penulis/news/create" class="inline-flex h-10 items-center gap-2 rounded-lg bg-accent px-5 font-bold" style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        Tambah Artikel
      </router-link>
      <div class="flex flex-col gap-3 sm:flex-row">
        <select v-model="authorId" @change="fetchData" class="filter-input rounded-xl px-3 py-2.5 text-sm">
          <option value="">Semua Penulis</option>
          <option v-for="writer in writers" :key="writer.id" :value="writer.id">{{ writer.name }}</option>
        </select>
        <div class="relative w-full sm:w-80">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px]">search</span>
          <input v-model="search" @input="debouncedFetch" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm" placeholder="Cari artikel..." />
        </div>
      </div>
    </div>
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Judul</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Penulis</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
            <th class="px-4 py-4 text-sm font-semibold text-right" style="color: var(--text-heading)">Actions</th>
          </tr></thead>
          <tbody class="table-body">
            <tr v-if="!items.length"><td colspan="4" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada artikel.</td></tr>
            <tr v-for="item in items" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4"><p class="font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</p><p class="text-xs" style="color: var(--text-muted)">{{ categoryNames(item) || '-' }}</p></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.author?.name || '-' }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <router-link :to="`/kepala-penulis/news/${item.id}/edit`" class="action-btn p-2 rounded-lg"><span class="material-symbols-outlined text-[20px]">edit</span></router-link>
                <button @click="deleteItem(item)" class="action-btn action-btn-delete p-2 rounded-lg"><span class="material-symbols-outlined text-[20px]">delete</span></button>
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
const writers = ref([])
const search = ref('')
const authorId = ref('')
let timer = null
function debouncedFetch(){ clearTimeout(timer); timer = setTimeout(fetchData, 300) }
async function fetchData(){
  const params = { per_page: 20 }
  if (search.value) params.search = search.value
  if (authorId.value) params.author_id = authorId.value
  const { data } = await api.get('/kepala-penulis/news', { params })
  items.value = data.data || []
}
async function loadWriters(){ writers.value = (await api.get('/kepala-penulis/writers/options')).data || [] }
async function deleteItem(item){
  if (!confirm(`Hapus artikel "${item.title}"?`)) return
  await api.delete(`/kepala-penulis/news/${item.id}`)
  fetchData()
}
function statusBadge(status){
  const base = 'app-badge'
  return status === 'Published' ? `${base} app-badge--success` : `${base} app-badge--blue`
}
function categoryNames(item){
  const list = item.categories?.length ? item.categories : (item.category ? [item.category] : [])
  return list.map(category => category.name).join(', ')
}
onMounted(async () => { await loadWriters(); fetchData() })
</script>

<style scoped>
.filter-input{background:var(--bg-card);border:1px solid var(--border);color:var(--text-heading);outline:none}.filter-input:focus{border-color:var(--color-accent);box-shadow:0 0 12px rgba(37,99,235,.3)}.table-wrapper{background:var(--bg-card);border:1px solid var(--border)}.table-head{background:var(--bg-input)}.table-row-hover{border-top:1px solid var(--border)}.table-row-hover:hover{background:var(--hover-nav-bg)}.action-btn{color:var(--text-muted)}.action-btn:hover{background:var(--bg-input);color:var(--color-accent)}.action-btn-delete:hover{color:#f87171}
</style>
