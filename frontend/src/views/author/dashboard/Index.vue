<template>
  <div class="flex flex-col gap-6">
    <div class="flex flex-col gap-1">
      <h2 class="text-xl font-black" style="color: var(--text-heading)">Dashboard Overview</h2>
      <p class="text-sm" style="color: var(--text-muted)">Ringkasan submission, notifikasi, dan progres naskah terakhir.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
      <article v-for="item in cards" :key="item.label" class="stat-card rounded-2xl border p-5" style="border-color: var(--border)">
        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-sm font-bold" style="color: var(--text-muted)">{{ item.label }}</p>
            <strong class="mt-2 block text-3xl font-black" style="color: var(--text-heading)">{{ item.value }}</strong>
          </div>
          <span class="material-symbols-outlined text-accent text-[30px]">{{ item.icon }}</span>
        </div>
      </article>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
      <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 class="text-lg font-black" style="color: var(--text-heading)">Progress Submission Terakhir</h3>
            <p class="text-sm" style="color: var(--text-muted)">Timeline status naskah terbaru.</p>
          </div>
          <router-link to="/author/submissions" class="rounded-lg px-3 py-2 text-sm font-bold text-accent hover:bg-white/5">Lihat Submission</router-link>
        </div>

        <div v-if="latest" class="mt-5 rounded-2xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <h4 class="font-black" style="color: var(--text-heading)">{{ latest.title }}</h4>
              <p class="text-sm" style="color: var(--text-muted)">{{ latest.category?.name || '-' }}</p>
            </div>
            <span :class="statusBadge(latest.status)">{{ statusLabel(latest.status) }}</span>
          </div>

          <div class="mt-5 grid gap-3">
            <div v-for="step in timeline" :key="step.key" class="flex items-center gap-3">
              <span class="flex size-8 items-center justify-center rounded-full"
                    :class="isStepDone(step.key) ? 'bg-green-500/10 text-green-400 border border-green-500/30' : 'bg-slate-500/10 text-slate-500 border border-slate-500/20'">
                <span class="material-symbols-outlined text-[18px]">{{ isStepDone(step.key) ? 'check' : 'radio_button_unchecked' }}</span>
              </span>
              <span class="text-sm font-bold" style="color: var(--text-body)">{{ step.label }}</span>
            </div>
          </div>
        </div>
        <p v-else class="mt-5 rounded-2xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada submission.</p>
      </section>

      <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
        <h3 class="text-lg font-black" style="color: var(--text-heading)">Notifikasi Terbaru</h3>
        <div class="mt-4 grid gap-3">
          <div v-for="item in notifications" :key="`${item.type}-${item.created_at}`" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
            <p class="font-black" style="color: var(--text-heading)">{{ item.message }}</p>
            <p v-if="item.note" class="mt-1 text-sm" style="color: var(--text-muted)">{{ item.note }}</p>
          </div>
          <p v-if="!notifications.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada notifikasi.</p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../../axios'

const dashboard = ref({ stats: {}, latest_submission: null, notifications: [] })
const latest = computed(() => dashboard.value.latest_submission)
const notifications = computed(() => dashboard.value.notifications || [])
const timeline = [
  { key: 'submitted', label: 'Submitted' },
  { key: 'under_review', label: 'Under Review' },
  { key: 'revision', label: 'Revision' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'published', label: 'Published' },
]
const order = timeline.map((item) => item.key)
const cards = computed(() => [
  { icon: 'description', label: 'Total Naskah', value: dashboard.value.stats?.total_submission || 0 },
  { icon: 'rate_review', label: 'Under Review', value: dashboard.value.stats?.under_review || 0 },
  { icon: 'task_alt', label: 'Accepted', value: dashboard.value.stats?.accepted || 0 },
  { icon: 'library_books', label: 'Published', value: dashboard.value.stats?.published || 0 },
  { icon: 'payments', label: 'Royalti', value: formatCurrency(dashboard.value.stats?.royalty_amount) },
])

function formatCurrency(value) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0))
}

function isStepDone(key) {
  if (!latest.value) return false
  return order.indexOf(key) <= order.indexOf(latest.value.status)
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
  if (value === 'revision') return `${base} bg-blue-500/10 text-blue-400 border border-blue-500/30`
  return `${base} bg-sky-500/10 text-sky-400 border border-sky-500/30`
}

onMounted(async () => {
  const { data } = await api.get('/author/dashboard')
  dashboard.value = data
})
</script>
