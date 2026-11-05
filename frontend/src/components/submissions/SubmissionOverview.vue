<template>
  <article class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <span :class="statusBadge(submission.status)">{{ statusLabel(submission.status) }}</span>
        <h1 class="mt-3 text-2xl font-black" style="color: var(--text-heading)">{{ submission.title }}</h1>
        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-sm" style="color: var(--text-muted)">
          <span>Author / Pengirim:</span>
          <div v-if="submission.submission_authors?.length" class="flex flex-wrap gap-1.5">
            <span v-for="sa in submission.submission_authors" :key="sa.id" class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
              <span class="material-symbols-outlined text-[13px]">person</span>
              {{ sa.name }}
              <span v-if="sa.role" class="text-[10px] opacity-75">({{ sa.role }})</span>
            </span>
          </div>
          <strong v-else style="color: var(--text-heading)">{{ submission.author_name || '-' }}</strong>
        </div>
      </div>
      <slot name="header-badge"></slot>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 md:grid-cols-4">
      <Info label="Kategori Buku" :value="submission.category?.name || '-'" />
      <Info label="Email Pengirim" :value="submission.email || '-'" />
      <Info label="Nomor Telepon / HP" :value="submission.phone || '-'" />
      <Info label="Status Submisi" :value="statusLabel(submission.status)" />
      <Info label="Tanggal Masuk" :value="formatDate(submission.submitted_at || submission.created_at)" />
      <Info label="Tanggal Review" :value="formatDate(submission.reviewed_at)" />
      <Info label="Primary Editor" :value="primaryEditorName" />
      <Info label="Tim Layouter" :value="assignedLayoutersList" />
      <Info label="Tim Desainer Sampul" :value="assignedCoverDesignersList" />
      <Info :label="assignmentLabel" :value="assignmentValue" />
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-2">
      <div class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
        <h3 class="text-sm font-black uppercase tracking-wider" style="color: var(--text-muted)">Deskripsi Naskah</h3>
        <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.description || 'Tidak ada deskripsi.' }}</p>
      </div>
      <div class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
        <h3 class="text-sm font-black uppercase tracking-wider" style="color: var(--text-muted)">Catatan Pengirim</h3>
        <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.note || 'Tidak ada catatan tambahan.' }}</p>
      </div>
    </div>

    <div class="mt-6 border-t pt-5" style="border-color: var(--border)">
      <div>
        <h3 class="text-base font-black" style="color: var(--text-heading)">Manuscript & Berkas Naskah</h3>
        <p class="text-xs" style="color: var(--text-muted)">Unduh berkas utama dan berkas revisi naskah.</p>
      </div>
      <div class="mt-4 flex flex-wrap gap-3">
        <a v-if="submission.manuscript_file" :href="storageUrl(submission.manuscript_file)" target="_blank" class="file-btn">
          <span class="material-symbols-outlined text-[20px]">description</span>
          Download Manuscript Naskah Awal
        </a>
        <a v-if="submission.cover_file" :href="storageUrl(submission.cover_file)" target="_blank" class="file-btn">
          <span class="material-symbols-outlined text-[20px]">image</span>
          Download Sampul (Cover)
        </a>
        <span v-if="!submission.manuscript_file && !submission.cover_file" class="text-sm" style="color: var(--text-muted)">Belum ada berkas utama.</span>
      </div>
      <SubmissionFileHistory :submission="submission" />
    </div>
  </article>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue'
import { storageUrl } from '../../utils/asset'
import SubmissionFileHistory from './SubmissionFileHistory.vue'

const props = defineProps({
  submission: { type: Object, required: true },
  assignmentLabel: { type: String, default: 'Penugasan Anda' },
  assignmentValue: { type: String, default: '-' },
})

const Info = defineComponent({
  props: { label: String, value: String },
  setup(infoProps) {
    return () => h('div', { class: 'rounded-xl border p-4', style: 'border-color: var(--border); background: var(--bg-input)' }, [
      h('p', { class: 'text-xs font-bold uppercase tracking-wider', style: 'color: var(--text-muted)' }, infoProps.label),
      h('p', { class: 'mt-1 text-sm font-black', style: 'color: var(--text-heading)' }, infoProps.value),
    ])
  },
})

const primaryEditorName = computed(() => {
  const assignment = props.submission?.editor_assignments?.find((item) => item.role === 'primary')
  return assignment?.editor?.name || '-'
})

const assignedLayoutersList = computed(() => {
  const assignments = Array.isArray(props.submission?.layouter_assignments) ? props.submission.layouter_assignments : []
  return assignments.map((item) => item.layouter?.name).filter(Boolean).join(', ') || '-'
})

const assignedCoverDesignersList = computed(() => {
  const assignments = Array.isArray(props.submission?.cover_assignments) ? props.submission.cover_assignments : []
  return assignments.map((item) => item.designer?.name).filter(Boolean).join(', ') || '-'
})

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

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<style scoped>
.file-btn { display: inline-flex; align-items: center; gap: .5rem; border-radius: 999px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: .7rem 1rem; font-weight: 800; text-decoration: none; transition: background .2s; }
.file-btn:hover { background: var(--hover-nav-bg); color: var(--color-accent); }
</style>
