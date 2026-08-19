<template>
  <div class="flex flex-col gap-6">
    <router-link to="/editor/submissions" class="back-link">
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
        :assignment-value="myAssignment?.role === 'primary' ? 'Primary Editor' : 'Co-editor'"
      >
        <template #header-badge>
          <span class="role-badge" :class="myAssignment?.role === 'primary' ? 'role-badge--primary' : 'role-badge--co'">
            {{ myAssignment?.role === 'primary' ? '★ Primary Editor' : 'Co-editor' }}
          </span>
        </template>
      </SubmissionOverview>

      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex items-start gap-3">
            <span class="material-symbols-outlined rounded-xl bg-accent/15 p-2 text-2xl text-accent">timeline</span>
            <div>
              <h2 class="text-lg font-black" style="color: var(--text-heading)">Timeline Review Editor</h2>
              <p v-if="needsReviewInput" class="mt-1 text-sm" style="color: var(--text-muted)">
                Review Anda untuk round {{ currentRound }} belum diinputkan.
              </p>
              <p v-else class="mt-1 text-sm" style="color: var(--text-muted)">
                Review Anda untuk round {{ currentRound }} sudah tersimpan di timeline.
              </p>
            </div>
          </div>

          <router-link
            :to="{ name: 'EditorSubmissionTimeline', params: { id: route.params.id } }"
            class="timeline-link"
          >
            <span class="material-symbols-outlined text-[18px]">open_in_new</span>
            {{ needsReviewInput ? 'Segera input review' : 'Buka Timeline Review' }}
          </router-link>
        </div>

        <div v-if="needsReviewInput || needsFinalDecision" class="mt-5 grid gap-3 md:grid-cols-2">
          <div v-if="needsReviewInput" class="notice notice--warning">
            <span class="material-symbols-outlined text-[20px]">priority_high</span>
            <span>Segera input review dengan klik tombol di atas. Form akan tersedia di timeline.</span>
          </div>
          <div v-if="needsFinalDecision" class="notice notice--info">
            <span class="material-symbols-outlined text-[20px]">gavel</span>
            <span>Keputusan editor final belum diinputkan. Input juga dilakukan melalui timeline.</span>
          </div>
        </div>
      </section>
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

const route = useRoute()
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user || {})
const loading = ref(false)
const submission = ref(null)
const errorMessage = ref('')

const currentRound = computed(() => Number(submission.value?.current_round || 1))
const myAssignment = computed(() => (submission.value?.editor_assignments || []).find(
  (assignment) => String(assignment.editor_id) === String(currentUser.value.id),
))
const evaluations = computed(() => Array.isArray(submission.value?.editor_evaluations) ? submission.value.editor_evaluations : [])
const decisions = computed(() => Array.isArray(submission.value?.editorial_decisions) ? submission.value.editorial_decisions : [])
const myEvaluationForCurrentRound = computed(() => evaluations.value.find(
  (evaluation) => String(evaluation.editor_id) === String(currentUser.value.id)
    && Number(evaluation.round || 1) === currentRound.value,
))
const finalDecisionForCurrentRound = computed(() => decisions.value.find(
  (decision) => Number(decision.round || 1) === currentRound.value,
))
const needsReviewInput = computed(() => Boolean(myAssignment.value && !myEvaluationForCurrentRound.value))
const needsFinalDecision = computed(() => myAssignment.value?.role === 'primary' && !finalDecisionForCurrentRound.value)

async function loadData() {
  loading.value = true
  errorMessage.value = ''
  try {
    const { data } = await api.get('/editor/submissions/' + route.params.id)
    submission.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat detail submission.'
  } finally {
    loading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.back-link { display: inline-flex; align-items: center; gap: .25rem; color: var(--text-muted); font-size: .875rem; font-weight: 800; }
.back-link:hover { color: var(--color-accent); }
.role-badge { border-radius: 999px; border: 1px solid; padding: .4rem .75rem; font-size: .7rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
.role-badge--primary { border-color: rgb(245 158 11 / .3); background: rgb(245 158 11 / .15); color: #fbbf24; }
.role-badge--co { border-color: rgb(59 130 246 / .3); background: rgb(59 130 246 / .15); color: #60a5fa; }
.timeline-link { display: inline-flex; align-items: center; justify-content: center; gap: .45rem; border-radius: .85rem; background: var(--color-accent); color: var(--text-btn); padding: .7rem 1rem; font-size: .8rem; font-weight: 900; white-space: nowrap; }
.timeline-link:hover { filter: brightness(1.08); }
.notice { display: flex; align-items: flex-start; gap: .6rem; border-radius: .85rem; padding: .8rem .9rem; font-size: .8rem; font-weight: 700; line-height: 1.45; }
.notice--warning { border: 1px solid rgb(245 158 11 / .3); background: rgb(245 158 11 / .1); color: #fbbf24; }
.notice--info { border: 1px solid rgb(59 130 246 / .3); background: rgb(59 130 246 / .1); color: #60a5fa; }
</style>
