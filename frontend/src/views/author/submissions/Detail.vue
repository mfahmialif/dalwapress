<template>
  <div class="flex flex-col gap-6">
    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 class="text-2xl font-black" style="color: var(--text-heading)">{{ item.title }}</h2>
          <p class="mt-1 text-sm font-bold" style="color: var(--text-muted)">{{ item.category?.name || '-' }} · {{ formatDate(item.submitted_at) }}</p>
        </div>
        <span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span>
      </div>
      <p class="mt-5 leading-7" style="color: var(--text-body)">{{ item.description || 'Tidak ada deskripsi.' }}</p>
      <div class="mt-5 flex flex-wrap gap-2">
        <a v-if="item.manuscript_file" class="btn-soft" :href="storageUrl(item.manuscript_file)" target="_blank">File Naskah</a>
        <a v-if="item.cover_file" class="btn-soft" :href="storageUrl(item.cover_file)" target="_blank">Cover</a>
        <router-link v-if="item.status === 'revision'" class="btn-primary" :to="`/author/submissions/${item.id}/revision`">Upload Revisi</router-link>
      </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
      <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
        <h3 class="text-lg font-black" style="color: var(--text-heading)">Status Timeline</h3>
        <div class="mt-4 grid gap-3">
          <div v-for="step in timeline" :key="step.key" class="flex items-center gap-3">
            <span class="material-symbols-outlined" :class="isStepDone(step.key) ? 'text-green-400' : 'text-slate-500'">{{ isStepDone(step.key) ? 'check_circle' : 'radio_button_unchecked' }}</span>
            <span class="font-bold" style="color: var(--text-body)">{{ step.label }}</span>
          </div>
        </div>
      </section>

      <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
        <h3 class="text-lg font-black" style="color: var(--text-heading)">Catatan Editor</h3>
        <div class="mt-4 grid gap-3">
          <div v-for="review in item.reviews" :key="review.id" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
            <p class="font-black" style="color: var(--text-heading)">{{ review.reviewer_name || 'Editor' }} · {{ statusLabel(review.status) }}</p>
            <p class="mt-1 text-sm" style="color: var(--text-muted)">{{ review.note || '-' }}</p>
          </div>
          <p v-if="!item.reviews?.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada reviewer notes.</p>
        </div>
      </section>
    </div>

    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <h3 class="text-lg font-black" style="color: var(--text-heading)">Riwayat Revisi</h3>
      <div class="mt-4 grid gap-3">
        <div v-for="revision in item.revisions" :key="revision.id" class="flex flex-wrap items-center justify-between gap-3 rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
          <div>
            <p class="font-black" style="color: var(--text-heading)">{{ formatDate(revision.created_at) }}</p>
            <p class="text-sm" style="color: var(--text-muted)">{{ revision.note || '-' }}</p>
          </div>
          <a class="btn-soft" :href="storageUrl(revision.revision_file)" target="_blank">Lihat File</a>
        </div>
        <p v-if="!item.revisions?.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada revisi.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const route = useRoute()
const item = ref({})
const timeline = [
  { key: 'submitted', label: 'Submitted' },
  { key: 'under_review', label: 'Under Review' },
  { key: 'revision', label: 'Revision' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'published', label: 'Published' },
]
const order = timeline.map((step) => step.key)

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
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-black'
  if (value === 'accepted' || value === 'published') return `${base} bg-green-500/10 text-green-400 border border-green-500/30`
  if (value === 'rejected') return `${base} bg-red-500/10 text-red-400 border border-red-500/30`
  if (value === 'revision') return `${base} bg-yellow-500/10 text-yellow-400 border border-yellow-500/30`
  return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString('id-ID') : '-'
}

onMounted(async () => {
  const { data } = await api.get(`/author/submissions/${route.params.id}`)
  item.value = data
})
</script>

<style scoped>
.btn-primary, .btn-soft { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.7rem 0.9rem; font-weight: 900; }
.btn-primary { background: var(--color-accent); color: var(--text-btn); }
.btn-soft { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
</style>
