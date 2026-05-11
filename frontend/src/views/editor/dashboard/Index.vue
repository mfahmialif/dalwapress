<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">Dashboard Editor</h2>
      <p class="text-sm" style="color: var(--text-muted)">Ringkasan naskah yang ditugaskan kepada Anda.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
      <article v-for="item in cards" :key="item.label" class="stat-card rounded-2xl border p-5" style="border-color: var(--border)">
        <span class="material-symbols-outlined text-accent">{{ item.icon }}</span>
        <p class="mt-4 text-sm font-bold" style="color: var(--text-muted)">{{ item.label }}</p>
        <strong class="mt-1 block text-3xl font-black" style="color: var(--text-heading)">{{ item.value }}</strong>
      </article>
    </div>
    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-black" style="color: var(--text-heading)">Submission Terbaru</h3>
        <router-link to="/editor/submissions" class="rounded-lg px-3 py-2 text-sm font-bold text-accent hover:bg-white/5">Lihat Semua</router-link>
      </div>
      <div class="mt-4 grid gap-3">
        <router-link v-for="item in latest" :key="item.id" :to="`/editor/submissions/${item.id}`" class="rounded-xl p-4 transition hover:bg-white/5" style="background: var(--bg-input); border: 1px solid var(--border)">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="font-black" style="color: var(--text-heading)">{{ item.title }}</p>
              <p class="text-sm" style="color: var(--text-muted)">{{ item.author_name }} · {{ item.category?.name || '-' }}</p>
            </div>
            <span :class="statusBadge(item.status)">{{ statusLabel(item.status) }}</span>
          </div>
        </router-link>
        <p v-if="!latest.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada assignment.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../../axios'

const data = ref({ stats: {}, latest_submissions: [] })
const latest = computed(() => data.value.latest_submissions || [])
const cards = computed(() => [
  { icon: 'assignment', label: 'Total', value: data.value.stats?.total || 0 },
  { icon: 'inbox', label: 'Submitted', value: data.value.stats?.submitted || 0 },
  { icon: 'rate_review', label: 'Review', value: data.value.stats?.under_review || 0 },
  { icon: 'edit_note', label: 'Revision', value: data.value.stats?.revision || 0 },
  { icon: 'task_alt', label: 'Accepted', value: data.value.stats?.accepted || 0 },
  { icon: 'cancel', label: 'Rejected', value: data.value.stats?.rejected || 0 },
])
function statusLabel(value) { return { submitted: 'Submitted', under_review: 'Under Review', revision: 'Revision', accepted: 'Accepted', rejected: 'Rejected', published: 'Published' }[value] || value }
function statusBadge(value) {
  const base = 'inline-flex rounded-full px-3 py-1 text-xs font-black'
  if (value === 'accepted' || value === 'published') return `${base} bg-green-500/10 text-green-400 border border-green-500/30`
  if (value === 'rejected') return `${base} bg-red-500/10 text-red-400 border border-red-500/30`
  if (value === 'revision') return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`
  return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
}
onMounted(async () => { data.value = (await api.get('/editor/dashboard')).data })
</script>
