<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <button @click="openCreate" class="inline-flex h-10 items-center gap-2 rounded-lg bg-accent px-5 font-bold" style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">person_add</span>
        Tambah Penulis
      </button>
      <div class="relative w-full sm:w-80">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px]">search</span>
        <input v-model="search" @input="debouncedFetch" class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm" placeholder="Cari penulis..." />
      </div>
    </div>
    <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
      <div class="overflow-x-auto p-2">
        <table class="w-full text-left border-collapse">
          <thead><tr class="table-head">
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Nama</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Email</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Artikel</th>
            <th class="px-4 py-4 text-sm font-semibold" style="color: var(--text-heading)">Status</th>
            <th class="px-4 py-4 text-sm font-semibold text-right" style="color: var(--text-heading)">Actions</th>
          </tr></thead>
          <tbody>
            <tr v-if="!items.length"><td colspan="5" class="px-4 py-12 text-center text-sm" style="color: var(--text-muted)">Belum ada akun penulis.</td></tr>
            <tr v-for="item in items" :key="item.id" class="table-row-hover">
              <td class="px-4 py-4"><p class="font-bold" style="color: var(--text-heading)">{{ item.name }}</p><p class="text-xs" style="color: var(--text-muted)">@{{ item.username }}</p></td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.email }}</td>
              <td class="px-4 py-4 text-sm" style="color: var(--text-body)">{{ item.authored_news_count || 0 }}</td>
              <td class="px-4 py-4"><span :class="statusBadge(item.status)">{{ item.status }}</span></td>
              <td class="px-4 py-4 text-right">
                <button @click="openEdit(item)" class="action-btn p-2 rounded-lg"><span class="material-symbols-outlined text-[20px]">edit</span></button>
                <button @click="disableWriter(item)" class="action-btn action-btn-delete p-2 rounded-lg"><span class="material-symbols-outlined text-[20px]">block</span></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" @click.self="showModal = false">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
          <h3 class="text-lg font-black" style="color: var(--text-heading)">{{ editing ? 'Edit Penulis' : 'Tambah Penulis' }}</h3>
          <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div class="field"><label>Username</label><input v-model="form.username" class="filter-input" /></div>
            <div class="field"><label>Nama</label><input v-model="form.name" class="filter-input" /></div>
            <div class="field"><label>Email</label><input v-model="form.email" type="email" class="filter-input" /></div>
            <div class="field"><label>Password {{ editing ? '(opsional)' : '' }}</label><input v-model="form.password" type="password" class="filter-input" /></div>
            <div class="field"><label>Status</label><select v-model="form.status" class="filter-input"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div>
          </div>
          <div v-if="error" class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">{{ error }}</div>
          <div class="mt-6 flex justify-end gap-3">
            <button @click="showModal = false" class="rounded-lg px-5 py-2.5 text-sm font-bold" style="border:1px solid var(--border); color: var(--text-body)">Batal</button>
            <button @click="save" :disabled="saving" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-bold disabled:opacity-60" style="color: var(--text-btn)">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'

const items = ref([])
const search = ref('')
const showModal = ref(false)
const editing = ref(null)
const saving = ref(false)
const error = ref('')
const form = ref({ username: '', name: '', email: '', password: '', status: 'Active' })
let timer = null
function debouncedFetch(){ clearTimeout(timer); timer = setTimeout(fetchData, 300) }
async function fetchData(){
  const params = { per_page: 50 }
  if (search.value) params.search = search.value
  const { data } = await api.get('/kepala-penulis/writers', { params })
  items.value = data.data || []
}
function openCreate(){
  editing.value = null
  form.value = { username: '', name: '', email: '', password: '', status: 'Active' }
  error.value = ''
  showModal.value = true
}
function openEdit(item){
  editing.value = item
  form.value = { username: item.username, name: item.name, email: item.email, password: '', status: item.status || 'Active' }
  error.value = ''
  showModal.value = true
}
async function save(){
  saving.value = true; error.value = ''
  try {
    const payload = { ...form.value }
    if (editing.value && !payload.password) delete payload.password
    if (editing.value) await api.put(`/kepala-penulis/writers/${editing.value.id}`, payload)
    else await api.post('/kepala-penulis/writers', payload)
    showModal.value = false
    fetchData()
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Gagal menyimpan penulis.')
  } finally { saving.value = false }
}
async function disableWriter(item){
  if (!confirm(`Nonaktifkan akun "${item.name}"?`)) return
  await api.delete(`/kepala-penulis/writers/${item.id}`)
  fetchData()
}
function statusBadge(status){
  const base = 'app-badge'
  return status === 'Active' ? `${base} app-badge--success` : `${base} app-badge--muted`
}
onMounted(fetchData)
</script>

<style scoped>
.filter-input{background:var(--bg-card);border:1px solid var(--border);color:var(--text-heading);outline:none}.filter-input:focus{border-color:var(--color-accent);box-shadow:0 0 12px rgba(37,99,235,.3)}.table-wrapper{background:var(--bg-card);border:1px solid var(--border)}.table-head{background:var(--bg-input)}.table-row-hover{border-top:1px solid var(--border)}.table-row-hover:hover{background:var(--hover-nav-bg)}.action-btn{color:var(--text-muted)}.action-btn:hover{background:var(--bg-input);color:var(--color-accent)}.action-btn-delete:hover{color:#f87171}.field{display:flex;flex-direction:column;gap:.4rem}.field label{font-size:.88rem;font-weight:800;color:var(--text-body)}.field .filter-input{width:100%;border-radius:.75rem;background:var(--bg-input);padding:.7rem .9rem}.fade-enter-active,.fade-leave-active{transition:opacity .2s}.fade-enter-from,.fade-leave-to{opacity:0}
</style>
