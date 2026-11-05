<template>
  <div class="flex flex-col gap-6">
    <router-link to="/author/submissions" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)">
      <span class="material-symbols-outlined text-[20px]">arrow_back</span>Kembali ke Submission Saya
    </router-link>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <template v-else-if="item.id">
      <!-- Detail Card -->
      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span>
            <h2 class="mt-3 text-2xl font-black" style="color: var(--text-heading)">{{ item.title }}</h2>
            <p class="mt-1 text-sm font-bold" style="color: var(--text-muted)">Kategori: {{ item.category?.name || '-' }} · Tanggal Submit: {{ formatDate(item.submitted_at) }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-sm" style="color: var(--text-muted)">
              <span>Penulis / Author:</span>
              <div v-if="item.submission_authors?.length" class="flex flex-wrap gap-1.5">
                <span v-for="sa in item.submission_authors" :key="sa.id" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                  <span class="material-symbols-outlined text-[13px]">person</span>
                  {{ sa.name }}
                  <span v-if="sa.role" class="text-[10px] opacity-75">({{ sa.role }})</span>
                </span>
              </div>
              <strong v-else style="color: var(--text-heading)">{{ item.author_name }}</strong>
            </div>
          </div>
        </div>

        <p class="mt-5 leading-7 text-sm whitespace-pre-line" style="color: var(--text-body)">{{ item.description || 'Tidak ada deskripsi.' }}</p>

        <!-- Manuscript Files & Revision Button -->
        <div class="mt-6 border-t pt-5" style="border-color: var(--border)">
          <h3 class="font-black text-base" style="color: var(--text-heading)">Manuscript & File Naskah</h3>
          <div class="mt-3 flex flex-wrap items-center gap-3">
            <a v-if="item.manuscript_file" class="btn-primary" :href="storageUrl(item.manuscript_file)" target="_blank">
              Download File Naskah
            </a>
            <a v-if="item.cover_file" class="btn-soft" :href="storageUrl(item.cover_file)" target="_blank">Download Cover</a>
            <router-link v-if="item.status === 'revision'" class="btn-accent" :to="`/author/submissions/${item.id}/revision`">
              <span class="material-symbols-outlined text-[18px]">upload_file</span>
              Upload Manuscript Revisi
            </router-link>
          </div>

          <div v-if="item.manuscript_versions?.length" class="mt-4 grid gap-3">
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Histori File Naskah & Revisi</p>
            <div v-for="ver in item.manuscript_versions" :key="ver.id" class="flex flex-wrap items-center justify-between rounded-xl border p-3" style="border-color: var(--border); background: var(--bg-input)">
              <div>
                <span class="font-black text-sm" style="color: var(--text-heading)">Manuscript v{{ ver.version_number }}</span>
                <p class="text-xs opacity-70" style="color: var(--text-muted)">Diunggah: {{ formatDate(ver.created_at) }}</p>
                <p v-if="ver.note" class="mt-1 text-xs" style="color: var(--text-body)">{{ ver.note }}</p>
              </div>
              <a :href="storageUrl(ver.file_path)" target="_blank" class="btn-soft text-xs py-1 px-3">
                Download
              </a>
            </div>
          </div>
        </div>
      </section>

      <div class="grid gap-6 xl:grid-cols-2">
        <!-- Status Timeline -->
        <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
          <h3 class="text-lg font-black" style="color: var(--text-heading)">Status Timeline</h3>
          <div class="mt-4 grid gap-3">
            <div v-for="step in timeline" :key="step.key" class="flex items-center gap-3">
              <span class="material-symbols-outlined text-[20px]" :class="isStepDone(step.key) ? 'text-green-400' : 'text-slate-500'">
                {{ isStepDone(step.key) ? 'check_circle' : 'radio_button_unchecked' }}
              </span>
              <span class="font-bold text-sm" style="color: var(--text-body)">{{ step.label }}</span>
            </div>
          </div>
        </section>

        <!-- Keputusan Editorial Final -->
        <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
          <h3 class="text-lg font-black" style="color: var(--text-heading)">Keputusan Editorial & Catatan Editor</h3>
          <div v-if="latestDecision" class="mt-4 rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
            <div class="flex items-center justify-between">
              <span :class="statusBadge(decisionStatusToSubStatus(latestDecision.decision))">{{ latestDecision.decision }}</span>
              <span class="text-xs" style="color: var(--text-muted)">{{ formatDate(latestDecision.decided_at) }}</span>
            </div>
            <p class="mt-2 text-xs font-bold" style="color: var(--text-muted)">Oleh: {{ latestDecision.decider?.name || 'Primary Editor' }}</p>
            <p class="mt-3 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ latestDecision.notes || '-' }}</p>
          </div>
          <div v-else-if="item.reviews?.length" class="mt-4 grid gap-3">
            <div v-for="review in item.reviews" :key="review.id" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
              <p class="font-black" style="color: var(--text-heading)">Editor · {{ statusLabel(review.status) }}</p>
              <p class="mt-1 text-sm" style="color: var(--text-muted)">{{ review.note || '-' }}</p>
            </div>
          </div>
          <p v-else class="mt-4 text-sm" style="color: var(--text-muted)">Belum ada keputusan editorial.</p>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const route = useRoute()
const loading = ref(true)
const item = ref({})

const timeline = [
  { key: 'submitted', label: 'Submitted' },
  { key: 'under_review', label: 'Under Review' },
  { key: 'revision', label: 'Revision' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'published', label: 'Published' },
]
const order = timeline.map((step) => step.key)

const latestDecision = computed(() => {
  const decisions = item.value?.editorial_decisions || []
  return decisions.length ? decisions[decisions.length - 1] : null
})

function isStepDone(key) {
  return order.indexOf(key) <= order.indexOf(item.value.status)
}

function statusLabel(value) {
  return {
    submitted: 'Submitted',
    under_review: 'Under Review',
    revision: 'Revision',
    accepted: 'Accepted',
    rejected: 'Rejected',
    published: 'Published',
  }[value] || value
}

function statusBadge(value) {
  const base = 'app-badge'
  if (value === 'accepted' || value === 'published') return `${base} app-badge--success`
  if (value === 'rejected') return `${base} app-badge--red`
  if (value === 'revision') return `${base} app-badge--blue`
  return `${base} app-badge--sky`
}

function decisionStatusToSubStatus(dec) {
  if (dec === 'accepted') return 'accepted'
  if (dec === 'rejected') return 'rejected'
  return 'revision'
}

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-'
}

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await api.get(`/author/submissions/${route.params.id}`)
    item.value = data
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.btn-primary, .btn-soft, .btn-accent { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.7rem 0.9rem; font-weight: 900; text-decoration: none; }
.btn-primary { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
.btn-soft { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
.btn-accent { background: var(--color-accent); color: var(--text-btn); }
</style>
