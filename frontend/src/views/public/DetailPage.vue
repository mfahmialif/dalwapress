<template>
  <div class="min-h-screen bg-[#0B1120] text-white">
    <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B1120]">
      <div class="flex flex-col items-center gap-4">
        <span class="material-symbols-outlined text-accent text-6xl animate-spin">progress_activity</span>
        <p class="text-white/60 text-lg">Memuat news...</p>
      </div>
    </div>

    <div v-else-if="error" class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B1120] p-5">
      <div class="flex max-w-md flex-col items-center gap-4 text-center">
        <span class="material-symbols-outlined text-red-400 text-6xl">error</span>
        <p class="text-white/80 text-xl font-bold">News Tidak Ditemukan</p>
        <p class="text-white/50">{{ error }}</p>
        <button @click="goBack" class="mt-2 rounded-lg bg-accent px-6 py-2 font-bold text-[#0B1120]">
          Kembali
        </button>
      </div>
    </div>

    <DetailNews v-else-if="detailItem && displayMode === 'article'" :item="detailItem" @close="goBack" />
    <DetailImage v-else-if="detailItem && displayMode === 'image'" :item="detailItem" @close="goBack" />
    <DetailVideo v-else-if="detailItem" :item="detailItem" @close="goBack" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../axios'
import { storageUrl } from '../../utils/asset'
import DetailNews from '../../components/DetailNews.vue'
import DetailImage from '../../components/DetailImage.vue'
import DetailVideo from '../../components/DetailVideo.vue'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const error = ref(null)
const detailItem = ref(null)
const displayMode = ref('article')

function transformItem(raw) {
  if (raw.category === 'Video') displayMode.value = 'video'
  else if (raw.category === 'Gambar') displayMode.value = 'image'
  else displayMode.value = 'article'

  const videoSrc = raw.video_path ? storageUrl(raw.video_path) : null

  return {
    ...raw,
    image: raw.image_path ? storageUrl(raw.image_path) : '/img/default-agenda.png',
    videoSrc,
    videoUrl: videoSrc,
    videoTag: raw.category || 'News',
    time: formatTime(raw.created_at),
    date: formatDate(raw.created_at),
    description: raw.body || '',
  }
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} Menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} Jam lalu`
  if (diff < 172800) return '1 Hari lalu'
  return `${Math.floor(diff / 86400)} Hari lalu`
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return dateStr
  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(d)
}

function goBack() {
  router.push({ name: 'News' })
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/news/${route.params.id}`)
    detailItem.value = transformItem(data.data || data)
  } catch (e) {
    error.value = e.response?.status === 404
      ? 'News tidak ditemukan atau sudah dihapus.'
      : 'Terjadi kesalahan saat memuat news.'
  } finally {
    loading.value = false
  }
})
</script>
