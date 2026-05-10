<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">{{ isEdit ? 'Edit Submission' : 'Create Submission' }}</h2>
      <p class="text-sm" style="color: var(--text-muted)">Upload naskah hanya tersedia untuk author yang sudah login.</p>
    </div>

    <form class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)" @submit.prevent="submit">
      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field"><span>Judul buku *</span><input v-model="form.title" class="input" required /></label>
        <label class="field">
          <span>Kategori *</span>
          <select v-model="form.category_id" class="input" required>
            <option value="">Pilih kategori</option>
            <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </label>
      </div>

      <label class="field mt-4"><span>Deskripsi</span><textarea v-model="form.description" class="input min-h-32"></textarea></label>

      <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <label class="field">
          <span>Upload naskah {{ isEdit ? '' : '*' }}</span>
          <input class="input" type="file" accept=".pdf,.doc,.docx" :required="!isEdit" @change="manuscriptFile = $event.target.files[0]" />
          <small>PDF/DOCX/DOC, maksimal 50MB.</small>
        </label>
        <label class="field">
          <span>Upload cover</span>
          <input class="input" type="file" accept="image/*" @change="coverFile = $event.target.files[0]" />
        </label>
      </div>

      <label class="field mt-4"><span>Catatan tambahan</span><textarea v-model="form.note" class="input min-h-28"></textarea></label>

      <div v-if="message" class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm font-bold text-red-400">{{ message }}</div>

      <div class="mt-5 flex flex-wrap gap-3">
        <button class="btn-primary" :disabled="saving">
          <span class="material-symbols-outlined text-[18px]">{{ saving ? 'progress_activity' : 'send' }}</span>
          {{ saving ? 'Menyimpan...' : 'Simpan Submission' }}
        </button>
        <router-link to="/author/submissions" class="btn-soft">Batal</router-link>
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
const categories = ref([])
const manuscriptFile = ref(null)
const coverFile = ref(null)
const saving = ref(false)
const message = ref('')
const form = ref({ category_id: '', title: '', description: '', note: '' })

async function loadData() {
  const { data } = await api.get('/book-categories', { params: { all: 1 } })
  categories.value = data || []

  if (isEdit.value) {
    const res = await api.get(`/author/submissions/${route.params.id}`)
    form.value = {
      category_id: res.data.category_id,
      title: res.data.title,
      description: res.data.description || '',
      note: res.data.note || '',
    }
  }
}

async function submit() {
  saving.value = true
  message.value = ''
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
      if (value !== null && value !== undefined) fd.append(key, value)
    })
    if (manuscriptFile.value) fd.append('manuscript_file', manuscriptFile.value)
    if (coverFile.value) fd.append('cover_file', coverFile.value)

    if (isEdit.value) {
      await api.post(`/author/submissions/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/author/submissions', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    router.push('/author/submissions')
  } catch (e) {
    const errors = e.response?.data?.errors
    message.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Submission gagal disimpan.')
  } finally {
    saving.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.45rem; color: var(--text-body); font-size: 0.9rem; font-weight: 900; }
.field small { color: var(--text-muted); font-weight: 700; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.75rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.15); }
.btn-primary, .btn-soft { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.75rem 1rem; font-weight: 900; }
.btn-primary { background: var(--color-accent); color: var(--text-btn); }
.btn-soft { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
</style>
