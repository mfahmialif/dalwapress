<template>
  <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
    <div class="flex flex-wrap items-end justify-between gap-3">
      <div>
        <h2 class="text-lg font-black" style="color: var(--text-heading)">Tim & Informasi Proses</h2>
        <p class="text-sm" style="color: var(--text-muted)">Assignment, evaluasi editor, dan keputusan editorial pada submission ini.</p>
      </div>
      <span class="text-xs font-bold" style="color: var(--text-muted)">Round aktif: {{ submission.current_round || 1 }}</span>
    </div>

    <div class="mt-5 grid gap-4 md:grid-cols-3">
      <TeamCard title="Tim Editor" icon="rate_review" icon-class="text-violet-400" :items="editorItems" empty="Belum ada editor yang diassign." />
      <TeamCard title="Tim Layouter" icon="design_services" icon-class="text-emerald-400" :items="layouterItems" empty="Belum ada layouter yang diassign." />
      <TeamCard title="Tim Desain Sampul" icon="palette" icon-class="text-sky-400" :items="coverItems" empty="Belum ada desainer sampul yang diassign." />
    </div>
  </section>

  <section v-if="showDecision" class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
    <h2 class="text-lg font-black" style="color: var(--text-heading)">Masukan & Evaluasi Editor</h2>
    <div v-if="evaluations.length" class="mt-5 grid gap-4 md:grid-cols-2">
      <div v-for="evaluation in evaluations" :key="evaluation.id" class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div>
            <p class="font-black" style="color: var(--text-heading)">{{ evaluation.editor?.name || 'Editor' }}</p>
            <p class="text-xs" style="color: var(--text-muted)">{{ editorRole(evaluation.editor_id) }} · {{ formatDate(evaluation.submitted_at || evaluation.created_at) }}</p>
          </div>
          <span :class="recommendationBadge(evaluation.recommendation)">{{ recommendationLabel(evaluation.recommendation) }}</span>
        </div>
        <p class="mt-3 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ evaluation.notes || '-' }}</p>
      </div>
    </div>
    <p v-else class="mt-4 text-sm" style="color: var(--text-muted)">Belum ada evaluasi editor.</p>

    <div v-if="legacyReviews.length" class="mt-5 border-t pt-5" style="border-color: var(--border)">
      <h3 class="text-sm font-black uppercase tracking-wider" style="color: var(--text-muted)">Catatan Review</h3>
      <div class="mt-3 grid gap-3">
        <div v-for="review in legacyReviews" :key="review.id" class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="font-black" style="color: var(--text-heading)">{{ review.reviewer_name || review.editor?.name || 'Reviewer' }}</p>
            <span :class="statusBadge(review.status)">{{ statusLabel(review.status) }}</span>
          </div>
          <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ review.note || '-' }}</p>
        </div>
      </div>
    </div>
  </section>

  <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
    <h2 class="text-lg font-black" style="color: var(--text-heading)">Keputusan Editorial Final</h2>
    <div v-if="latestDecision" class="mt-5 rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
      <div class="flex flex-wrap items-center justify-between gap-2">
        <span :class="statusBadge(decisionStatus(latestDecision.decision))">{{ decisionLabel(latestDecision.decision) }}</span>
        <span class="text-xs" style="color: var(--text-muted)">{{ formatDate(latestDecision.decided_at || latestDecision.created_at) }}</span>
      </div>
      <p class="mt-2 text-xs font-bold" style="color: var(--text-muted)">Diputuskan oleh: {{ latestDecision.decider?.name || 'Primary Editor' }}</p>
      <p class="mt-3 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ latestDecision.notes || '-' }}</p>
    </div>
    <p v-else class="mt-4 text-sm" style="color: var(--text-muted)">Keputusan final belum ditetapkan oleh Primary Editor.</p>
  </section>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue'

const props = defineProps({
  submission: { type: Object, required: true },
  showDecision: { type: Boolean, default: true },
})

const TeamCard = defineComponent({
  props: { title: String, icon: String, iconClass: String, items: Array, empty: String },
  setup(teamProps) {
    return () => h('div', { class: 'rounded-xl border p-4', style: 'border-color: var(--border); background: var(--bg-input)' }, [
      h('div', { class: 'flex items-center gap-2' }, [
        h('span', { class: `material-symbols-outlined text-[20px] ${teamProps.iconClass}` }, teamProps.icon),
        h('h3', { class: 'text-sm font-black', style: 'color: var(--text-heading)' }, teamProps.title),
      ]),
      ...(teamProps.items?.length
        ? teamProps.items.map((item) => h('div', { key: item.id, class: 'mt-3 border-t pt-3 first:border-t-0 first:pt-0', style: 'border-color: var(--border)' }, [
            h('p', { class: 'text-sm font-black', style: 'color: var(--text-heading)' }, item.name),
            h('p', { class: 'mt-0.5 text-xs', style: 'color: var(--text-muted)' }, item.meta),
          ]))
        : [h('p', { class: 'mt-3 text-xs', style: 'color: var(--text-muted)' }, teamProps.empty)]),
    ])
  },
})

const editorItems = computed(() => (props.submission?.editor_assignments || []).map((item) => ({
  id: item.id,
  name: item.editor?.name || '-',
  meta: `${item.role === 'primary' ? '★ Primary Editor' : 'Co-editor'} · ${item.editor?.email || '-'}`,
})))
const layouterItems = computed(() => (props.submission?.layouter_assignments || []).map((item) => ({
  id: item.id,
  name: item.layouter?.name || '-',
  meta: `Layouter · ${item.layouter?.email || '-'}`,
})))
const coverItems = computed(() => (props.submission?.cover_assignments || []).map((item) => ({
  id: item.id,
  name: item.designer?.name || '-',
  meta: `Desainer Cover · ${item.designer?.email || '-'}`,
})))
const evaluations = computed(() => Array.isArray(props.submission?.editor_evaluations) ? props.submission.editor_evaluations : [])
const legacyReviews = computed(() => Array.isArray(props.submission?.reviews) ? props.submission.reviews : [])
const decisions = computed(() => Array.isArray(props.submission?.editorial_decisions) ? props.submission.editorial_decisions : [])
const latestDecision = computed(() => decisions.value.length ? decisions.value[decisions.value.length - 1] : null)

function editorRole(editorId) {
  const assignment = (props.submission?.editor_assignments || []).find((item) => item.editor_id === editorId)
  return assignment?.role === 'primary' ? 'Primary Editor' : 'Co-editor'
}

function recommendationLabel(value) {
  return { accept: 'Accept', revision: 'Revision', reject: 'Reject' }[value] || value || '-'
}

function recommendationBadge(value) {
  const base = 'app-badge text-xs uppercase font-extrabold'
  if (value === 'accept') return `${base} app-badge--success`
  if (value === 'reject') return `${base} app-badge--red`
  return `${base} app-badge--blue`
}

function decisionStatus(value) {
  if (value === 'accepted') return 'accepted'
  if (value === 'rejected') return 'rejected'
  return 'revision'
}

function decisionLabel(value) {
  return { revision_required: 'Revision Required', accepted: 'Accepted', rejected: 'Rejected' }[value] || value || '-'
}

function statusLabel(value) {
  return { revision: 'Revision', accepted: 'Accepted', rejected: 'Rejected' }[value] || value || '-'
}

function statusBadge(value) {
  const base = 'app-badge'
  if (value === 'accepted') return `${base} app-badge--success`
  if (value === 'rejected') return `${base} app-badge--red`
  return `${base} app-badge--blue`
}

function formatDate(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>
