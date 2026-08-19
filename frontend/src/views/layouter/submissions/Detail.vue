<template>
  <div class="flex flex-col gap-6">
    <router-link to="/layouter/submissions" class="back-link">
      <span class="material-symbols-outlined text-[20px]">arrow_back</span>
      Kembali
    </router-link>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <div v-else-if="errorMessage" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-400">
      {{ errorMessage }}
    </div>

    <template v-else-if="submission">
      <SubmissionTimeline :submission="submission" />

      <SubmissionOverview
        :submission="submission"
        assignment-label="Peran Anda"
        assignment-value="Layouter"
      >
        <template #header-badge>
          <span class="role-badge">Layouter</span>
        </template>
      </SubmissionOverview>

      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-black" style="color: var(--text-heading)">Upload Naskah Hasil Layout</h2>
            <p class="mt-1 text-sm" style="color: var(--text-muted)">Unggah berkas layout terbaru untuk diteruskan ke tahap finalisasi dan penerbitan.</p>
          </div>
          <span v-if="myAssignment?.note" class="rounded-lg border px-3 py-2 text-xs" style="border-color: var(--border); color: var(--text-muted)">
            Instruksi: {{ myAssignment.note }}
          </span>
        </div>

        <form class="mt-5 grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end" @submit.prevent="uploadLayout">
          <label class="field">
            <span>File Hasil Layout (PDF/DOC/DOCX) *</span>
            <input ref="layoutInput" type="file" @change="layoutFile = $event.target.files[0]" class="input" accept=".pdf,.doc,.docx" required />
          </label>
          <label class="field">
            <span>Catatan Layout</span>
            <textarea v-model="layoutForm.note" class="input min-h-[44px]" placeholder="Catatan hasil layout..."></textarea>
          </label>
          <button type="submit" :disabled="uploading" class="upload-button">
            <span v-if="uploading" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
            <span v-else class="material-symbols-outlined text-[18px]">upload_file</span>
            {{ uploading ? 'Mengupload...' : 'Upload Hasil Layout' }}
          </button>
        </form>
        <div v-if="uploadMessage" class="mt-4 text-sm font-bold" :class="uploadSuccess ? 'text-green-400' : 'text-red-400'">{{ uploadMessage }}</div>
      </section>

      <SubmissionWorkflowSummary :submission="submission" />
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../../axios'
import { useAuthStore } from '../../../stores/auth'
import SubmissionOverview from '../../../components/submissions/SubmissionOverview.vue'
import SubmissionTimeline from '../../../components/submissions/SubmissionTimeline.vue'
import SubmissionWorkflowSummary from '../../../components/submissions/SubmissionWorkflowSummary.vue'

const route = useRoute()
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user || {})
const loading = ref(false)
const submission = ref(null)
const errorMessage = ref('')
const layoutForm = ref({ note: '' })
const layoutFile = ref(null)
const layoutInput = ref(null)
const uploading = ref(false)
const uploadMessage = ref('')
const uploadSuccess = ref(false)

const myAssignment = computed(() => submission.value?.layouter_assignments?.find((assignment) => assignment.layouter_id === currentUser.value.id))

async function loadData() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get(`/layouter/submissions/${route.params.id}`)
    submission.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat detail submission.'
  } finally {
    loading.value = false
  }
}

async function uploadLayout() {
  if (!layoutFile.value) {
    uploadSuccess.value = false
    uploadMessage.value = 'Silakan pilih file hasil layout terlebih dahulu.'
    return
  }

  uploading.value = true
  uploadMessage.value = ''
  const formData = new FormData()
  formData.append('revision_file', layoutFile.value)
  if (layoutForm.value.note) formData.append('note', layoutForm.value.note)

  try {
    const { data } = await api.post(`/layouter/submissions/${route.params.id}/upload-layout`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    submission.value = data.submission
    layoutForm.value.note = ''
    layoutFile.value = null
    if (layoutInput.value) layoutInput.value.value = ''
    uploadSuccess.value = true
    uploadMessage.value = 'Hasil layout berhasil diunggah.'
  } catch (error) {
    uploadSuccess.value = false
    uploadMessage.value = error.response?.data?.message || 'Gagal mengunggah hasil layout.'
  } finally {
    uploading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.back-link { display: inline-flex; align-items: center; gap: .25rem; color: var(--text-muted); font-size: .875rem; font-weight: 800; }
.back-link:hover { color: var(--color-accent); }
.role-badge { border: 1px solid rgb(16 185 129 / .3); border-radius: 999px; background: rgb(16 185 129 / .15); color: #34d399; padding: .4rem .75rem; font-size: .7rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
.field { display: flex; flex-direction: column; gap: .4rem; color: var(--text-body); font-size: .875rem; font-weight: 700; }
.input { width: 100%; border-radius: .85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: .7rem .9rem; outline: none; }
.upload-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; border-radius: .85rem; background: #10b981; color: white; padding: .7rem 1rem; font-size: .875rem; font-weight: 900; cursor: pointer; white-space: nowrap; }
.upload-button:disabled { cursor: not-allowed; opacity: .6; }
</style>
