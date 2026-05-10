<template>
  <div class="relative w-screen h-screen overflow-hidden font-display text-slate-100 selection:bg-accent selection:text-[#0a192f]">
    <!-- ═══════ PATTERN OVERLAY ═══════ -->
    <div class="absolute inset-0 pointer-events-none z-0"
         :style="{ backgroundImage: patternBg, backgroundSize: '30px 30px' }"></div>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="relative z-10 flex flex-col h-screen w-full px-4 py-3 md:px-8 md:py-5 lg:px-10 lg:py-6 max-w-[1920px] mx-auto">

      <!-- ═══════ HEADER ═══════ -->
      <header class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 md:mb-6 pb-3 md:pb-4 border-b border-accent/20 gap-3">
        <div class="flex items-center gap-3 md:gap-4">
          <button @click="goBack"
                  class="flex items-center justify-center size-10 md:size-12 rounded-full bg-[#0a192f]/50 border border-accent/30 text-accent hover:bg-accent hover:text-[#0a192f] transition-all duration-300 shadow-[0_0_10px_rgba(255,215,0,0.1)] hover:shadow-[0_0_20px_rgba(255,215,0,0.4)] cursor-pointer">
            <span class="material-symbols-outlined text-xl! md:text-2xl!">arrow_back</span>
          </button>
          <div class="flex items-center justify-center size-10 md:size-12 rounded-full bg-accent text-[#0a192f] shadow-[0_0_15px_rgba(251,191,36,0.4)]">
            <span class="material-symbols-outlined text-2xl! md:text-3xl!">newspaper</span>
          </div>
          <div>
            <h1 class="text-lg md:text-2xl font-black uppercase tracking-tight text-white leading-none">UII Dalwa Press</h1>
            <span class="text-xs md:text-sm font-bold text-accent uppercase tracking-widest">News</span>
          </div>
        </div>
        <div class="hidden md:flex items-center gap-6">
          <div class="flex items-center gap-3 px-5 py-2.5 rounded-full bg-[#050e1f]/50 border border-accent/30 backdrop-blur-sm">
            <span class="material-symbols-outlined text-accent">calendar_month</span>
            <div class="flex flex-col items-end">
              <span class="text-sm font-semibold text-slate-200 leading-tight">{{ hijriDate }}</span>
              <span class="text-xs text-slate-400 leading-tight">{{ currentDate }}</span>
            </div>
            <div class="w-px h-8 bg-accent/40 mx-1"></div>
            <span class="text-base font-bold text-accent tabular-nums">{{ currentTime }} WIB</span>
          </div>
        </div>
      </header>

      <!-- ═══════ CONTENT ═══════ -->
      <main class="flex-1 flex flex-col gap-5 overflow-hidden">
        <!-- Title + Controls -->
        <div class="flex items-end flex-col sm:flex-row justify-between px-1 md:px-2 gap-3">
          <div class="space-y-0.5 md:space-y-1">
            <h2 class="text-2xl md:text-4xl font-black text-accent tracking-tight drop-shadow-sm">News</h2>
            <p class="text-slate-300 font-medium text-sm md:text-lg">Berita dan artikel terbaru UII Dalwa Press</p>
          </div>
          <!-- ═══ FILTER TOOLBAR ═══ -->
          <div class="flex items-center gap-2 flex-wrap">
            <!-- Search -->
            <div class="relative">
              <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 text-sm!">search</span>
              <input v-model="searchQuery" @input="onSearch" type="text" placeholder="Cari berita..."
                     class="w-44 pl-8 pr-3 py-1.5 rounded-full bg-[#050e1f]/60 border border-white/10 text-xs text-white placeholder-slate-500 outline-none focus:border-accent/50 focus:shadow-[0_0_10px_rgba(251,191,36,0.15)] transition-all" />
            </div>
            <!-- Category Filter -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full p-1">
              <button v-for="tab in filterTabs" :key="tab.value"
                      @click="setCategory(tab.value)"
                      :class="[
                        'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 cursor-pointer',
                        activeCategory === tab.value
                          ? 'bg-accent text-[#0a192f] shadow-[0_0_15px_rgba(251,191,36,0.4)]'
                          : 'text-slate-400 hover:text-white hover:bg-white/5'
                      ]">
                {{ tab.label }}
              </button>
            </div>
            <!-- Sort -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full p-1">
              <button v-for="sort in sortOptions" :key="sort.value"
                      @click="setSort(sort.value)"
                      :class="[
                        'px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-300 cursor-pointer',
                        activeSortBy === sort.value
                          ? 'bg-white/10 text-white border border-white/20'
                          : 'text-slate-500 hover:text-white'
                      ]">
                <span class="flex items-center gap-1">{{ sort.label }}
                  <span v-if="activeSortBy === sort.value" class="material-symbols-outlined text-xs">{{ activeSortDir === 'asc' ? 'arrow_upward' : 'arrow_downward' }}</span>
                </span>
              </button>
            </div>
            <!-- Limit -->
            <div class="flex items-center gap-1 bg-[#050e1f]/60 border border-white/10 rounded-full px-3 py-1">
              <span class="text-xs text-slate-500 font-bold">Tampil:</span>
              <select v-model.number="perPage" @change="currentPage = 1; loadNews()" class="bg-transparent text-white text-xs font-bold outline-none cursor-pointer appearance-none px-1">
                <option v-for="n in [6, 9, 12, 18]" :key="n" :value="n" class="bg-[#0a192f]">{{ n }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- ═══════ NEWS GRID ═══════ -->
        <div v-if="loading" class="flex items-center justify-center flex-1">
          <span class="material-symbols-outlined text-5xl text-accent animate-spin">progress_activity</span>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 portrait:lg:grid-cols-2 gap-4 md:gap-6 flex-1 pb-4 overflow-y-auto">
          <!-- Featured Article (Card 1) -->
          <article v-if="newsItems.length > 0"
                   @click="router.push({ name: 'DetailNews', params: { id: newsItems[0].id } })"
                   class="group relative flex flex-col justify-end overflow-hidden rounded-xl bg-[#050e1f] border border-white/10 hover:border-accent transition-all duration-300 hover:scale-[1.01] hover:shadow-[0_0_20px_rgba(251,191,36,0.2)] cursor-pointer min-h-[200px] md:min-h-0">
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                 :style="{ backgroundImage: `url('${getImageUrl(newsItems[0])}')` }"></div>
            <div class="absolute inset-0 bg-linear-to-t from-[#050e1f] via-[#050e1f]/70 to-transparent opacity-95"></div>
            <div v-if="newsItems[0].category === 'Video'" class="absolute inset-0 flex items-center justify-center z-10">
              <div class="size-16 rounded-full bg-accent/90 flex items-center justify-center shadow-[0_0_30px_rgba(251,191,36,0.5)] group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[#0a192f] text-3xl ml-1">play_arrow</span>
              </div>
            </div>
            <div class="relative z-10 p-6 flex flex-col gap-3">
              <span :class="categoryBadgeClass(newsItems[0].category)">{{ newsItems[0].category }}</span>
              <h3 class="text-2xl font-bold text-white leading-tight line-clamp-2">{{ newsItems[0].title }}</h3>
              <div class="flex items-center gap-2 text-slate-300 text-sm mt-1">
                <span class="material-symbols-outlined text-[18px] text-accent">schedule</span>
                <span>{{ formatTime(newsItems[0].created_at) }}</span>
              </div>
            </div>
          </article>

          <!-- Regular Articles -->
          <article v-for="item in newsItems.slice(1)" :key="item.id"
                   @click="router.push({ name: 'DetailNews', params: { id: item.id } })"
                   class="group relative flex flex-col justify-end overflow-hidden rounded-xl bg-[#050e1f] border border-white/10 hover:border-accent transition-all duration-300 hover:scale-[1.01] hover:shadow-[0_0_20px_rgba(251,191,36,0.2)] cursor-pointer">
            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-110"
                 :style="{ backgroundImage: `url('${getImageUrl(item)}')` }"></div>
            <div class="absolute inset-0 bg-linear-to-t from-[#050e1f] via-[#050e1f]/60 to-transparent opacity-90 group-hover:opacity-95 transition-opacity"></div>
            <div v-if="item.category === 'Video'" class="absolute inset-0 flex items-center justify-center z-10">
              <div class="size-12 rounded-full bg-accent/80 flex items-center justify-center shadow-[0_0_20px_rgba(251,191,36,0.3)] group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined text-[#0a192f] text-2xl ml-0.5">play_arrow</span>
              </div>
            </div>
            <div class="relative z-10 p-6 flex flex-col gap-3">
              <span :class="categoryBadgeClass(item.category)">{{ item.category }}</span>
              <h3 class="text-xl font-bold text-white leading-tight line-clamp-2 opacity-95 group-hover:opacity-100">{{ item.title }}</h3>
              <div class="flex items-center gap-2 text-slate-300 text-sm mt-1">
                <span class="material-symbols-outlined text-[18px]">schedule</span>
                <span>{{ formatTime(item.created_at) }}</span>
              </div>
            </div>
          </article>

          <!-- Empty state -->
          <div v-if="newsItems.length === 0" class="col-span-3 flex flex-col items-center justify-center gap-3 text-slate-500">
            <span class="material-symbols-outlined text-6xl">article</span>
            <p class="text-lg font-medium">Tidak ada konten ditemukan</p>
          </div>
        </div>
      </main>

      <!-- ═══════ PAGINATION FOOTER ═══════ -->
      <footer v-if="totalPages > 1" class="flex items-center justify-center py-4">
        <div class="flex items-center gap-2 bg-[#050e1f]/50 backdrop-blur-sm px-6 py-2 rounded-full border border-accent/20 shadow-[0_0_15px_rgba(0,0,0,0.3)]">
          <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1"
                  class="size-8 flex items-center justify-center rounded-full text-accent hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 cursor-pointer">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <div class="flex items-center gap-2 px-2">
            <button v-for="p in pageNumbers" :key="p"
                    @click="typeof p === 'number' && goToPage(p)"
                    :class="[
                      'rounded-full transition-all duration-300 cursor-pointer',
                      p === currentPage
                        ? 'size-8 bg-accent text-[#0a192f] font-bold text-sm flex items-center justify-center shadow-[0_0_12px_rgba(251,191,36,1)]'
                        : p === '...' ? 'size-8 text-slate-500 text-sm flex items-center justify-center' : 'size-8 bg-white/10 text-white text-sm flex items-center justify-center hover:bg-white/20'
                    ]">{{ p }}</button>
          </div>
          <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages"
                  class="size-8 flex items-center justify-center rounded-full text-accent hover:text-white hover:bg-white/10 transition-colors disabled:opacity-30 cursor-pointer">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
          <span class="text-xs text-slate-500 ml-2">{{ currentPage }}/{{ totalPages }}</span>
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../axios'
import { storageUrl } from '../../utils/asset'

const router = useRouter()

// ── Time & Date ──
const currentDate = ref(''); const currentTime = ref(''); const hijriDate = ref('')

function updateTime() {
  const now = new Date()
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
  const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
  currentDate.value = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`
  currentTime.value = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
  try { hijriDate.value = new Intl.DateTimeFormat('id-u-ca-islamic', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(now) } catch { hijriDate.value = '' }
}

let ti
onMounted(() => { updateTime(); ti = setInterval(updateTime, 1000); loadNews() })
onUnmounted(() => clearInterval(ti))
function goBack() { router.push({ name: 'Landing' }) }

// ── Data ──
const newsItems = ref([]); const loading = ref(false)
const currentPage = ref(1); const totalPages = ref(1); const lastPage = ref(1)
const perPage = ref(6)
const activeCategory = ref('all')
const activeSortBy = ref('created_at'); const activeSortDir = ref('desc')
const searchQuery = ref('')
let searchTimeout = null
function onSearch() { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => { currentPage.value = 1; loadNews() }, 400) }

const filterTabs = [
  { value: 'all', label: 'Semua' },
  { value: 'Artikel', label: 'Artikel' },
  { value: 'Gambar', label: 'Gambar' },
  { value: 'Video', label: 'Video' },
]
const sortOptions = [
  { value: 'created_at', label: 'Waktu' },
  { value: 'category', label: 'Jenis' },
  { value: 'title', label: 'Judul' },
]

function setCategory(val) { activeCategory.value = val; currentPage.value = 1; loadNews() }
function setSort(val) {
  if (activeSortBy.value === val) activeSortDir.value = activeSortDir.value === 'desc' ? 'asc' : 'desc'
  else { activeSortBy.value = val; activeSortDir.value = val === 'created_at' ? 'desc' : 'asc' }
  currentPage.value = 1; loadNews()
}

async function loadNews() {
  loading.value = true
  try {
    const params = { per_page: perPage.value, page: currentPage.value, status: 'Published', sort_by: activeSortBy.value, sort_dir: activeSortDir.value }
    if (activeCategory.value !== 'all') params.category = activeCategory.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    const { data } = await api.get('/news', { params })
    newsItems.value = data.data || []
    totalPages.value = data.last_page || 1
    lastPage.value = data.last_page || 1
  } catch (e) { console.error(e); newsItems.value = [] } finally { loading.value = false }
}

function goToPage(p) { if (p >= 1 && p <= totalPages.value) { currentPage.value = p; loadNews() } }

const pageNumbers = computed(() => {
  const last = totalPages.value; const curr = currentPage.value; const pages = []
  for (let i = 1; i <= last; i++) {
    if (i === 1 || i === last || (i >= curr - 1 && i <= curr + 1)) pages.push(i)
    else if (pages[pages.length - 1] !== '...') pages.push('...')
  }
  return pages
})

function getImageUrl(item) {
  if (item.image_path) return storageUrl(item.image_path)
  return '/img/default-agenda.png'
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr); const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} Menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} Jam lalu`
  if (diff < 172800) return '1 Hari lalu'
  return `${Math.floor(diff / 86400)} Hari lalu`
}



// ── Category Badge ──
function categoryBadgeClass(cat) {
  const base = 'w-fit px-3 py-1 rounded text-xs font-bold uppercase tracking-wider shadow-sm flex items-center gap-1.5'
  if (cat === 'Artikel') return `${base} bg-accent text-[#0a192f]`
  if (cat === 'Gambar') return `${base} bg-blue-500 text-white`
  if (cat === 'Video') return `${base} bg-red-500 text-white`
  return `${base} bg-accent text-[#0a192f]`
}

const patternBg = `radial-gradient(circle at 0% 0%, rgba(251,191,36,0.15) 2px, transparent 2px), radial-gradient(circle at 100% 100%, rgba(251,191,36,0.15) 2px, transparent 2px)`
</script>

<style scoped>
.detail-enter-active { animation: fadeIn 0.3s ease-out; }
.detail-leave-active { animation: fadeOut 0.2s ease-in; }
@keyframes fadeIn { from { opacity: 0; transform: scale(1.02); } to { opacity: 1; transform: scale(1); } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; transform: scale(0.98); } }
</style>
