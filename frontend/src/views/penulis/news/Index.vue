<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <router-link to="/penulis/news/create" class="inline-flex h-10 items-center gap-2 rounded-lg bg-accent px-5 font-bold" style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        Tambah Artikel
      </router-link>
      <div class="relative w-full sm:w-80">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px]">search</span>
        <input v-model="search" @input="debouncedFetch" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm" placeholder="Cari artikel..." />
      </div>
    </div>
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Judul</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Kategori</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
            <th class="px-4 py-4 text-sm font-semibold text-right" style="color: var(--text-heading)">Actions</th>
          </tr></thead>
          <tbody class="table-body">
            <tr v-if="!items.length"><td colspan="4" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada artikel.</td></tr>
            <tr v-for="item in items" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4"><p class="font-bold line-clamp-1" style="color: var(--text-heading)">{{ item.title }}</p><p class="text-xs" style="color: var(--text-muted)">{{ formatDate(item.created_at) }}</p></td>
              <td class="px-4 py-4">
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="category in categoryList(item)" :key="category.id || category.name" class="pill">{{ category.name }}</span>
                  <span v-if="!categoryList(item).length" class="pill">-</span>
                </div>
              </td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <router-link :to="`/penulis/news/${item.id}/edit`" class="action-btn p-2 rounded-lg"><span class="material-symbols-outlined text-[20px]">edit</span></router-link>
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
const search = ref('')
let timer = null
function debouncedFetch(){ clearTimeout(timer); timer = setTimeout(fetchData, 300) }
async function fetchData(){
  const params = { per_page: 20 }
  if (search.value) params.search = search.value
  const { data } = await api.get('/penulis/news', { params })
  items.value = data.data || []
}
async function deleteItem(item){
  if (!confirm(`Hapus artikel "${item.title}"?`)) return
  await api.delete(`/penulis/news/${item.id}`)
  fetchData()
}
function formatDate(value){ return value ? new Date(value).toLocaleDateString('id-ID') : '-' }
function statusBadge(status){
  const base = 'app-badge'
  return status === 'Published' ? `${base} app-badge--success` : `${base} app-badge--blue`
}
function categoryList(item){
  return item.categories?.length ? item.categories : (item.category ? [item.category] : [])
}
onMounted(fetchData)
</script>

<style scoped>
.filter-input{background:var(--bg-card);border:1px solid var(--border);color:var(--text-heading);outline:none}.filter-input:focus{border-color:var(--color-accent);box-shadow:0 0 12px rgba(37,99,235,.3)}.table-wrapper{background:var(--bg-card);border:1px solid var(--border)}.table-head{background:var(--bg-input)}.table-row-hover{border-top:1px solid var(--border)}.table-row-hover:hover{background:var(--hover-nav-bg)}.pill{display:inline-flex;border:1px solid var(--border);border-radius:999px;padding:.25rem .7rem;font-size:.75rem;font-weight:800;color:var(--text-body)}.action-btn{color:var(--text-muted)}.action-btn:hover{background:var(--bg-input);color:var(--color-accent)}.action-btn-delete:hover{color:#f87171}
</style>
