<template>
  <div class="flex flex-col gap-6">
    <router-link to="/administrator/submissions" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)">
      <span class="material-symbols-outlined text-[20px]">arrow_back</span>
      Kembali
    </router-link>

    <div v-if="loading" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <template v-else-if="submission">
      <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <article class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
          <span :class="statusBadge(submission.status)">{{ statusLabel(submission.status) }}</span>
          <h1 class="mt-4 text-2xl font-black" style="color: var(--text-heading)">{{ submission.title }}</h1>
          <p class="mt-2 text-sm" style="color: var(--text-muted)">Oleh {{ submission.author_name }} · {{ submission.category?.name }}</p>
          <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <Info label="Email" :value="submission.email || '-'" />
            <Info label="Phone" :value="submission.phone || '-'" />
            <Info label="Submitted" :value="formatDate(submission.submitted_at)" />
            <Info label="Reviewed" :value="formatDate(submission.reviewed_at)" />
          </div>
          <div class="mt-6">
            <h3 class="font-black" style="color: var(--text-heading)">Deskripsi</h3>
            <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.description || '-' }}</p>
          </div>
          <div class="mt-6">
            <h3 class="font-black" style="color: var(--text-heading)">Catatan Pengirim</h3>
            <p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.note || '-' }}</p>
          </div>
          <div class="mt-6 flex flex-wrap gap-3">
            <a v-if="submission.manuscript_file" :href="storageUrl(submission.manuscript_file)" target="_blank" class="file-btn">
              <span class="material-symbols-outlined">description</span>
              Manuscript
            </a>
            <a v-if="submission.cover_file" :href="storageUrl(submission.cover_file)" target="_blank" class="file-btn">
              <span class="material-symbols-outlined">image</span>
              Cover
            </a>
          </div>
        </article>

        <aside class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
          <h2 class="text-lg font-black" style="color: var(--text-heading)">Tambah Review</h2>
          <form class="mt-5 grid gap-4" @submit.prevent="sendReview">
            <label class="field"><span>Reviewer</span><input v-model="review.reviewer_name" class="input" placeholder="Nama reviewer" /></label>
            <label class="field"><span>Email Reviewer</span><input v-model="review.reviewer_email" type="email" class="input" /></label>
            <label class="field">
              <span>Keputusan *</span>
              <select v-model="review.status" required class="input">
                <option value="revision">Revision</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
              </select>
            </label>
            <label class="field"><span>Catatan</span><textarea v-model="review.note" class="input min-h-[140px]"></textarea></label>
            <button class="rounded-lg bg-accent px-5 py-2.5 text-sm font-black" style="color: var(--text-btn)">Simpan Review</button>
          </form>
        </aside>
      </div>

      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-col gap-1">
          <h2 class="text-lg font-black" style="color: var(--text-heading)">Assignment Editor</h2>
          <p class="text-sm" style="color: var(--text-muted)">Pilih satu primary editor dan beberapa co-editor untuk submission ini.</p>
        </div>
        <form class="mt-5 grid gap-5" @submit.prevent="assignEditors">
          <div class="grid gap-5 xl:grid-cols-2">
            <div class="field assignment-field">
              <span>Primary Editor *</span>
              <VueMultiselect
                v-model="primaryEditorOption"
                :options="editors"
                :close-on-select="true"
                :searchable="true"
                :allow-empty="false"
                :show-labels="false"
                label="name"
                track-by="id"
                placeholder="Cari primary editor"
              >
                <template #option="{ option }">
                  <div class="flex flex-col">
                    <span class="font-bold">{{ option.name }}</span>
                    <span class="text-xs opacity-70">{{ option.username }} · {{ option.email }}</span>
                  </div>
                </template>
                <template #singleLabel="{ option }">
                  <span>{{ option.name }} ({{ option.username }})</span>
                </template>
              </VueMultiselect>
            </div>
            <div class="field assignment-field">
              <span>Co-editor</span>
              <VueMultiselect
                v-model="coEditorOptions"
                :options="availableCoEditors"
                :multiple="true"
                :close-on-select="false"
                :searchable="true"
                :clear-on-select="false"
                :show-labels="false"
                label="name"
                track-by="id"
                placeholder="Cari co-editor"
              >
                <template #option="{ option }">
                  <div class="flex items-start gap-3">
                    <span class="editor-check" :class="{ selected: isCoEditorSelected(option) }">
                      <span v-if="isCoEditorSelected(option)" class="material-symbols-outlined text-[14px]">check</span>
                    </span>
                    <span class="flex flex-col">
                      <span class="font-bold">{{ option.name }}</span>
                      <span class="text-xs opacity-70">{{ option.username }} · {{ option.email }}</span>
                    </span>
                  </div>
                </template>
                <template #tag="{ option, remove }">
                  <span class="editor-tag">
                    {{ option.name }}
                    <button type="button" @click.stop="remove(option)">×</button>
                  </span>
                </template>
              </VueMultiselect>
            </div>
          </div>
          <label class="field">
            <span>Catatan assignment</span>
            <textarea v-model="assignment.note" class="input min-h-[96px]" placeholder="Tambahkan catatan singkat untuk editor jika diperlukan"></textarea>
          </label>
          <div class="flex flex-col gap-3 border-t pt-5 sm:flex-row sm:items-center sm:justify-between" style="border-color: var(--border)">
            <p class="text-sm" style="color: var(--text-muted)">Perubahan assignment akan langsung memperbarui akses editor di portal Editor.</p>
            <button
              :disabled="assignmentSaving"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-black disabled:cursor-not-allowed disabled:opacity-60"
              style="color: var(--text-btn)"
            >
              <span v-if="assignmentSaving" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
              {{ assignmentSaving ? 'Menyimpan...' : 'Simpan Assignment' }}
            </button>
            <span v-if="assignmentMessage" class="text-sm font-bold text-green-400 sm:ml-3">{{ assignmentMessage }}</span>
          </div>
        </form>
        <div class="mt-5 grid gap-3 md:grid-cols-2">
          <div v-for="assignmentItem in submission.editor_assignments" :key="assignmentItem.id" class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
            <p class="font-black" style="color: var(--text-heading)">{{ assignmentItem.editor?.name || '-' }}</p>
            <p class="text-xs" style="color: var(--text-muted)">{{ assignmentItem.role === 'primary' ? 'Primary Editor' : 'Co-editor' }} · {{ assignmentItem.editor?.email || '-' }}</p>
          </div>
          <p v-if="!submission.editor_assignments?.length" class="text-sm" style="color: var(--text-muted)">Belum ada editor yang diassign.</p>
        </div>
      </section>

      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <h2 class="text-lg font-black" style="color: var(--text-heading)">Riwayat Review</h2>
        <div class="mt-5 grid gap-4">
          <div v-for="item in submission.reviews" :key="item.id" class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <p class="font-black" style="color: var(--text-heading)">{{ item.reviewer_name || 'Reviewer' }}</p>
              <span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span>
            </div>
            <p class="mt-1 text-xs" style="color: var(--text-muted)">{{ item.reviewer_email || '-' }} · {{ formatDate(item.created_at) }}</p>
            <p class="mt-3 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ item.note || '-' }}</p>
          </div>
          <p v-if="!submission.reviews?.length" class="text-sm" style="color: var(--text-muted)">Belum ada review.</p>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const Info = defineComponent({
  props: { label: String, value: String },
  setup(props) {
    return () => h('div', { class: 'rounded-xl border p-4', style: 'border-color: var(--border); background: var(--bg-input)' }, [
      h('p', { class: 'text-xs font-bold uppercase tracking-wider', style: 'color: var(--text-muted)' }, props.label),
      h('p', { class: 'mt-1 text-sm font-black', style: 'color: var(--text-heading)' }, props.value),
    ])
  },
})

const route = useRoute()
const loading = ref(false)
const submission = ref(null)
const review = ref({ reviewer_name: '', reviewer_email: '', status: 'revision', note: '' })
const editors = ref([])
const assignment = ref({ primary_editor_id: '', co_editor_ids: [], note: '' })
const assignmentSaving = ref(false)
const assignmentMessage = ref('')

const primaryEditorOption = computed({
  get: () => editors.value.find((editor) => editor.id === assignment.value.primary_editor_id) || null,
  set: (editor) => {
    assignment.value.primary_editor_id = editor?.id || ''
    assignment.value.co_editor_ids = assignment.value.co_editor_ids.filter((id) => id !== editor?.id)
  },
})

const coEditorOptions = computed({
  get: () => editors.value.filter((editor) => assignment.value.co_editor_ids.includes(editor.id)),
  set: (selectedEditors) => {
    assignment.value.co_editor_ids = (selectedEditors || []).map((editor) => editor.id)
  },
})

const availableCoEditors = computed(() => editors.value.filter((editor) => editor.id !== assignment.value.primary_editor_id))

function isCoEditorSelected(editor) {
  return assignment.value.co_editor_ids.includes(editor.id)
}

async function loadData() {
  loading.value = true
  const { data } = await api.get(`/submissions/${route.params.id}`)
  submission.value = data
  const primary = data.editor_assignments?.find((item) => item.role === 'primary')
  assignment.value = {
    primary_editor_id: primary?.editor_id || '',
    co_editor_ids: (data.editor_assignments || []).filter((item) => item.role === 'co_editor').map((item) => item.editor_id),
    note: primary?.note || '',
  }
  loading.value = false
}

async function loadEditors() {
  const { data } = await api.get('/editors')
  editors.value = data || []
}

async function assignEditors() {
  assignmentSaving.value = true
  assignmentMessage.value = ''
  try {
    const { data } = await api.post(`/submissions/${route.params.id}/assign-editors`, assignment.value)
    submission.value = data
    const primary = data.editor_assignments?.find((item) => item.role === 'primary')
    assignment.value = {
      primary_editor_id: primary?.editor_id || '',
      co_editor_ids: (data.editor_assignments || []).filter((item) => item.role === 'co_editor').map((item) => item.editor_id),
      note: primary?.note || '',
    }
    assignmentMessage.value = 'Assignment berhasil disimpan.'
    setTimeout(() => {
      assignmentMessage.value = ''
    }, 2500)
  } finally {
    assignmentSaving.value = false
  }
}

async function sendReview() {
  await api.post(`/submissions/${route.params.id}/reviews`, review.value)
  review.value = { reviewer_name: '', reviewer_email: '', status: 'revision', note: '' }
  await loadData()
}

function statusLabel(value) {
  return { submitted: 'Submitted', under_review: 'Under Review', revision: 'Revision', accepted: 'Accepted', rejected: 'Rejected', published: 'Published' }[value] || value
}

function statusBadge(value) {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-black'
  if (value === 'accepted' || value === 'published') return `${base} bg-green-500/10 text-green-400 border border-green-500/30`
  if (value === 'rejected') return `${base} bg-red-500/10 text-red-400 border border-red-500/30`
  if (value === 'revision') return `${base} bg-yellow-500/10 text-yellow-400 border border-yellow-500/30`
  return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  await Promise.all([loadData(), loadEditors()])
})
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.4rem; color: var(--text-body); font-size: 0.875rem; font-weight: 700; }
.assignment-field { min-width: 0; }
.assignment-field :deep(.multiselect) { min-height: 44px; }
.assignment-field :deep(.multiselect__tags) { min-height: 44px; padding-top: 8px; }
.assignment-field :deep(.multiselect__content-wrapper) { z-index: 80; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 0.9rem; outline: none; }
.file-btn { display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 999px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.7rem 1rem; font-weight: 800; }
.editor-tag { display: inline-flex; align-items: center; gap: 0.35rem; margin: 0 0.25rem 0.25rem 0; border-radius: 999px; background: rgba(251, 191, 36, 0.16); color: var(--color-accent); padding: 0.25rem 0.55rem; font-size: 0.75rem; font-weight: 900; }
.editor-tag button { color: inherit; font-weight: 900; line-height: 1; }
.editor-check { display: inline-flex; align-items: center; justify-content: center; width: 1.1rem; height: 1.1rem; margin-top: 0.1rem; border-radius: 0.3rem; border: 1px solid var(--border-light); color: var(--text-btn); background: var(--bg-card); flex-shrink: 0; }
.editor-check.selected { border-color: var(--color-accent); background: var(--color-accent); }
</style>
