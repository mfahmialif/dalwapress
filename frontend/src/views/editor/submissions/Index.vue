<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h2 class="text-xl font-black" style="color: var(--text-heading)">Assigned Submissions</h2>
        <p class="text-sm" style="color: var(--text-muted)">Naskah yang ditugaskan kepada Anda sebagai primary atau co-editor.</p>
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
      <div v-if="loading" class="flex justify-center py-16"><span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span></div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead><tr style="background: var(--bg-input)"><th class="th">Naskah</th><th class="th">Author</th><th class="th">Status</th><th class="th">Assignment</th><th class="th text-right">Action</th></tr></thead>
          <tbody>
            <tr v-if="items.length === 0"><td colspan="5" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada assignment</td></tr>
            <tr v-for="item in items" :key="item.id" class="table-row">
              <td class="px-4 py-4"><p class="font-black" style="color: var(--text-heading)">{{ item.title }}</p><p class="text-xs" style="color: var(--text-muted)">{{ item.category?.name || '-' }}</p></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.author_name }}<p class="text-xs" style="color: var(--text-muted)">{{ item.email || '-' }}</p></td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-muted)">{{ myAssignment(item)?.role === 'primary' ? 'Primary Editor' : 'Co-editor' }}</td>
              <td class="px-4 py-4 text-right"><router-link :to="`/editor/submissions/${item.id}`" class="rounded-lg px-3 py-2 text-sm font-bold text-accent hover:bg-white/5">Review</router-link></td>
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
const items = ref([]), loading = ref(false), search = ref(''), status = ref('')
const currentUser = ref(JSON.parse(localStorage.getItem('auth_user') || '{}'))
const statuses = ['submitted','under_review','revision','accepted','rejected','published']
let timer = null
function debouncedFetch(){ clearTimeout(timer); timer = setTimeout(fetchData, 300) }
async function fetchData(){ loading.value = true; const params = { per_page: 100 }; if(search.value) params.search = search.value; if(status.value) params.status = status.value; items.value = ((await api.get('/editor/submissions',{params})).data.data || []); loading.value = false }
function myAssignment(item){ return item.editor_assignments?.find((a) => a.editor_id === currentUser.value.id) }
function statusLabel(value){ return { submitted:'Submitted', under_review:'Under Review', revision:'Revision', accepted:'Accepted', rejected:'Rejected', published:'Published' }[value] || value }
function statusBadge(value){ const base='inline-flex rounded-full px-3 py-1 text-xs font-black'; if(value==='accepted'||value==='published')return `${base} bg-green-500/10 text-green-400 border border-green-500/30`; if(value==='rejected')return `${base} bg-red-500/10 text-red-400 border border-red-500/30`; if(value==='revision')return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`; return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30` }
onMounted(fetchData)
</script>
<style scoped>
.input{border-radius:.85rem;background:var(--bg-input);border:1px solid var(--border);color:var(--text-heading);padding:.7rem .9rem;outline:none}.th{padding:1rem;font-size:.875rem;font-weight:700;color:var(--text-heading)}.table-row{border-top:1px solid var(--border);transition:background .2s}.table-row:hover{background:var(--bg-input)}
</style>
