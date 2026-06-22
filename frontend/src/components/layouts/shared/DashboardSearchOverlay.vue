<template>
  <Transition name="search-overlay">
    <div v-if="open" class="search-backdrop" @click.self="close">
      <div class="search-panel">
        <div class="search-top">
          <div class="search-icon">
            <span class="material-symbols-outlined">search</span>
          </div>
          <input
            ref="searchInputRef"
            v-model="query"
            class="search-field"
            type="text"
            placeholder="Cari menu, halaman, atau aksi..."
            autocomplete="off"
            @keydown.esc.prevent="close"
            @keydown.enter.prevent="openFirstResult"
          />
          <button class="close-btn" type="button" title="Tutup" @click="close">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <div class="search-body">
          <div class="search-meta">
            <span>{{ filteredItems.length }} hasil</span>
            <span class="hidden sm:inline">Tekan Enter untuk membuka hasil pertama</span>
          </div>

          <div v-if="filteredItems.length" class="result-list">
            <button
              v-for="item in filteredItems"
              :key="item.to"
              class="result-item"
              type="button"
              @click="goTo(item.to)"
            >
              <span class="result-icon material-symbols-outlined">{{ item.icon }}</span>
              <span class="min-w-0">
                <span class="result-title">{{ item.title }}</span>
                <span class="result-desc">{{ item.description }}</span>
              </span>
              <span class="result-arrow material-symbols-outlined">arrow_forward</span>
            </button>
          </div>

          <div v-else class="empty-state">
            <span class="material-symbols-outlined">manage_search</span>
            <p>Tidak ada hasil untuk pencarian ini.</p>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  open: { type: Boolean, default: false },
  portal: { type: String, default: 'admin' },
})

const emit = defineEmits(['close'])
const router = useRouter()
const query = ref('')
const searchInputRef = ref(null)

const searchItems = {
  admin: [
    { title: 'Dashboard', description: 'Ringkasan aktivitas admin', icon: 'dashboard', to: '/administrator/dashboard' },
    { title: 'News', description: 'Kelola berita dan artikel', icon: 'article', to: '/administrator/news' },
    { title: 'Tambah News', description: 'Buat artikel atau berita baru', icon: 'add_circle', to: '/administrator/news/create' },
    { title: 'Kategori News', description: 'Kelola kategori berita', icon: 'category', to: '/administrator/news-categories' },
    { title: 'Books', description: 'Kelola katalog buku', icon: 'menu_book', to: '/administrator/books' },
    { title: 'Kategori Buku', description: 'Kelola kategori buku', icon: 'library_books', to: '/administrator/book-categories' },
    { title: 'Authors', description: 'Kelola data author', icon: 'edit', to: '/administrator/authors' },
    { title: 'Submissions', description: 'Kelola pengajuan naskah', icon: 'assignment', to: '/administrator/submissions' },
    { title: 'Royalti', description: 'Kelola royalti author', icon: 'payments', to: '/administrator/royalties' },
    { title: 'Manajemen User', description: 'Kelola akun pengguna', icon: 'group', to: '/administrator/manajemen-user' },
    { title: 'Manajemen Role', description: 'Kelola role dan akses', icon: 'admin_panel_settings', to: '/administrator/manajemen-role' },
    { title: 'Pengaturan', description: 'Konfigurasi sistem, email, Google Login', icon: 'settings', to: '/administrator/pengaturan' },
    { title: 'Profile', description: 'Profile akun admin', icon: 'person', to: '/administrator/profile' },
  ],
  author: [
    { title: 'Dashboard', description: 'Ringkasan author', icon: 'dashboard', to: '/author/dashboard' },
    { title: 'My Submissions', description: 'Daftar pengajuan naskah', icon: 'assignment', to: '/author/submissions' },
    { title: 'Create Submission', description: 'Kirim naskah baru', icon: 'add_circle', to: '/author/submissions/create' },
    { title: 'My Published Books', description: 'Buku yang sudah terbit', icon: 'menu_book', to: '/author/books' },
    { title: 'Royalti Saya', description: 'Laporan royalti author', icon: 'payments', to: '/author/royalties' },
    { title: 'Notifications', description: 'Notifikasi author', icon: 'notifications', to: '/author/notifications' },
    { title: 'Activity Log', description: 'Riwayat aktivitas', icon: 'history', to: '/author/activity' },
    { title: 'Bookmark Buku', description: 'Daftar bookmark', icon: 'bookmark', to: '/author/bookmarks' },
    { title: 'Profile Author', description: 'Profil akun author', icon: 'person', to: '/author/profile' },
  ],
  editor: [
    { title: 'Dashboard', description: 'Ringkasan editor', icon: 'dashboard', to: '/editor/dashboard' },
    { title: 'Assigned Submissions', description: 'Naskah yang ditugaskan', icon: 'rate_review', to: '/editor/submissions' },
    { title: 'Profile Editor', description: 'Profil akun editor', icon: 'person', to: '/editor/profile' },
  ],
  penulis: [
    { title: 'Dashboard', description: 'Ringkasan penulis', icon: 'dashboard', to: '/penulis/dashboard' },
    { title: 'Artikel Saya', description: 'Kelola artikel milik sendiri', icon: 'article', to: '/penulis/news' },
    { title: 'Tambah Artikel', description: 'Tulis artikel baru', icon: 'add_circle', to: '/penulis/news/create' },
    { title: 'Profile Penulis', description: 'Profil akun penulis', icon: 'person', to: '/penulis/profile' },
  ],
  kepalaPenulis: [
    { title: 'Dashboard', description: 'Ringkasan kepala penulis', icon: 'dashboard', to: '/kepala-penulis/dashboard' },
    { title: 'Artikel Penulis', description: 'Kelola artikel semua penulis', icon: 'article', to: '/kepala-penulis/news' },
    { title: 'Tambah Artikel Penulis', description: 'Buat artikel untuk penulis', icon: 'add_circle', to: '/kepala-penulis/news/create' },
    { title: 'Akun Penulis', description: 'Kelola akun penulis', icon: 'group', to: '/kepala-penulis/writers' },
    { title: 'Profile Kepala Penulis', description: 'Profil akun kepala penulis', icon: 'person', to: '/kepala-penulis/profile' },
  ],
}

const filteredItems = computed(() => {
  const items = searchItems[props.portal] || searchItems.admin
  const keyword = query.value.trim().toLowerCase()
  if (!keyword) return items

  return items.filter((item) => `${item.title} ${item.description}`.toLowerCase().includes(keyword))
})

function close() {
  emit('close')
}

function goTo(to) {
  router.push(to)
  close()
}

function openFirstResult() {
  if (filteredItems.value[0]) {
    goTo(filteredItems.value[0].to)
  }
}

watch(() => props.open, async (value) => {
  if (value) {
    query.value = ''
    await nextTick()
    searchInputRef.value?.focus()
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
.search-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 7vh 1rem 1rem;
  background:
    radial-gradient(circle at 50% 0%, rgba(37, 99, 235, 0.16), transparent 34rem),
    rgba(2, 6, 23, 0.34);
  backdrop-filter: blur(28px) saturate(0.72) brightness(0.82);
  -webkit-backdrop-filter: blur(28px) saturate(0.72) brightness(0.82);
}
.search-panel {
  position: relative;
  isolation: isolate;
  width: min(760px, 100%);
  overflow: hidden;
  border: 1px solid color-mix(in srgb, var(--border, rgba(148, 163, 184, 0.25)) 80%, transparent);
  border-radius: 1.35rem;
  background: color-mix(in srgb, var(--bg-card, #0f172a) 94%, transparent);
  box-shadow: 0 34px 120px rgba(0, 0, 0, 0.38);
}
.search-panel::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: -1;
  background: var(--bg-card, #0f172a);
  opacity: 0.92;
}
.search-top {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 0.85rem;
  border-bottom: 1px solid var(--border, rgba(148, 163, 184, 0.2));
  padding: 1rem;
}
.search-icon,
.close-btn,
.result-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.search-icon,
.close-btn {
  width: 2.6rem;
  height: 2.6rem;
  border-radius: 0.9rem;
  background: var(--bg-input, rgba(255, 255, 255, 0.08));
  color: var(--color-accent, #38bdf8);
}
.close-btn {
  color: var(--text-muted, #94a3b8);
  transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}
.close-btn:hover {
  background: rgba(239, 68, 68, 0.12);
  color: #ef4444;
  transform: rotate(8deg);
}
.search-field {
  min-width: 0;
  background: transparent;
  color: var(--text-heading, #ffffff);
  font-size: clamp(1.15rem, 2.2vw, 1.7rem);
  font-weight: 900;
  outline: none;
}
.search-field::placeholder {
  color: var(--text-muted, #94a3b8);
}
.search-body {
  max-height: min(66vh, 560px);
  overflow: auto;
  padding: 1rem;
}
.search-meta {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 0.85rem;
  color: var(--text-muted, #94a3b8);
  font-size: 0.78rem;
  font-weight: 850;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.result-list {
  display: grid;
  gap: 0.55rem;
}
.result-item {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 0.85rem;
  width: 100%;
  border: 1px solid transparent;
  border-radius: 1rem;
  padding: 0.85rem;
  color: var(--text-body, #cbd5e1);
  text-align: left;
  transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}
.result-item:hover {
  border-color: color-mix(in srgb, var(--color-accent, #38bdf8) 42%, transparent);
  background: var(--hover-nav-bg, rgba(37, 99, 235, 0.12));
  transform: translateY(-1px);
}
.result-icon {
  width: 2.65rem;
  height: 2.65rem;
  border-radius: 0.85rem;
  background: var(--bg-input, rgba(255, 255, 255, 0.08));
  color: var(--color-accent, #38bdf8);
}
.result-title,
.result-desc {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.result-title {
  color: var(--text-heading, #ffffff);
  font-size: 0.96rem;
  font-weight: 950;
}
.result-desc {
  margin-top: 0.15rem;
  color: var(--text-muted, #94a3b8);
  font-size: 0.82rem;
  font-weight: 650;
}
.result-arrow {
  color: var(--text-muted, #94a3b8);
  font-size: 1.15rem;
}
.empty-state {
  display: grid;
  justify-items: center;
  gap: 0.65rem;
  padding: 3rem 1rem;
  color: var(--text-muted, #94a3b8);
  text-align: center;
  font-weight: 800;
}
.empty-state .material-symbols-outlined {
  color: var(--color-accent, #38bdf8);
  font-size: 2.4rem;
}
.search-overlay-enter-active,
.search-overlay-leave-active {
  transition: opacity 0.22s ease;
}
.search-overlay-enter-active .search-panel,
.search-overlay-leave-active .search-panel {
  transition: transform 0.22s ease, opacity 0.22s ease;
}
.search-overlay-enter-from,
.search-overlay-leave-to {
  opacity: 0;
}
.search-overlay-enter-from .search-panel,
.search-overlay-leave-to .search-panel {
  opacity: 0;
  transform: translateY(-18px) scale(0.98);
}
</style>
