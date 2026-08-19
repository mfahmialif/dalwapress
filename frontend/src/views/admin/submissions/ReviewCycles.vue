<template>
  <div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <router-link :to="`/administrator/submissions/${route.params.id}`" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)">
          <span class="material-symbols-outlined text-[20px]">arrow_back</span>
          Kembali ke Detail Submission
        </router-link>
        <h1 class="mt-2 text-2xl font-black" style="color: var(--text-heading)">Review Cycles Timeline</h1>
        <p v-if="submission" class="text-sm mt-1" style="color: var(--text-muted)">
          {{ submission.title }} · <strong>{{ submission.author_name }}</strong> ·
          <span :class="statusBadge(submission.status)">{{ statusLabel(submission.status) }}</span>
          · Round saat ini: <strong class="text-accent">{{ submission.current_round || 1 }}</strong>
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <template v-else-if="cycles.length">
      <!-- TIMELINE -->
      <div class="relative pl-8 md:pl-12">
        <!-- Timeline Vertical Line -->
        <div class="absolute left-3.5 md:left-5.5 top-0 bottom-0 w-0.5" style="background: var(--border)"></div>

        <div v-for="(cycle, idx) in cycles" :key="cycle.round" class="relative mb-8 last:mb-0">
          <!-- Timeline Dot (100% Centered over line & vertically aligned with Cycle title) -->
          <div class="absolute left-[-18px] md:left-[-26px] top-[34px] -translate-x-1/2 -translate-y-1/2 w-4 h-4 rounded-full border-2 z-10"
            :class="cycleDotClass(cycle)">
          </div>

          <!-- Cycle Card -->
          <div class="rounded-2xl p-5 md:p-6 transition-all" style="background: var(--bg-card); border: 1px solid var(--border)">
            <!-- Cycle Header -->
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="flex items-center gap-3">
                <span class="text-lg font-black" style="color: var(--text-heading)">Cycle {{ cycle.round }}</span>
                <span :class="cycleStatusBadge(cycle.status)" class="text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                  {{ cycleStatusLabel(cycle.status) }}
                </span>
              </div>
              <div class="flex gap-2">
                <!-- Upload Revision Button (only for cycles > 1 that are 'open') -->
                <button v-if="cycle.round > 1 && cycle.status === 'open' && cycle.revisions.length === 0"
                  @click="openRevisionModal(cycle.round)"
                  class="rounded-xl bg-accent px-3 py-1.5 text-xs font-black text-white flex items-center gap-1 cursor-pointer hover:opacity-90 transition shadow-md">
                  <span class="material-symbols-outlined text-[16px]">upload_file</span>
                  Upload Revisi Naskah
                </button>
                <!-- Add Evaluation Button -->
                <button v-if="cycle.status === 'open' || cycle.status === 'revision_required'"
                  @click="openEvalModal(cycle.round)"
                  class="rounded-xl bg-purple-500 px-3 py-1.5 text-xs font-black text-white flex items-center gap-1 cursor-pointer hover:opacity-90 transition shadow-md">
                  <span class="material-symbols-outlined text-[16px]">edit_note</span>
                  Isi Evaluasi
                </button>
                <!-- Final Decision Button -->
                <button v-if="cycle.status === 'open'"
                  @click="openDecisionModal(cycle.round)"
                  class="rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-black text-black flex items-center gap-1 cursor-pointer hover:opacity-90 transition shadow-md">
                  <span class="material-symbols-outlined text-[16px]">gavel</span>
                  Keputusan Final
                </button>
                <!-- Change Decision (for closed cycles) -->
                <button v-if="cycle.decision && cycle.status !== 'open'"
                  @click="openDecisionModal(cycle.round)"
                  class="rounded-xl border px-3 py-1.5 text-xs font-bold flex items-center gap-1 cursor-pointer transition"
                  style="border-color: var(--border); color: var(--text-muted)">
                  <span class="material-symbols-outlined text-[16px]">edit</span>
                  Ubah Keputusan
                </button>
              </div>
            </div>

            <!-- Cycle Content Steps -->
            <div class="mt-5 grid gap-4">

              <!-- STEP 1: Revision Upload (for cycles > 1) -->
              <div v-if="cycle.round > 1" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
                <div class="flex items-center gap-2 mb-2">
                  <span class="material-symbols-outlined text-accent text-[20px]">upload_file</span>
                  <h4 class="font-black text-sm" style="color: var(--text-heading)">Berkas Revisi Naskah</h4>
                </div>
                <div v-if="cycle.revisions.length" class="grid gap-2">
                  <div v-for="rev in cycle.revisions" :key="rev.id" class="flex flex-wrap items-center justify-between gap-2 rounded-lg border p-2.5" style="border-color: var(--border)">
                    <div>
                      <p class="font-bold text-xs" style="color: var(--text-heading)">{{ rev.note || 'Berkas Revisi' }}</p>
                      <p class="text-[11px] mt-0.5" style="color: var(--text-muted)">
                        Diunggah oleh: <span :class="roleBadgeInline(rev.user?.role?.name)">{{ rev.user?.name || 'User' }} ({{ rev.user?.role?.name || 'User' }})</span>
                        · {{ formatDateTime(rev.created_at) }}
                      </p>
                    </div>
                    <a :href="storageUrl(rev.revision_file)" target="_blank" class="file-btn text-[11px] py-1 px-2.5">
                      <span class="material-symbols-outlined text-[14px]">download</span> Download
                    </a>
                  </div>
                </div>
                <p v-else class="text-xs italic" style="color: var(--text-muted)">Belum ada revisi yang diunggah untuk cycle ini.</p>
              </div>

              <!-- STEP 2: Editor Evaluations -->
              <div class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
                <div class="flex items-center gap-2 mb-2">
                  <span class="material-symbols-outlined text-purple-400 text-[20px]">rate_review</span>
                  <h4 class="font-black text-sm" style="color: var(--text-heading)">Evaluasi Editor</h4>
                </div>
                <div v-if="cycle.evaluations.length" class="grid gap-2">
                  <div v-for="ev in cycle.evaluations" :key="ev.id" class="rounded-lg border p-3" style="border-color: var(--border)">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                      <div class="flex items-center gap-2">
                        <span class="font-bold text-sm" style="color: var(--text-heading)">{{ ev.editor?.name || 'Editor' }}</span>
                        <span v-if="isEditorPrimary(ev.editor_id)" class="text-amber-400 text-[11px] font-bold">★ Primary</span>
                        <span v-else class="text-[11px] font-bold" style="color: var(--text-muted)">Co-Editor</span>
                      </div>
                      <span :class="recommendationBadge(ev.recommendation)" class="text-[10px]">{{ ev.recommendation }}</span>
                    </div>
                    <p class="mt-2 text-xs whitespace-pre-line leading-5" style="color: var(--text-body)">{{ ev.notes || '-' }}</p>
                    <p class="mt-1 text-[10px]" style="color: var(--text-muted)">{{ formatDateTime(ev.submitted_at || ev.created_at) }}</p>
                  </div>
                </div>
                <p v-else class="text-xs italic" style="color: var(--text-muted)">Belum ada evaluasi editor di cycle ini.</p>
              </div>

              <!-- STEP 3: Final Decision -->
              <div class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
                <div class="flex items-center gap-2 mb-2">
                  <span class="material-symbols-outlined text-amber-400 text-[20px]">gavel</span>
                  <h4 class="font-black text-sm" style="color: var(--text-heading)">Keputusan Final</h4>
                </div>
                <div v-if="cycle.decision">
                  <div class="flex flex-wrap items-center gap-3">
                    <span :class="decisionBadge(cycle.decision.decision)" class="text-xs px-3 py-1 rounded-full font-extrabold uppercase tracking-wide">
                      {{ decisionLabel(cycle.decision.decision) }}
                    </span>
                    <span class="text-xs" style="color: var(--text-muted)">
                      Oleh: <strong style="color: var(--text-heading)">{{ cycle.decision.decider?.name || 'Admin' }}</strong>
                      · {{ formatDateTime(cycle.decision.decided_at) }}
                    </span>
                  </div>
                  <p class="mt-2 text-xs whitespace-pre-line leading-5" style="color: var(--text-body)">{{ cycle.decision.notes }}</p>
                </div>
                <p v-else class="text-xs italic" style="color: var(--text-muted)">Menunggu keputusan final dari Primary Editor / Admin...</p>
              </div>

            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-16">
      <span class="material-symbols-outlined text-5xl" style="color: var(--text-muted)">inbox</span>
      <p class="mt-3 text-sm" style="color: var(--text-muted)">Belum ada data review cycle untuk submission ini.</p>
    </div>

    <!-- MODAL: Upload Revision -->
    <Teleport to="body">
      <div v-if="showRevisionModal" :data-theme="currentTheme" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md">
        <div class="w-full max-w-lg rounded-2xl p-6 shadow-2xl transition-colors duration-300" :class="modalCard">
          <div class="flex items-center justify-between border-b pb-3" :class="modalBorder">
            <div>
              <h3 class="text-lg font-black" :class="modalTitle">Upload Revisi Naskah — Cycle {{ activeRound }}</h3>
              <p class="text-xs" :class="modalSub">Unggah berkas revisi untuk round ini.</p>
            </div>
            <button @click="showRevisionModal = false" class="cursor-pointer transition" :class="modalClose"><span class="material-symbols-outlined">close</span></button>
          </div>
          <form class="mt-4 grid gap-4" @submit.prevent="submitRevision">
            <label class="field" :class="modalLabel">
              <span>File Revisi (PDF/DOC/DOCX) *</span>
              <input type="file" ref="revisionFileInput" accept=".pdf,.doc,.docx" required class="input" :class="modalInput" />
            </label>
            <label class="field" :class="modalLabel">
              <span>Catatan Revisi</span>
              <textarea v-model="revisionForm.note" class="input min-h-[80px]" :class="modalInput" placeholder="Catatan revisi naskah..."></textarea>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span class="text-[11px] font-bold flex items-center gap-1 w-full" :class="modalSub">
                  <span class="material-symbols-outlined text-[14px] text-amber-400">lightbulb</span> Catatan Cepat:
                </span>
                <button v-for="(h, i) in revisionHints" :key="i" type="button" @click="revisionForm.note = h"
                  class="text-[11px] font-medium rounded-lg px-2.5 py-1 border transition cursor-pointer hover:bg-accent hover:text-white text-left"
                  :class="modalChip">{{ h }}</button>
              </div>
            </label>
            <div v-if="revisionMsg" class="text-sm font-bold" :class="revisionSuccess ? 'text-green-500' : 'text-red-500'">{{ revisionMsg }}</div>
            <div class="flex justify-end gap-2 border-t pt-4" :class="modalBorder">
              <button type="button" @click="showRevisionModal = false" class="rounded-xl border px-4 py-2 text-sm font-bold cursor-pointer transition" :class="modalBtnCancel">Batal</button>
              <button type="submit" :disabled="revisionSaving" class="rounded-xl bg-accent px-5 py-2 text-sm font-black text-white flex items-center gap-2 cursor-pointer hover:opacity-90">
                <span v-if="revisionSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                Upload Revisi
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL: Evaluation -->
    <Teleport to="body">
      <div v-if="showEvalModal" :data-theme="currentTheme" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md">
        <div class="w-full max-w-lg rounded-2xl p-6 shadow-2xl transition-colors duration-300 max-h-[90vh] overflow-y-auto" :class="modalCard">
          <div class="flex items-center justify-between border-b pb-3" :class="modalBorder">
            <div>
              <h3 class="text-lg font-black" :class="modalTitle">Isi Evaluasi Editor — Cycle {{ activeRound }}</h3>
              <p class="text-xs" :class="modalSub">Pilih editor dan isi evaluasi.</p>
            </div>
            <button @click="showEvalModal = false" class="cursor-pointer transition" :class="modalClose"><span class="material-symbols-outlined">close</span></button>
          </div>
          <form class="mt-4 grid gap-4" @submit.prevent="submitEvaluation">
            <label class="field" :class="modalLabel">
              <span>Evaluasi Untuk Editor *</span>
              <select v-model="evalForm.editor_id" required class="input" :class="modalInput">
                <option value="">— Pilih Editor —</option>
                <option v-for="a in editorAssignments" :key="a.editor_id" :value="a.editor_id">
                  {{ a.editor?.name }} ({{ a.role === 'primary' ? '★ Primary' : 'Co-Editor' }})
                </option>
              </select>
            </label>
            <label class="field" :class="modalLabel">
              <span>Rekomendasi *</span>
              <select v-model="evalForm.recommendation" required class="input" :class="modalInput">
                <option value="accept">Accept (Terima)</option>
                <option value="revision">Revision (Perlu Revisi)</option>
                <option value="reject">Reject (Tolak)</option>
              </select>
            </label>
            <label class="field" :class="modalLabel">
              <span>Catatan Evaluasi *</span>
              <textarea v-model="evalForm.notes" required class="input min-h-[100px]" :class="modalInput" placeholder="Masukan & catatan..."></textarea>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span class="text-[11px] font-bold flex items-center gap-1 w-full" :class="modalSub">
                  <span class="material-symbols-outlined text-[14px] text-amber-400">lightbulb</span> Catatan Cepat:
                </span>
                <button v-for="(h, i) in activeEvalHintsList" :key="i" type="button" @click="evalForm.notes = h"
                  class="text-[11px] font-medium rounded-lg px-2.5 py-1 border transition cursor-pointer hover:bg-accent hover:text-white text-left"
                  :class="modalChip">{{ h }}</button>
              </div>
            </label>
            <div v-if="evalMsg" class="text-sm font-bold" :class="evalSuccess ? 'text-green-500' : 'text-red-500'">{{ evalMsg }}</div>
            <div class="flex justify-end gap-2 border-t pt-4" :class="modalBorder">
              <button type="button" @click="showEvalModal = false" class="rounded-xl border px-4 py-2 text-sm font-bold cursor-pointer transition" :class="modalBtnCancel">Batal</button>
              <button type="submit" :disabled="evalSaving" class="rounded-xl bg-purple-500 px-5 py-2 text-sm font-black text-white flex items-center gap-2 cursor-pointer hover:opacity-90">
                <span v-if="evalSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                Simpan Evaluasi
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- MODAL: Final Decision -->
    <Teleport to="body">
      <div v-if="showDecisionModal" :data-theme="currentTheme" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/75 backdrop-blur-md">
        <div class="w-full max-w-lg rounded-2xl p-6 shadow-2xl transition-colors duration-300 max-h-[90vh] overflow-y-auto" :class="modalCard">
          <div class="flex items-center justify-between border-b pb-3" :class="modalBorder">
            <div>
              <h3 class="text-lg font-black" :class="modalTitle">Keputusan Final — Cycle {{ activeRound }}</h3>
              <p class="text-xs" :class="modalSub">Tetapkan keputusan editorial final untuk round ini.</p>
            </div>
            <button @click="showDecisionModal = false" class="cursor-pointer transition" :class="modalClose"><span class="material-symbols-outlined">close</span></button>
          </div>
          <form class="mt-4 grid gap-4" @submit.prevent="submitDecision">
            <label class="field" :class="modalLabel">
              <span>Keputusan Status *</span>
              <select v-model="decisionForm.decision" required class="input" :class="modalInput">
                <option value="revision_required">Revision Required (Minta Revisi)</option>
                <option value="accepted">Accepted (Diterima)</option>
                <option value="rejected">Rejected (Ditolak)</option>
              </select>
            </label>
            <label class="field" :class="modalLabel">
              <span>Catatan untuk Author *</span>
              <textarea v-model="decisionForm.notes" required class="input min-h-[100px]" :class="modalInput" placeholder="Instruksi atau catatan keputusan..."></textarea>
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span class="text-[11px] font-bold flex items-center gap-1 w-full" :class="modalSub">
                  <span class="material-symbols-outlined text-[14px] text-amber-400">lightbulb</span> Catatan Cepat:
                </span>
                <button v-for="(h, i) in activeDecisionHintsList" :key="i" type="button" @click="decisionForm.notes = h"
                  class="text-[11px] font-medium rounded-lg px-2.5 py-1 border transition cursor-pointer hover:bg-amber-500 hover:text-black text-left"
                  :class="modalChip">{{ h }}</button>
              </div>
            </label>
            <div v-if="decisionMsg" class="text-sm font-bold" :class="decisionSuccess ? 'text-green-500' : 'text-red-500'">{{ decisionMsg }}</div>
            <div class="flex justify-end gap-2 border-t pt-4" :class="modalBorder">
              <button type="button" @click="showDecisionModal = false" class="rounded-xl border px-4 py-2 text-sm font-bold cursor-pointer transition" :class="modalBtnCancel">Batal</button>
              <button type="submit" :disabled="decisionSaving" class="rounded-xl bg-amber-500 px-5 py-2 text-sm font-black text-black flex items-center gap-2 cursor-pointer hover:opacity-90">
                <span v-if="decisionSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                <span class="material-symbols-outlined text-[18px]">gavel</span>
                Tetapkan Keputusan
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import { readThemePreference, resolveThemePreference } from '../../../composables/useThemePreference'

const route = useRoute()
const loading = ref(false)
const submission = ref(null)
const cycles = ref([])
const editorAssignments = ref([])

// Theme
const currentTheme = ref('dark')
let themeObserver = null
function syncTheme() {
  const root = document.querySelector('.admin-root')
  currentTheme.value = root ? (root.getAttribute('data-theme') || 'dark') : resolveThemePreference(readThemePreference())
}

// Modal theme classes
const modalCard = computed(() => currentTheme.value === 'light' ? 'bg-white text-slate-800 border border-slate-200' : 'bg-[#141d38] text-slate-100 border border-[#1c2850]')
const modalBorder = computed(() => currentTheme.value === 'light' ? 'border-slate-200' : 'border-[#1c2850]')
const modalTitle = computed(() => currentTheme.value === 'light' ? 'text-slate-900' : 'text-white')
const modalSub = computed(() => currentTheme.value === 'light' ? 'text-slate-500' : 'text-slate-400')
const modalClose = computed(() => currentTheme.value === 'light' ? 'text-slate-400 hover:text-slate-800' : 'text-slate-400 hover:text-white')
const modalLabel = computed(() => currentTheme.value === 'light' ? 'text-slate-700' : 'text-slate-200')
const modalInput = computed(() => currentTheme.value === 'light' ? 'bg-white text-slate-900 border-slate-300' : 'bg-[#0f172a] text-white border-[#1c2850]')
const modalChip = computed(() => currentTheme.value === 'light' ? 'bg-slate-100 border-slate-200 text-slate-700' : 'bg-[#0f172a] border-[#1c2850] text-slate-300')
const modalBtnCancel = computed(() => currentTheme.value === 'light' ? 'bg-slate-100 border-slate-300 text-slate-700 hover:bg-slate-200' : 'bg-[#0f172a] border-[#1c2850] text-slate-300 hover:bg-slate-800')

// Hint maps
const evalHintsMap = {
  accept: ['Naskah sudah baik & sesuai standar penerbitan. Siap ke tahap layout.', 'Kualitas penulisan sangat baik. Layak diterima tanpa revisi.'],
  revision: ['Mohon perbaiki sistematika penulisan, daftar pustaka, dan tata bahasa PUEBI.', 'Perlu penambahan referensi ilmiah terbaru serta perapihan struktur bab.', 'Format penulisan belum sesuai template. Mohon sesuaikan margin & font.'],
  reject: ['Isi naskah kurang sesuai dengan cakupan penerbitan.', 'Kualitas penulisan belum memenuhi standar minimal penerbitan.'],
}
const decisionHintsMap = {
  accepted: ['Selamat! Naskah DITERIMA dan dilanjutkan ke proses tata letak & penerbitan.', 'Naskah memenuhi seluruh kriteria penerbitan. Proses cetak segera dimulai.'],
  revision_required: ['Naskah memerlukan REVISI. Mohon perbaiki sesuai catatan editor dan unggah kembali.', 'Silakan revisi bagian yang ditandai oleh editor dan kirim ulang naskah.', 'Beberapa bagian perlu penyesuaian. Cermati catatan evaluasi editor.'],
  rejected: ['Mohon maaf, naskah belum memenuhi kriteria penerbitan Dalwa Press.', 'Naskah dinyatakan TIDAK LAYAK TERBIT. Silakan ajukan naskah baru.'],
}
const revisionHints = [
  'Naskah revisi terbaru hasil perbaikan struktur & tata bahasa PUEBI.',
  'Perbaikan daftar pustaka dan penyesuaian format sesuai masukan editor.',
  'Naskah revisi final siap untuk proses tata letak (layout).',
]

// Revision Modal
const showRevisionModal = ref(false)
const revisionFileInput = ref(null)
const revisionForm = ref({ note: '' })
const revisionSaving = ref(false)
const revisionMsg = ref('')
const revisionSuccess = ref(false)
const activeRound = ref(1)

// Eval Modal
const showEvalModal = ref(false)
const evalForm = ref({ editor_id: '', recommendation: 'revision', notes: '' })
const evalSaving = ref(false)
const evalMsg = ref('')
const evalSuccess = ref(false)

// Decision Modal
const showDecisionModal = ref(false)
const decisionForm = ref({ decision: 'revision_required', notes: '' })
const decisionSaving = ref(false)
const decisionMsg = ref('')
const decisionSuccess = ref(false)

const activeEvalHintsList = computed(() => evalHintsMap[evalForm.value.recommendation] || evalHintsMap.revision)
const activeDecisionHintsList = computed(() => decisionHintsMap[decisionForm.value.decision] || decisionHintsMap.revision_required)

async function loadCycles() {
  loading.value = true
  try {
    const { data } = await api.get(`/submissions/${route.params.id}/review-cycles`)
    submission.value = data.submission
    cycles.value = data.cycles || []
    editorAssignments.value = data.editor_assignments || []
  } catch (err) {
    console.error('Failed to load review cycles', err)
  } finally {
    loading.value = false
  }
}

function openRevisionModal(round) {
  activeRound.value = round
  revisionForm.value = { note: '' }
  revisionMsg.value = ''
  showRevisionModal.value = true
}

function openEvalModal(round) {
  activeRound.value = round
  evalForm.value = { editor_id: '', recommendation: 'revision', notes: '' }
  evalMsg.value = ''
  showEvalModal.value = true
}

function openDecisionModal(round) {
  activeRound.value = round
  const existingDecision = cycles.value.find(c => c.round === round)?.decision
  decisionForm.value = {
    decision: existingDecision?.decision || 'revision_required',
    notes: existingDecision?.notes || '',
  }
  decisionMsg.value = ''
  showDecisionModal.value = true
}

async function submitRevision() {
  const file = revisionFileInput.value?.files?.[0]
  if (!file) { revisionSuccess.value = false; revisionMsg.value = 'Pilih berkas revisi.'; return }
  revisionSaving.value = true; revisionMsg.value = ''
  const fd = new FormData()
  fd.append('revision_file', file)
  if (revisionForm.value.note) fd.append('note', revisionForm.value.note)
  try {
    await api.post(`/submissions/${route.params.id}/upload-revision`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    revisionSuccess.value = true; revisionMsg.value = 'Berkas revisi berhasil diunggah!'
    setTimeout(() => { showRevisionModal.value = false; loadCycles() }, 1200)
  } catch (err) { revisionSuccess.value = false; revisionMsg.value = err.response?.data?.message || 'Gagal mengunggah.' }
  finally { revisionSaving.value = false }
}

async function submitEvaluation() {
  evalSaving.value = true; evalMsg.value = ''
  try {
    await api.post(`/submissions/${route.params.id}/evaluations`, evalForm.value)
    evalSuccess.value = true; evalMsg.value = 'Evaluasi berhasil disimpan!'
    setTimeout(() => { showEvalModal.value = false; loadCycles() }, 1200)
  } catch (err) { evalSuccess.value = false; evalMsg.value = err.response?.data?.message || 'Gagal menyimpan evaluasi.' }
  finally { evalSaving.value = false }
}

async function submitDecision() {
  decisionSaving.value = true; decisionMsg.value = ''
  try {
    await api.post(`/submissions/${route.params.id}/final-decision`, decisionForm.value)
    decisionSuccess.value = true; decisionMsg.value = 'Keputusan berhasil dikirim!'
    setTimeout(() => { showDecisionModal.value = false; loadCycles() }, 1200)
  } catch (err) { decisionSuccess.value = false; decisionMsg.value = err.response?.data?.message || 'Gagal mengirim keputusan.' }
  finally { decisionSaving.value = false }
}

function isEditorPrimary(editorId) {
  return editorAssignments.value.some(a => a.editor_id === editorId && a.role === 'primary')
}

// UI Helpers
function statusLabel(v) { return { submitted: 'Submitted', under_review: 'Under Review', revision: 'Revision', accepted: 'Accepted', rejected: 'Rejected', published: 'Published' }[v] || v }
function statusBadge(v) { const b = 'app-badge'; if (v === 'accepted' || v === 'published') return `${b} app-badge--success`; if (v === 'rejected') return `${b} app-badge--red`; if (v === 'revision') return `${b} app-badge--blue`; return `${b} app-badge--sky` }
function cycleStatusLabel(s) { return { open: 'Sedang Berjalan', revision_required: 'Revisi Diminta', accepted: 'Diterima', rejected: 'Ditolak', unknown: 'Selesai' }[s] || s }
function cycleStatusBadge(s) { if (s === 'accepted') return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'; if (s === 'rejected') return 'bg-red-500/20 text-red-400 border border-red-500/30'; if (s === 'revision_required') return 'bg-amber-500/20 text-amber-400 border border-amber-500/30'; return 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }
function cycleDotClass(c) { if (c.status === 'accepted') return 'bg-emerald-500 border-emerald-400'; if (c.status === 'rejected') return 'bg-red-500 border-red-400'; if (c.status === 'revision_required') return 'bg-amber-500 border-amber-400'; return 'bg-blue-500 border-blue-400 animate-pulse' }
function recommendationBadge(r) { const b = 'app-badge font-extrabold uppercase'; if (r === 'accept') return `${b} app-badge--success`; if (r === 'reject') return `${b} app-badge--red`; return `${b} app-badge--blue` }
function decisionBadge(d) { if (d === 'accepted') return 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'; if (d === 'rejected') return 'bg-red-500/20 text-red-400 border border-red-500/30'; return 'bg-amber-500/20 text-amber-400 border border-amber-500/30' }
function decisionLabel(d) { return { revision_required: 'Revision Required', accepted: 'Accepted', rejected: 'Rejected' }[d] || d }
function roleBadgeInline(r) { const role = (r || '').toLowerCase(); if (role.includes('admin')) return 'text-amber-400 font-bold'; if (role.includes('author')) return 'text-blue-400 font-bold'; if (role.includes('editor')) return 'text-purple-400 font-bold'; if (role.includes('layouter')) return 'text-emerald-400 font-bold'; return 'font-bold' }
function formatDateTime(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }

onMounted(async () => {
  syncTheme()
  const root = document.querySelector('.admin-root')
  if (root) { themeObserver = new MutationObserver(() => syncTheme()); themeObserver.observe(root, { attributes: true, attributeFilter: ['data-theme'] }) }
  await loadCycles()
})

onUnmounted(() => { if (themeObserver) { themeObserver.disconnect(); themeObserver = null } })
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.875rem; font-weight: 700; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.file-btn { display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 999px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.5rem 0.8rem; font-weight: 800; text-decoration: none; transition: background 0.2s; }
.file-btn:hover { opacity: 0.85; }
</style>
