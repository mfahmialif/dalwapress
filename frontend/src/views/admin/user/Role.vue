<template>
  <div class="flex flex-col gap-6">

    <!-- ═══ ACTION BAR ═══ -->
    <div class="flex items-center justify-between">
      <button @click="openCreateModal" class="flex items-center gap-2 rounded-lg h-10 px-5 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 shadow-[0_0_15px_rgba(251,191,36,0.3)] shrink-0 cursor-pointer active:scale-95"
              style="color: var(--text-btn)">
        <span class="material-symbols-outlined text-[20px]">add_circle</span>
        <span>Tambah Role</span>
      </button>
    </div>
    <!-- ═══ FILTERS BAR ═══ -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
      <!-- Search -->
      <div class="relative w-full lg:w-[400px]">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-accent text-[20px] z-10">search</span>
        <input v-model="searchQuery"
               class="filter-input w-full rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent"
               placeholder="Search roles..." type="text" />
      </div>
      <!-- Filter Controls -->
      <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 lg:gap-4">
        <!-- Status Filter -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Status:</span>
          <VueMultiselect
            v-model="filterStatus"
            :options="statusOptions"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="All Status"
            class="flex-1 sm:w-[150px] sm:flex-none"
          />
        </div>
        <!-- Entries per page -->
        <div class="flex items-center gap-2">
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">Show:</span>
          <VueMultiselect
            v-model="perPage"
            :options="perPageOptions"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="false"
            :show-labels="false"
            label="name"
            track-by="value"
            placeholder="10"
            class="w-[90px]"
          />
          <span class="text-sm font-medium shrink-0" style="color: var(--text-body)">entries</span>
        </div>
      </div>
    </div>

    <!-- ═══ ROLE TABLE ═══ -->
    <div class="flex flex-col gap-4">
      <div class="table-wrapper rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto p-2">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="table-head">
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Nama Role</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Deskripsi</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Jumlah User</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide" style="color: var(--text-heading)">Status</th>
                <th class="px-6 py-4 text-sm font-semibold tracking-wide text-right" style="color: var(--text-heading)">Actions</th>
              </tr>
            </thead>
            <tbody class="table-body">
              <tr v-for="role in filteredRoles" :key="role.id" class="table-row-hover">
                <td class="px-6 py-5">
                  <span class="text-sm font-bold" style="color: var(--text-heading)">{{ role.name }}</span>
                </td>
                <td class="px-6 py-5 text-sm" style="color: var(--text-muted)">{{ role.description }}</td>
                <td class="px-6 py-5 text-sm" style="color: var(--text-heading)">{{ role.users_count || 0 }} Users</td>
                <td class="px-6 py-5">
                  <span :class="statusBadge(role.status)">{{ role.status }}</span>
                </td>
                <td class="px-6 py-5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button @click="openEditModal(role)" class="action-btn p-2 rounded-lg transition-all duration-200" title="Edit">
                      <span class="material-symbols-outlined text-[20px] text-accent">edit</span>
                    </button>
                    <button @click="confirmDelete(role)" class="action-btn action-btn-delete p-2 rounded-lg transition-all duration-200" title="Delete">
                      <span class="material-symbols-outlined text-[20px] text-accent">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-bar flex items-center justify-between px-6 py-4">
          <span class="text-sm font-medium" style="color: var(--text-muted)">Showing {{ roleStore.pagination.from || 0 }} to {{ roleStore.pagination.to || 0 }} of {{ roleStore.pagination.total || 0 }} roles</span>
          <div class="flex items-center gap-1.5 ml-auto">
            <button @click="goToPage(currentPage - 1)" class="page-btn p-2 rounded-lg flex items-center justify-center disabled:opacity-50 cursor-pointer" :disabled="currentPage <= 1">
              <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </button>
            <template v-for="p in pageNumbers" :key="p">
              <span v-if="p === '...'" class="w-8 h-8 flex items-center justify-center text-sm" style="color: var(--text-muted)">...</span>
              <button v-else @click="goToPage(p)" :class="p === currentPage ? 'page-btn-active w-8 h-8 rounded-full font-bold text-sm flex items-center justify-center' : 'page-btn w-8 h-8 rounded-full text-sm font-medium flex items-center justify-center cursor-pointer'">{{ p }}</button>
            </template>
            <button @click="goToPage(currentPage + 1)" class="page-btn p-2 rounded-lg flex items-center justify-center cursor-pointer" :disabled="currentPage >= roleStore.pagination.lastPage">
              <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══ MODAL ═══ -->
    <Transition name="fade">
      <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showModal = false">
        <div class="modal-card w-full max-w-md rounded-2xl p-6 flex flex-col gap-5">
          <h3 class="text-lg font-bold" style="color: var(--text-heading)">{{ editingRole ? 'Edit Role' : 'Tambah Role' }}</h3>
          <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Nama Role</label>
              <input v-model="form.name" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Nama role" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Deskripsi</label>
              <input v-model="form.description" class="filter-input rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-1 focus:ring-accent" placeholder="Deskripsi role" />
            </div>
            <div class="flex flex-col gap-1.5">
              <label class="text-sm font-medium" style="color: var(--text-body)">Status</label>
              <VueMultiselect
                v-model="formStatusOption"
                :options="modalStatusOptions"
                :close-on-select="true"
                :searchable="false"
                :allow-empty="false"
                :show-labels="false"
                label="name"
                track-by="value"
                placeholder="Pilih Status"
              />
            </div>
          </div>
          <div v-if="formError" class="text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">{{ formError }}</div>
          <div class="flex justify-end gap-3">
            <button @click="showModal = false" class="px-5 py-2 rounded-lg text-sm font-medium cursor-pointer" style="color: var(--text-body); border: 1px solid var(--border)">Batal</button>
            <button @click="saveRole" :disabled="formLoading" class="flex items-center gap-2 px-5 py-2 rounded-lg bg-accent text-sm font-bold cursor-pointer active:scale-95 disabled:opacity-50" style="color: var(--text-btn)">
              <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
              {{ editingRole ? 'Simpan' : 'Tambah' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import { useRoleStore } from '../../../stores/role'

const roleStore = useRoleStore()
const searchQuery = ref('')

const statusOptions = [
  { name: 'All Status', value: 'all' },
  { name: 'Active', value: 'Active' },
  { name: 'Inactive', value: 'Inactive' }
]
const perPageOptions = [
  { name: '10', value: 10 },
  { name: '25', value: 25 },
  { name: '50', value: 50 },
  { name: '100', value: 100 }
]

const filterStatus = ref(statusOptions[0])
const perPage = ref(perPageOptions[0])
const currentPage = ref(1)

// ── Modal state ──
const showModal = ref(false)
const editingRole = ref(null)
const form = ref({ name: '', description: '', status: 'Active' })
const formError = ref('')
const formLoading = ref(false)

// ── Fetch ──
function loadRoles() {
  roleStore.fetchRoles({
    search: searchQuery.value || undefined,
    status: filterStatus.value.value !== 'all' ? filterStatus.value.value : undefined,
    per_page: perPage.value.value,
    page: currentPage.value,
  })
}

onMounted(() => loadRoles())

// Watchers
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => { currentPage.value = 1; loadRoles() }, 400)
})
watch([filterStatus, perPage], () => { currentPage.value = 1; loadRoles() })

const filteredRoles = computed(() => roleStore.roles)

// ── Pagination ──
function goToPage(page) {
  if (page >= 1 && page <= roleStore.pagination.lastPage) {
    currentPage.value = page
    loadRoles()
  }
}

const pageNumbers = computed(() => {
  const last = roleStore.pagination.lastPage || 1
  const curr = currentPage.value
  const pages = []
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= curr - 1 && i <= curr + 1)) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }
  return pages
})

// ── CRUD ──
function openCreateModal() {
  editingRole.value = null
  form.value = { name: '', description: '', status: 'Active' }
  formError.value = ''
  showModal.value = true
}

function openEditModal(role) {
  editingRole.value = role
  form.value = { name: role.name, description: role.description || '', status: role.status }
  formError.value = ''
  showModal.value = true
}

async function saveRole() {
  formError.value = ''
  formLoading.value = true
  try {
    if (editingRole.value) {
      await roleStore.updateRole(editingRole.value.id, form.value)
    } else {
      await roleStore.createRole(form.value)
    }
    showModal.value = false
    loadRoles()
  } catch (e) {
    formError.value = e.response?.data?.message || 'Terjadi kesalahan.'
  } finally {
    formLoading.value = false
  }
}

async function confirmDelete(role) {
  if (!confirm(`Hapus role "${role.name}"?`)) return
  try {
    await roleStore.deleteRole(role.id)
    loadRoles()
  } catch (e) {
    alert(e.response?.data?.message || 'Gagal menghapus role.')
  }
}

// ── Modal status VueMultiselect ──
const modalStatusOptions = [
  { name: 'Active', value: 'Active' },
  { name: 'Inactive', value: 'Inactive' }
]
const formStatusOption = computed({
  get: () => modalStatusOptions.find(o => o.value === form.value.status) || modalStatusOptions[0],
  set: (val) => { form.value.status = val.value }
})

function statusBadge(status) {
  const base = 'inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold'
  if (status === 'Active') return `${base} text-green-400 border border-green-500/30 bg-green-900/20 shadow-[0_0_10px_rgba(74,222,128,0.3)]`
  return `${base} text-slate-400 border border-slate-500/30 bg-slate-800/50 shadow-[0_0_10px_rgba(148,163,184,0.2)]`
}
</script>

<style scoped>
/* ═══ Filter Inputs ═══ */
.filter-input {
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text-heading);
  transition: box-shadow 0.3s ease;
}
.filter-input::placeholder { color: var(--text-muted); }
.filter-input:hover { box-shadow: 0 0 15px rgba(251, 191, 36, 0.15); }
.filter-input:focus { border-color: var(--color-accent); box-shadow: 0 0 12px rgba(251, 191, 36, 0.3); }

/* ═══ Action Buttons ═══ */
.action-btn { color: var(--text-muted); }
.action-btn:hover { color: var(--color-accent); background: var(--bg-input); }
.action-btn-delete:hover { color: #f87171; background: var(--bg-input); }

/* ═══ Pagination ═══ */
.pagination-bar { border-top: 1px solid var(--border); background: var(--bg-card); }
.page-btn { color: var(--text-muted); border: 1px solid transparent; transition: all 0.2s ease; }
.page-btn:hover { background: var(--bg-input); color: var(--text-heading); }
.page-btn-active { background: var(--color-accent); color: var(--text-btn); box-shadow: 0 0 10px rgba(251, 191, 36, 0.4); }

/* ═══ Modal ═══ */
.modal-card { background: var(--bg-card); border: 1px solid var(--border); box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
