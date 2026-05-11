<template>
  <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <h2 class="text-xl font-black" style="color: var(--text-heading)">{{ item.title }}</h2>
      <p class="mt-1 text-sm" style="color: var(--text-muted)">Upload revisi tersedia saat status submission adalah revision.</p>

      <form class="mt-5 grid gap-4" @submit.prevent="submit">
        <label class="field">
          <span>File revisi *</span>
          <input class="input" required type="file" accept=".pdf,.doc,.docx" @change="revisionFile = $event.target.files[0]" />
          <small>PDF/DOCX/DOC, maksimal 50MB.</small>
        </label>
        <label class="field">
          <span>Catatan revisi user</span>
          <textarea v-model="note" class="input min-h-28"></textarea>
        </label>
        <div v-if="message" class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm font-bold text-red-400">{{ message }}</div>
        <button class="btn-primary" :disabled="saving">
          <span class="material-symbols-outlined text-[18px]">{{ saving ? 'progress_activity' : 'upload_file' }}</span>
          Upload Revisi
        </button>
      </form>
    </section>

    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <h3 class="text-lg font-black" style="color: var(--text-heading)">History Revisi Sebelumnya</h3>
      <div class="mt-4 grid gap-3">
        <div v-for="revision in item.revisions" :key="revision.id" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
          <p class="font-black" style="color: var(--text-heading)">{{ formatDate(revision.created_at) }}</p>
          <p class="text-sm" style="color: var(--text-muted)">{{ revision.note || '-' }}</p>
        </div>
        <p v-if="!item.revisions?.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada revisi.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../../axios'

const route = useRoute()
const router = useRouter()
const item = ref({})
const revisionFile = ref(null)
const note = ref('')
const saving = ref(false)
const message = ref('')

async function loadItem() {
  const { data } = await api.get(`/author/submissions/${route.params.id}`)
  item.value = data
}

async function submit() {
  saving.value = true
  message.value = ''
  try {
    const fd = new FormData()
    fd.append('revision_file', revisionFile.value)
    if (note.value) fd.append('note', note.value)
    await api.post(`/author/submissions/${route.params.id}/revision`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    router.push(`/author/submissions/${route.params.id}`)
  } catch (e) {
    const errors = e.response?.data?.errors
    message.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Upload revisi gagal.')
  } finally {
    saving.value = false
  }
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString('id-ID') : '-'
}

onMounted(loadItem)
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.45rem; color: var(--text-body); font-size: 0.9rem; font-weight: 900; }
.field small { color: var(--text-muted); font-weight: 700; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.75rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.75rem 1rem; font-weight: 900; background: var(--color-accent); color: var(--text-btn); }
</style>
