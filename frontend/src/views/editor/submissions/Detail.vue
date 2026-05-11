<template>
  <div class="flex flex-col gap-6">
    <router-link to="/editor/submissions" class="inline-flex items-center gap-1 text-sm font-bold" style="color: var(--text-muted)"><span class="material-symbols-outlined text-[20px]">arrow_back</span>Kembali</router-link>
    <div v-if="loading" class="flex justify-center py-16"><span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span></div>
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
            <Info label="Assignment" :value="myAssignment?.role === 'primary' ? 'Primary Editor' : 'Co-editor'" />
          </div>
          <div class="mt-6"><h3 class="font-black" style="color: var(--text-heading)">Deskripsi</h3><p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.description || '-' }}</p></div>
          <div class="mt-6"><h3 class="font-black" style="color: var(--text-heading)">Catatan Pengirim</h3><p class="mt-2 whitespace-pre-line text-sm leading-7" style="color: var(--text-body)">{{ submission.note || '-' }}</p></div>
          <div class="mt-6 flex flex-wrap gap-3">
            <a v-if="submission.manuscript_file" :href="storageUrl(submission.manuscript_file)" target="_blank" class="file-btn"><span class="material-symbols-outlined">description</span>Manuscript</a>
            <a v-if="submission.cover_file" :href="storageUrl(submission.cover_file)" target="_blank" class="file-btn"><span class="material-symbols-outlined">image</span>Cover</a>
          </div>
        </article>
        <aside class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
          <h2 class="text-lg font-black" style="color: var(--text-heading)">Keputusan Editor</h2>
          <form class="mt-5 grid gap-4" @submit.prevent="sendReview">
            <label class="field"><span>Keputusan *</span><select v-model="review.status" required class="input"><option value="revision">Revision</option><option value="accepted">Accepted</option><option value="rejected">Rejected</option></select></label>
            <label class="field"><span>Catatan</span><textarea v-model="review.note" class="input min-h-[160px]"></textarea></label>
            <button class="rounded-lg bg-accent px-5 py-2.5 text-sm font-black" style="color: var(--text-btn)">Simpan Review</button>
          </form>
        </aside>
      </div>
      <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
        <h2 class="text-lg font-black" style="color: var(--text-heading)">Riwayat Review</h2>
        <div class="mt-5 grid gap-4">
          <div v-for="item in submission.reviews" :key="item.id" class="rounded-xl border p-4" style="border-color: var(--border); background: var(--bg-input)">
            <div class="flex flex-wrap items-center justify-between gap-3"><p class="font-black" style="color: var(--text-heading)">{{ item.reviewer_name || item.editor?.name || 'Reviewer' }}</p><span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span></div>
            <p class="mt-1 text-xs" style="color: var(--text-muted)">{{ item.reviewer_email || item.editor?.email || '-' }} · {{ formatDate(item.created_at) }}</p>
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
import api from '../../../axios'
import { useAuthStore } from '../../../stores/auth'
import { storageUrl } from '../../../utils/asset'
const Info = defineComponent({ props:{label:String,value:String}, setup(props){ return () => h('div',{class:'rounded-xl border p-4',style:'border-color: var(--border); background: var(--bg-input)'},[h('p',{class:'text-xs font-bold uppercase tracking-wider',style:'color: var(--text-muted)'},props.label),h('p',{class:'mt-1 text-sm font-black',style:'color: var(--text-heading)'},props.value)]) }})
const route = useRoute(), loading = ref(false), submission = ref(null), review = ref({ status:'revision', note:'' })
const authStore = useAuthStore()
const currentUser = computed(() => authStore.user || {})
const myAssignment = computed(() => submission.value?.editor_assignments?.find((a) => a.editor_id === currentUser.value.id))
async function loadData(){ loading.value = true; submission.value = (await api.get(`/editor/submissions/${route.params.id}`)).data; loading.value = false }
async function sendReview(){ await api.post(`/editor/submissions/${route.params.id}/reviews`, review.value); review.value = { status:'revision', note:'' }; await loadData() }
function statusLabel(value){ return { submitted:'Submitted', under_review:'Under Review', revision:'Revision', accepted:'Accepted', rejected:'Rejected', published:'Published' }[value] || value }
function statusBadge(value){ const base='inline-flex rounded-full px-3 py-1 text-xs font-black'; if(value==='accepted'||value==='published')return `${base} bg-green-500/10 text-green-400 border border-green-500/30`; if(value==='rejected')return `${base} bg-red-500/10 text-red-400 border border-red-500/30`; if(value==='revision')return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`; return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30` }
function formatDate(date){ return date ? new Date(date).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '-' }
onMounted(loadData)
</script>
<style scoped>
.field{display:flex;flex-direction:column;gap:.4rem;color:var(--text-body);font-size:.875rem;font-weight:700}.input{border-radius:.85rem;background:var(--bg-input);border:1px solid var(--border);color:var(--text-heading);padding:.7rem .9rem;outline:none}.file-btn{display:inline-flex;align-items:center;gap:.5rem;border-radius:999px;background:var(--bg-input);border:1px solid var(--border);color:var(--text-heading);padding:.7rem 1rem;font-weight:800}
</style>
