<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <router-link
          :to="{ name: 'EditorSubmissionDetail', params: { id: route.params.id } }"
          class="back-link"
        >
          <span class="material-symbols-outlined text-[20px]">arrow_back</span>
          Kembali ke Detail Submission
        </router-link>
        <h1 class="mt-3 text-2xl font-black" style="color: var(--text-heading)">Timeline Review Submission</h1>
        <p v-if="submission" class="mt-1 text-sm" style="color: var(--text-muted)">
          {{ submission.title }} · {{ submission.author_name }} ·
          Round aktif: <strong class="text-accent">{{ submission.current_round || 1 }}</strong>
        </p>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <div v-else-if="errorMessage" class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-400">
      {{ errorMessage }}
    </div>

    <template v-else-if="submission">
      <SubmissionTimeline :submission="submission" />

      <section class="rounded-2xl p-5 md:p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-start gap-3">
            <span class="material-symbols-outlined rounded-xl bg-accent/15 p-2 text-2xl text-accent">rate_review</span>
            <div>
              <h2 class="text-lg font-black" style="color: var(--text-heading)">Input review melalui timeline</h2>
              <p class="mt-1 text-sm" style="color: var(--text-muted)">
                Evaluasi dan keputusan final dicatat pada round yang sedang berjalan.
              </p>
            </div>
          </div>
          <span v-if="myAssignment" class="role-badge" :class="myAssignment.role === 'primary' ? 'role-badge--primary' : 'role-badge--co'">
            {{ myAssignment.role === 'primary' ? '★ Primary Editor' : 'Co-editor' }}
          </span>
        </div>
      </section>

      <div v-if="cycles.length" class="relative pl-8 md:pl-12">
        <div class="absolute bottom-0 left-3.5 top-0 w-0.5 md:left-5.5" style="background: var(--border)"></div>

        <article v-for="cycle in cycles" :key="cycle.round" class="relative mb-8 last:mb-0">
          <div
            class="absolute left-[-18px] top-[34px] z-10 h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 md:left-[-26px]"
            :class="cycleDotClass(cycle)"
          ></div>

          <div class="rounded-2xl p-5 md:p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <span class="text-lg font-black" style="color: var(--text-heading)">Round {{ cycle.round }}</span>
                <span :class="cycleStatusBadge(cycle.status)" class="rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-wide">
                  {{ cycleStatusLabel(cycle.status) }}
                </span>
                <span v-if="isCurrentCycle(cycle)" class="rounded-full bg-accent/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-accent">
                  Round aktif
                </span>
              </div>
            </div>

            <div class="mt-5 grid gap-4">
              <section v-if="cycle.revisions?.length" class="timeline-section">
                <div class="section-heading">
                  <span class="material-symbols-outlined text-[20px] text-accent">upload_file</span>
                  <h3>Berkas Revisi Naskah</h3>
                </div>
                <div class="grid gap-2">
                  <div v-for="revision in cycle.revisions" :key="revision.id" class="file-row">
                    <div>
                      <p class="text-xs font-black" style="color: var(--text-heading)">{{ revision.note || 'Berkas Revisi' }}</p>
                      <p class="mt-1 text-[11px]" style="color: var(--text-muted)">
                        {{ revision.user?.name || 'User' }} · {{ formatDateTime(revision.created_at) }}
                      </p>
                    </div>
                    <a v-if="revision.revision_file" :href="storageUrl(revision.revision_file)" target="_blank" class="file-link">
                      <span class="material-symbols-outlined text-[15px]">download</span>
                      Unduh
                    </a>
                  </div>
                </div>
              </section>

              <section class="timeline-section">
                <div class="section-heading">
                  <span class="material-symbols-outlined text-[20px] text-violet-400">rate_review</span>
                  <h3>Evaluasi Editor</h3>
                </div>

                <div v-if="cycle.evaluations?.length" class="grid gap-2">
                  <div v-for="evaluation in cycle.evaluations" :key="evaluation.id" class="rounded-xl border p-3" style="border-color: var(--border)">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <div>
                        <p class="text-sm font-black" style="color: var(--text-heading)">{{ evaluation.editor?.name || 'Editor' }}</p>
                        <p class="mt-0.5 text-[11px]" style="color: var(--text-muted)">
                          {{ editorRole(evaluation.editor_id) }} · {{ formatDateTime(evaluation.submitted_at || evaluation.created_at) }}
                        </p>
                      </div>
                      <span :class="recommendationBadge(evaluation.recommendation)">{{ recommendationLabel(evaluation.recommendation) }}</span>
                    </div>
                    <p class="mt-2 whitespace-pre-line text-xs leading-5" style="color: var(--text-body)">{{ evaluation.notes || '-' }}</p>
                  </div>
                </div>
                <p v-else class="text-xs italic" style="color: var(--text-muted)">Belum ada evaluasi editor pada round ini.</p>

                <div v-if="isCurrentCycle(cycle) && canEvaluate" id="input-review" class="action-panel action-panel--review">
                  <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[20px] text-violet-400">edit_note</span>
                    <div>
                      <h4 class="text-sm font-black" style="color: var(--text-heading)">
                        {{ myEvaluation ? 'Perbarui Review Anda' : 'Segera input review Anda' }}
                      </h4>
                      <p class="mt-1 text-xs" style="color: var(--text-muted)">Review ini akan tersimpan pada Round {{ currentRound }}.</p>
                    </div>
                  </div>

                  <form class="mt-4 grid gap-4" @submit.prevent="submitEvaluation">
                    <label class="field">
                      <span>Rekomendasi Evaluasi *</span>
                      <select v-model="evaluationForm.recommendation" required class="input">
                        <option value="accept">Accept (Terima)</option>
                        <option value="revision">Revision (Perlu Revisi)</option>
                        <option value="reject">Reject (Tolak)</option>
                      </select>
                    </label>
                    <label class="field">
                      <span>Catatan Evaluasi *</span>
                      <textarea v-model="evaluationForm.notes" required class="input min-h-[120px]" placeholder="Tuliskan masukan dan evaluasi detail naskah..."></textarea>
                    </label>
                    <div v-if="evaluationMessage" class="text-sm font-bold" :class="evaluationSuccess ? 'text-green-400' : 'text-red-400'">
                      {{ evaluationMessage }}
                    </div>
                    <button type="submit" :disabled="evaluationSaving" class="submit-button">
                      <span v-if="evaluationSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                      {{ evaluationSaving ? 'Menyimpan...' : 'Simpan Review Saya' }}
                    </button>
                  </form>
                </div>
              </section>

              <section class="timeline-section">
                <div class="section-heading">
                  <span class="material-symbols-outlined text-[20px] text-amber-400">gavel</span>
                  <h3>Keputusan Editor Final</h3>
                </div>

                <div v-if="cycle.decision">
                  <div class="flex flex-wrap items-center gap-3">
                    <span :class="decisionBadge(cycle.decision.decision)" class="rounded-full border px-3 py-1 text-xs font-extrabold uppercase tracking-wide">
                      {{ decisionLabel(cycle.decision.decision) }}
                    </span>
                    <span class="text-xs" style="color: var(--text-muted)">
                      Oleh: <strong style="color: var(--text-heading)">{{ cycle.decision.decider?.name || 'Primary Editor' }}</strong>
                      · {{ formatDateTime(cycle.decision.decided_at || cycle.decision.created_at) }}
                    </span>
                  </div>
                  <p class="mt-2 whitespace-pre-line text-xs leading-5" style="color: var(--text-body)">{{ cycle.decision.notes || '-' }}</p>
                </div>
                <p v-else class="text-xs italic" style="color: var(--text-muted)">Keputusan final belum ditetapkan oleh Primary Editor.</p>

                <div v-if="isCurrentCycle(cycle) && canDecide" class="action-panel action-panel--decision">
                  <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined text-[20px] text-amber-400">gavel</span>
                    <div>
                      <h4 class="text-sm font-black" style="color: var(--text-heading)">Input Keputusan Editor Final</h4>
                      <p class="mt-1 text-xs" style="color: var(--text-muted)">Sebagai Primary Editor, Anda dapat menetapkan keputusan untuk round ini.</p>
                    </div>
                  </div>

                  <form class="mt-4 grid gap-4" @submit.prevent="submitDecision">
                    <label class="field">
                      <span>Keputusan Status *</span>
                      <select v-model="decisionForm.decision" required class="input">
                        <option value="revision_required">Revision Required (Minta Revisi)</option>
                        <option value="accepted">Accepted (Diterima)</option>
                        <option value="rejected">Rejected (Ditolak)</option>
                      </select>
                    </label>
                    <label class="field">
                      <span>Catatan untuk Author *</span>
                      <textarea v-model="decisionForm.notes" required class="input min-h-[120px]" placeholder="Instruksi revisi atau catatan keputusan untuk author..."></textarea>
                    </label>
                    <div v-if="decisionMessage" class="text-sm font-bold" :class="decisionSuccess ? 'text-green-400' : 'text-red-400'">
                      {{ decisionMessage }}
                    </div>
                    <button type="submit" :disabled="decisionSaving" class="decision-button">
                      <span v-if="decisionSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                      <span v-else class="material-symbols-outlined text-[18px]">gavel</span>
                      {{ decisionSaving ? 'Mengirim...' : 'Simpan Keputusan Final' }}
                    </button>
                  </form>
                </div>
              </section>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="rounded-2xl p-10 text-center" style="background: var(--bg-card); border: 1px solid var(--border)">
        <span class="material-symbols-outlined text-5xl" style="color: var(--text-muted)">timeline</span>
        <p class="mt-3 text-sm" style="color: var(--text-muted)">Belum ada data review cycle untuk submission ini.</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import { useAuthStore } from '../../../stores/auth'
import SubmissionTimeline from '../../../components/submissions/SubmissionTimeline.vue'

const route = useRoute()
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user || {})
const loading = ref(false)
const submission = ref(null)
const cycles = ref([])
const editorAssignments = ref([])
const errorMessage = ref('')

const evaluationForm = ref({ recommendation: 'revision', notes: '' })
const evaluationSaving = ref(false)
const evaluationMessage = ref('')
const evaluationSuccess = ref(false)
const decisionForm = ref({ decision: 'revision_required', notes: '' })
const decisionSaving = ref(false)
const decisionMessage = ref('')
const decisionSuccess = ref(false)

const currentRound = computed(() => Number(submission.value?.current_round || 1))
const activeCycle = computed(() => cycles.value.find((cycle) => Number(cycle.round) === currentRound.value) || null)
const myAssignment = computed(() => editorAssignments.value.find(
  (assignment) => String(assignment.editor_id) === String(currentUser.value.id),
))
const myEvaluation = computed(() => (activeCycle.value?.evaluations || []).find(
  (evaluation) => String(evaluation.editor_id) === String(currentUser.value.id),
))
const canEvaluate = computed(() => Boolean(myAssignment.value && activeCycle.value))
const canDecide = computed(() => Boolean(
  activeCycle.value && myAssignment.value?.role === 'primary',
))

function syncForms() {
  evaluationForm.value = {
    recommendation: myEvaluation.value?.recommendation || 'revision',
    notes: myEvaluation.value?.notes || '',
  }
  decisionForm.value = {
    decision: activeCycle.value?.decision?.decision || 'revision_required',
    notes: activeCycle.value?.decision?.notes || '',
  }
}

async function loadTimeline() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/editor/submissions/' + route.params.id + '/review-cycles')
    submission.value = data.submission
    cycles.value = data.cycles || []
    editorAssignments.value = data.editor_assignments || []
    syncForms()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat timeline review.'
  } finally {
    loading.value = false
  }
}

async function submitEvaluation() {
  evaluationSaving.value = true
  evaluationMessage.value = ''
  try {
    await api.post('/editor/submissions/' + route.params.id + '/evaluations', evaluationForm.value)
    evaluationSuccess.value = true
    evaluationMessage.value = 'Review berhasil disimpan di timeline.'
    await loadTimeline()
  } catch (error) {
    evaluationSuccess.value = false
    evaluationMessage.value = error.response?.data?.message || 'Gagal menyimpan review.'
  } finally {
    evaluationSaving.value = false
  }
}

async function submitDecision() {
  decisionSaving.value = true
  decisionMessage.value = ''
  try {
    await api.post('/editor/submissions/' + route.params.id + '/final-decision', decisionForm.value)
    decisionSuccess.value = true
    decisionMessage.value = 'Keputusan final berhasil disimpan di timeline.'
    await loadTimeline()
  } catch (error) {
    decisionSuccess.value = false
    decisionMessage.value = error.response?.data?.message || 'Gagal menyimpan keputusan final.'
  } finally {
    decisionSaving.value = false
  }
}

function isCurrentCycle(cycle) {
  return Number(cycle.round) === currentRound.value
}

function editorRole(editorId) {
  const assignment = editorAssignments.value.find((item) => String(item.editor_id) === String(editorId))
  return assignment?.role === 'primary' ? 'Primary Editor' : 'Co-editor'
}

function cycleStatusLabel(status) {
  return {
    open: 'Sedang Berjalan',
    revision_required: 'Revisi Diminta',
    accepted: 'Diterima',
    rejected: 'Ditolak',
    unknown: 'Selesai',
  }[status] || status
}

function cycleStatusBadge(status) {
  if (status === 'accepted') return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
  if (status === 'rejected') return 'bg-red-500/20 text-red-400 border-red-500/30'
  if (status === 'revision_required') return 'bg-amber-500/20 text-amber-400 border-amber-500/30'
  return 'bg-blue-500/20 text-blue-400 border-blue-500/30'
}

function cycleDotClass(cycle) {
  if (cycle.status === 'accepted') return 'bg-emerald-500 border-emerald-400'
  if (cycle.status === 'rejected') return 'bg-red-500 border-red-400'
  if (cycle.status === 'revision_required') return 'bg-amber-500 border-amber-400'
  return 'bg-blue-500 border-blue-400 animate-pulse'
}

function recommendationLabel(value) {
  return { accept: 'Accept', revision: 'Revision', reject: 'Reject' }[value] || value || '-'
}

function recommendationBadge(value) {
  const base = 'rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase'
  if (value === 'accept') return base + ' border-emerald-500/30 bg-emerald-500/20 text-emerald-400'
  if (value === 'reject') return base + ' border-red-500/30 bg-red-500/20 text-red-400'
  return base + ' border-blue-500/30 bg-blue-500/20 text-blue-400'
}

function decisionBadge(value) {
  if (value === 'accepted') return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
  if (value === 'rejected') return 'bg-red-500/20 text-red-400 border-red-500/30'
  return 'bg-amber-500/20 text-amber-400 border-amber-500/30'
}

function decisionLabel(value) {
  return { revision_required: 'Revision Required', accepted: 'Accepted', rejected: 'Rejected' }[value] || value || '-'
}

function formatDateTime(value) {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(loadTimeline)
</script>

<style scoped>
.back-link { display: inline-flex; align-items: center; gap: .25rem; color: var(--text-muted); font-size: .875rem; font-weight: 800; }
.back-link:hover { color: var(--color-accent); }
.role-badge { border-radius: 999px; border: 1px solid; padding: .4rem .75rem; font-size: .7rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
.role-badge--primary { border-color: rgb(245 158 11 / .3); background: rgb(245 158 11 / .15); color: #fbbf24; }
.role-badge--co { border-color: rgb(59 130 246 / .3); background: rgb(59 130 246 / .15); color: #60a5fa; }
.timeline-section { border-radius: .85rem; padding: 1rem; background: var(--bg-input); border: 1px solid var(--border); }
.section-heading { display: flex; align-items: center; gap: .5rem; margin-bottom: .75rem; }
.section-heading h3 { color: var(--text-heading); font-size: .875rem; font-weight: 900; }
.file-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; border: 1px solid var(--border); border-radius: .65rem; padding: .7rem; }
.file-link { display: inline-flex; align-items: center; gap: .3rem; border-radius: 999px; border: 1px solid var(--border); background: var(--bg-card); color: var(--text-heading); padding: .35rem .65rem; font-size: .7rem; font-weight: 800; }
.file-link:hover { border-color: var(--color-accent); color: var(--color-accent); }
.action-panel { margin-top: 1rem; border-radius: .85rem; padding: 1rem; }
.action-panel--review { border: 1px solid rgb(139 92 246 / .35); background: rgb(139 92 246 / .08); }
.action-panel--decision { border: 1px solid rgb(245 158 11 / .35); background: rgb(245 158 11 / .08); }
.field { display: flex; flex-direction: column; gap: .4rem; color: var(--text-body); font-size: .875rem; font-weight: 700; }
.input { border-radius: .85rem; background: var(--bg-card); border: 1px solid var(--border); color: var(--text-heading); padding: .7rem .9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 3px rgb(37 99 235 / .12); }
.submit-button, .decision-button { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; border-radius: .85rem; padding: .7rem 1.25rem; font-size: .875rem; font-weight: 900; cursor: pointer; }
.submit-button { background: #8b5cf6; color: white; }
.decision-button { background: #f59e0b; color: #111827; }
.submit-button:disabled, .decision-button:disabled { cursor: not-allowed; opacity: .6; }
</style>
