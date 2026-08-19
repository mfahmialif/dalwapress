<template>
  <section class="rounded-2xl p-4 md:p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
    <h3 class="mb-4 text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Alur Status Submission</h3>

    <div class="relative flex items-center justify-between">
      <div v-for="(step, index) in steps" :key="step.key" class="flex min-w-0 flex-1 items-center last:flex-initial">
        <div class="relative z-10 flex w-full shrink-0 flex-col items-center text-center sm:w-auto">
          <div
            class="flex h-9 w-9 items-center justify-center rounded-full border-2 text-xs font-black shadow-md transition-all duration-300 sm:h-10 sm:w-10 sm:text-sm md:h-12 md:w-12 md:text-base"
            :class="stepCircleClass(step.state)"
          >
            <span v-if="step.state === 'completed'" class="material-symbols-outlined text-[18px] sm:text-[20px] md:text-[24px]">check</span>
            <span v-else-if="step.state === 'rejected'" class="material-symbols-outlined text-[18px] sm:text-[20px] md:text-[24px]">close</span>
            <span v-else-if="step.state === 'active'" class="material-symbols-outlined animate-pulse text-[18px] sm:text-[20px] md:text-[24px]">{{ step.icon }}</span>
            <span v-else class="text-xs font-bold">{{ step.number }}</span>
          </div>
          <span class="mt-1.5 text-[11px] font-black tracking-tight sm:text-xs md:text-sm" :class="stepTitleClass(step.state)">{{ step.title }}</span>
          <span class="mt-0.5 hidden text-[11px] font-medium sm:block" style="color: var(--text-muted)">{{ step.subtitle }}</span>
        </div>

        <div
          v-if="index < steps.length - 1"
          class="mx-1 h-1 flex-1 self-center rounded-full transition-all sm:mx-2 md:mx-4"
          :class="connectorLineClass(step.state, steps[index + 1].state)"
        ></div>
      </div>
    </div>

    <div v-if="hasLayouterFile || hasCoverFile" class="mt-4 flex flex-wrap items-center gap-2 border-t pt-3 text-xs font-bold" style="border-color: var(--border)">
      <span class="material-symbols-outlined text-[16px] text-emerald-400">check_circle</span>
      <span class="text-emerald-400">
        {{ hasLayouterFile && hasCoverFile ? 'Hasil layout dan desain sampul sudah tersedia.' : hasLayouterFile ? 'Hasil layout sudah tersedia.' : 'Hasil desain sampul sudah tersedia.' }}
      </span>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  submission: { type: Object, required: true },
})

function asArray(value) {
  if (Array.isArray(value)) return value
  if (value && typeof value === 'object') return Object.values(value)
  return []
}

const revisions = computed(() => asArray(props.submission?.revisions))

function revisionHas(text, ...needles) {
  const value = String(text || '').toLowerCase()
  return needles.some((needle) => value.includes(needle))
}

function isCoverRevision(revision) {
  return revisionHas(
    revision?.user?.role?.name,
    'cover',
    'sampul',
  ) || revisionHas(
    revision?.user?.name,
    'cover',
    'sampul',
  ) || revisionHas(revision?.note, 'cover', 'sampul')
}

function isLayouterRevision(revision) {
  return !isCoverRevision(revision) && (
    revisionHas(revision?.user?.role?.name, 'layouter') ||
    revisionHas(revision?.user?.name, 'layouter') ||
    revisionHas(revision?.note, 'layout')
  )
}

const hasLayouterFile = computed(() => revisions.value.some(isLayouterRevision))
const hasCoverFile = computed(() => revisions.value.some(isCoverRevision))

const steps = computed(() => {
  const status = props.submission?.status

  const review = {
    number: 2,
    key: 'review',
    title: 'Review',
    subtitle: 'Menunggu Review',
    state: 'pending',
    icon: 'rate_review',
  }

  if (status === 'rejected') {
    review.state = 'rejected'
    review.subtitle = 'Ditolak Editor'
    review.icon = 'close'
  } else if (status === 'submitted') {
    review.state = 'active'
    review.subtitle = 'Draft Masuk'
  } else if (status === 'under_review' || status === 'revision') {
    review.state = 'active'
    review.subtitle = status === 'revision' ? 'Perlu Revisi' : 'Proses Review'
  } else if (status === 'accepted' || status === 'published') {
    review.state = 'completed'
    review.subtitle = 'Lolos Review'
  }

  const production = {
    number: 3,
    key: 'production',
    title: 'Layout & Sampul',
    subtitle: 'Layout & Sampul',
    state: 'pending',
    icon: 'palette',
  }

  if (status === 'rejected') {
    production.state = 'rejected'
    production.subtitle = 'Dibatalkan'
    production.icon = 'close'
  } else if (status === 'published' || hasLayouterFile.value || hasCoverFile.value) {
    production.state = 'completed'
    production.subtitle = 'Layout & Sampul Selesai'
  } else if (status === 'accepted' || asArray(props.submission?.layouter_assignments).length || asArray(props.submission?.cover_assignments).length) {
    production.state = 'active'
    production.subtitle = 'Proses Layout & Sampul'
  }

  const done = {
    number: 4,
    key: 'done',
    title: 'Done',
    subtitle: 'Belum Terbit',
    state: 'pending',
    icon: 'flag',
  }

  if (status === 'rejected') {
    done.state = 'rejected'
    done.subtitle = 'Gagal Terbit'
    done.icon = 'close'
  } else if (status === 'published') {
    done.state = 'completed'
    done.subtitle = 'Telah Diterbitkan'
    done.icon = 'check_circle'
  } else if (hasLayouterFile.value || production.state === 'completed') {
    done.state = 'active'
    done.subtitle = 'Siap Diterbitkan'
  }

  return [
    { number: 1, key: 'open', title: 'Open', subtitle: 'Naskah Masuk', state: 'completed', icon: 'folder_open' },
    review,
    production,
    done,
  ]
})

function stepCircleClass(state) {
  if (state === 'completed') return 'bg-emerald-500 text-white border-emerald-400 shadow-emerald-500/20'
  if (state === 'rejected') return 'bg-red-500 text-white border-red-400 shadow-red-500/20'
  if (state === 'active') return 'bg-accent text-white border-blue-400 ring-4 ring-accent/20'
  return 'timeline-step-pending'
}

function stepTitleClass(state) {
  if (state === 'completed') return 'text-emerald-400'
  if (state === 'rejected') return 'text-red-400'
  if (state === 'active') return 'text-accent'
  return 'opacity-60'
}

function connectorLineClass(currentState, nextState) {
  if (nextState === 'rejected') return 'bg-red-500/60'
  if (currentState === 'completed') return 'bg-emerald-500'
  if (currentState === 'active') return 'bg-accent/50'
  return 'timeline-connector-pending'
}
</script>

<style scoped>
.timeline-step-pending { background: var(--bg-input); color: var(--text-muted); border-color: var(--border); }
.timeline-connector-pending { background: var(--border); }
</style>
