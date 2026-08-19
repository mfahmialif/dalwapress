<template>
  <div class="mt-6">
    <h4 class="mb-3 text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ title }}</h4>

    <div v-if="history.length" class="grid gap-3">
      <div
        v-for="item in history"
        :key="item.id"
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl border p-3.5"
        style="border-color: var(--border); background: var(--bg-input)"
      >
        <div class="flex items-start gap-3">
          <span class="material-symbols-outlined mt-0.5 text-2xl" :class="historyIconClass(item)">{{ historyIcon(item) }}</span>
          <div>
            <span class="text-sm font-black" :class="item.is_layouter ? 'text-emerald-400' : (item.is_cover ? 'text-amber-400' : '')" :style="item.is_layouter || item.is_cover ? '' : 'color: var(--text-heading)'">
              {{ item.title }}
            </span>
            <p class="mt-0.5 text-xs opacity-75" style="color: var(--text-muted)">
              Diunggah oleh: <strong style="color: var(--text-heading)">{{ item.user_name }}</strong> · {{ formatDateTime(item.created_at) }}
            </p>
            <p v-if="item.note" class="mt-1.5 whitespace-pre-line text-xs" style="color: var(--text-body)">Catatan: {{ item.note }}</p>
          </div>
        </div>

        <a :href="storageUrl(item.file_path)" target="_blank" class="file-btn shrink-0 text-xs">
          <span class="material-symbols-outlined text-[16px]">download</span>
          Download File
        </a>
      </div>
    </div>
    <p v-else class="text-xs" style="color: var(--text-muted)">Belum ada riwayat berkas yang tersimpan.</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { storageUrl } from '../../utils/asset'

const props = defineProps({
  submission: { type: Object, required: true },
  title: { type: String, default: 'Riwayat Berkas Revisi & Manuscript (Terlacak)' },
})

function asArray(value) {
  if (Array.isArray(value)) return value
  if (value && typeof value === 'object') return Object.values(value)
  return []
}

function includes(value, ...needles) {
  const text = String(value || '').toLowerCase()
  return needles.some((needle) => text.includes(needle))
}

function isCover(revision) {
  return includes(revision?.user?.role?.name, 'cover', 'sampul') ||
    includes(revision?.user?.name, 'cover', 'sampul') ||
    includes(revision?.note, 'cover', 'sampul')
}

function isLayouter(revision) {
  return !isCover(revision) && (
    includes(revision?.user?.role?.name, 'layouter') ||
    includes(revision?.user?.name, 'layouter') ||
    includes(revision?.note, 'layout')
  )
}

const history = computed(() => {
  const submission = props.submission || {}
  const result = []
  const initialDate = submission.submitted_at || submission.created_at

  if (submission.manuscript_file) {
    result.push({
      id: 'main-manuscript',
      title: 'Manuscript Naskah Awal',
      type: 'manuscript',
      file_path: submission.manuscript_file,
      user_name: submission.author_name || 'Author',
      note: 'Berkas naskah utama yang diunggah saat pengajuan pertama.',
      created_at: initialDate,
    })
  }

  if (submission.cover_file) {
    result.push({
      id: 'main-cover',
      title: 'Desain Sampul / Cover Naskah',
      type: 'cover',
      is_cover: true,
      file_path: submission.cover_file,
      user_name: submission.author_name || 'Author',
      note: 'File sampul / cover awal naskah.',
      created_at: initialDate,
    })
  }

  let layouterIndex = 0
  let coverIndex = 0
  let revisionIndex = 0

  asArray(submission.revisions).forEach((revision) => {
    if (!revision?.revision_file) return

    const cover = isCover(revision)
    const layouter = !cover && isLayouter(revision)
    let title
    let type = 'revision'

    if (cover) {
      coverIndex += 1
      type = 'cover'
      title = coverIndex > 1 ? `Berkas Desain Sampul #${coverIndex}` : 'Berkas Desain Sampul'
    } else if (layouter) {
      layouterIndex += 1
      type = 'layouter'
      title = layouterIndex > 1 ? `Berkas Naskah Layouter #${layouterIndex}` : 'Berkas Naskah Layouter'
    } else {
      revisionIndex += 1
      title = `Berkas Revisi Naskah #${revisionIndex}`
    }

    result.push({
      id: `revision-${revision.id}`,
      title,
      type,
      is_layouter: layouter,
      is_cover: cover,
      file_path: revision.revision_file,
      user_name: revision.user?.name || 'User',
      note: revision.note || '-',
      created_at: revision.created_at,
    })
  })

  asArray(submission.manuscript_versions).forEach((version) => {
    if (!version?.file_path || result.some((item) => item.file_path === version.file_path)) return
    result.push({
      id: `manuscript-${version.id}`,
      title: `Manuscript Version v${version.version_number}`,
      type: 'manuscript_version',
      file_path: version.file_path,
      user_name: version.user?.name || 'User',
      note: version.note || '-',
      created_at: version.created_at,
    })
  })

  return result.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
})

function historyIcon(item) {
  if (item.is_layouter) return 'design_services'
  if (item.is_cover) return 'image'
  return 'description'
}

function historyIconClass(item) {
  if (item.is_layouter) return 'text-emerald-400'
  if (item.is_cover) return 'text-amber-400'
  return 'text-accent'
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
</script>

<style scoped>
.file-btn { display: inline-flex; align-items: center; gap: .5rem; border-radius: 999px; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: .55rem .85rem; font-weight: 800; text-decoration: none; transition: background .2s; }
.file-btn:hover { background: var(--hover-nav-bg); color: var(--color-accent); }
</style>
