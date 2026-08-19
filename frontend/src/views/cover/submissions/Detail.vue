<template>
  <div class="flex flex-col gap-6">
    <router-link to="/cover/submissions" class="back-link">
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
        assignment-value="Desainer Cover"
      >
        <template #header-badge>
          <span class="role-badge">Desainer Cover</span>
        </template>
      </SubmissionOverview>

      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h2 class="text-lg font-black" style="color: var(--text-heading)">Hasil Desain Sampul</h2>
            <p class="mt-1 text-sm" style="color: var(--text-muted)">Gunakan cover awal penulis atau unggah hasil desain sampul terbaru.</p>
          </div>
          <span v-if="myAssignment?.note" class="rounded-lg border px-3 py-2 text-xs" style="border-color: var(--border); color: var(--text-muted)">
            Instruksi: {{ myAssignment.note }}
          </span>
        </div>

        <div v-if="submission.cover_file" class="mt-5 grid gap-4 rounded-xl border p-4 md:grid-cols-[180px_1fr]" style="border-color: rgb(245 158 11 / .3); background: rgb(245 158 11 / .1)">
          <div class="flex min-h-[160px] items-center justify-center rounded-lg border p-2" style="border-color: rgb(245 158 11 / .2); background: rgb(0 0 0 / .15)">
            <img v-if="!coverPreviewFailed" :src="storageUrl(submission.cover_file)" alt="Preview cover awal" class="max-h-44 rounded object-contain" @error="coverPreviewFailed = true" />
            <span v-else class="material-symbols-outlined text-5xl text-amber-400">image</span>
          </div>
          <div class="flex flex-col items-start justify-center gap-3">
            <div>
              <p class="font-black text-amber-300">Cover awal penulis tersedia</p>
              <p class="mt-1 text-sm" style="color: var(--text-body)">Anda dapat menyetujui berkas awal ini sebagai hasil desain sampul.</p>
            </div>
            <div class="flex flex-wrap gap-2">
              <a :href="storageUrl(submission.cover_file)" target="_blank" class="file-link">
                <span class="material-symbols-outlined text-[16px]">open_in_new</span> Lihat Cover Awal
              </a>
              <button type="button" @click="useInitialCover" :disabled="uploading" class="use-button">
                <span v-if="uploading" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                <span v-else class="material-symbols-outlined text-[16px]">file_copy</span>
                Gunakan Cover Awal
              </button>
            </div>
          </div>
        </div>

        <div class="mt-5 border-t pt-5" style="border-color: var(--border)">
          <form class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end" @submit.prevent="uploadCover">
            <label class="field">
              <span>File Desain Cover Baru *</span>
              <input ref="coverInput" type="file" @change="coverFile = $event.target.files[0]" class="input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.psd,.ai,.zip,.rar" required />
            </label>
            <label class="field">
              <span>Catatan Desain</span>
              <textarea v-model="coverForm.note" class="input min-h-[44px]" placeholder="Catatan hasil desain cover..."></textarea>
            </label>
            <button type="submit" :disabled="uploading" class="upload-button">
              <span v-if="uploading" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
              <span v-else class="material-symbols-outlined text-[18px]">upload_file</span>
              {{ uploading ? 'Mengupload...' : 'Upload Hasil Cover' }}
            </button>
          </form>
        </div>
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
import { storageUrl } from '../../../utils/asset'
import SubmissionOverview from '../../../components/submissions/SubmissionOverview.vue'
import SubmissionTimeline from '../../../components/submissions/SubmissionTimeline.vue'
import SubmissionWorkflowSummary from '../../../components/submissions/SubmissionWorkflowSummary.vue'

const route = useRoute()
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user || {})
const loading = ref(false)
const submission = ref(null)
const errorMessage = ref('')
const coverForm = ref({ note: '' })
const coverFile = ref(null)
const coverInput = ref(null)
const coverPreviewFailed = ref(false)
const uploading = ref(false)
const uploadMessage = ref('')
const uploadSuccess = ref(false)

const myAssignment = computed(() => submission.value?.cover_assignments?.find((assignment) => assignment.cover_designer_id === currentUser.value.id))

async function loadData() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get(`/cover/submissions/${route.params.id}`)
    submission.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat detail submission.'
  } finally {
    loading.value = false
  }
}

async function useInitialCover() {
  if (!window.confirm('Gunakan berkas cover awal penulis sebagai hasil desain sampul?')) return

  uploading.value = true
  uploadMessage.value = ''
  try {
    const { data } = await api.post(`/cover/submissions/${route.params.id}/upload-cover`, {
      use_initial_cover: true,
      note: coverForm.value.note || 'Menggunakan berkas sampul / cover dari pengajuan awal penulis.',
    })
    submission.value = data.submission
    coverForm.value.note = ''
    uploadSuccess.value = true
    uploadMessage.value = 'Cover awal penulis berhasil digunakan.'
  } catch (error) {
    uploadSuccess.value = false
    uploadMessage.value = error.response?.data?.message || 'Gagal menggunakan cover awal penulis.'
  } finally {
    uploading.value = false
  }
}

async function uploadCover() {
  if (!coverFile.value) {
    uploadSuccess.value = false
    uploadMessage.value = 'Silakan pilih file desain cover terlebih dahulu.'
    return
  }

  uploading.value = true
  uploadMessage.value = ''
  const formData = new FormData()
  formData.append('revision_file', coverFile.value)
  if (coverForm.value.note) formData.append('note', coverForm.value.note)

  try {
    const { data } = await api.post(`/cover/submissions/${route.params.id}/upload-cover`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    submission.value = data.submission
    coverForm.value.note = ''
    coverFile.value = null
    if (coverInput.value) coverInput.value.value = ''
    uploadSuccess.value = true
    uploadMessage.value = 'Hasil desain cover berhasil diunggah.'
  } catch (error) {
    uploadSuccess.value = false
    uploadMessage.value = error.response?.data?.message || 'Gagal mengunggah hasil desain cover.'
  } finally {
    uploading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.back-link { display: inline-flex; align-items: center; gap: .25rem; color: var(--text-muted); font-size: .875rem; font-weight: 800; }
.back-link:hover { color: var(--color-accent); }
.role-badge { border: 1px solid rgb(14 165 233 / .3); border-radius: 999px; background: rgb(14 165 233 / .15); color: #38bdf8; padding: .4rem .75rem; font-size: .7rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
.field { display: flex; flex-direction: column; gap: .4rem; color: var(--text-body); font-size: .875rem; font-weight: 700; }
.input { width: 100%; border-radius: .85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: .7rem .9rem; outline: none; }
.file-link, .use-button, .upload-button { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; border-radius: .7rem; padding: .6rem .85rem; font-size: .75rem; font-weight: 900; }
.file-link { border: 1px solid rgb(14 165 233 / .3); background: rgb(14 165 233 / .12); color: #7dd3fc; }
.use-button { border: 1px solid rgb(14 165 233 / .3); background: #0ea5e9; color: white; cursor: pointer; }
.upload-button { border-radius: .85rem; background: #0ea5e9; color: white; padding: .7rem 1rem; font-size: .875rem; cursor: pointer; white-space: nowrap; }
.use-button:disabled, .upload-button:disabled { cursor: not-allowed; opacity: .6; }
</style>
