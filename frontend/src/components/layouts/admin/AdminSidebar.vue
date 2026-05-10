<template>
  <aside :class="['sidebar flex h-screen flex-col p-4 shrink-0 transition-all duration-300', collapsed ? 'sidebar-collapsed' : '']">
    <div class="flex items-center px-3 pb-4 shrink-0" :class="collapsed ? 'justify-center' : 'justify-between'">
      <div class="overflow-hidden transition-all duration-300" :style="collapsed ? 'width: 0; opacity: 0' : 'width: auto; opacity: 1'">
        <h1 class="text-heading text-lg font-bold leading-normal whitespace-nowrap">UII Dalwa Press</h1>
        <p class="text-muted text-sm font-medium leading-normal whitespace-nowrap">Admin CMS</p>
      </div>
      <span v-if="collapsed" class="material-symbols-outlined text-accent text-[28px]">menu_book</span>
      <button @click="$emit('close-sidebar')" class="close-btn p-1.5 rounded-lg transition-colors cursor-pointer lg:hidden">
        <span class="material-symbols-outlined text-[22px]">close</span>
      </button>
    </div>

    <div class="flex-1 min-h-0">
      <simplebar class="h-full" :auto-hide="true">
        <nav class="flex flex-col gap-1 pr-1">
          <template v-for="section in navSections" :key="section.label">
            <div v-if="section.label" class="section-heading mt-5 mb-1 px-3" :class="collapsed ? 'is-collapsed' : ''">
              <p v-if="!collapsed" class="text-xs font-bold uppercase tracking-wider text-muted">{{ section.label }}</p>
            </div>

            <router-link v-for="item in section.items" :key="item.label"
                         :to="item.route"
                         :class="[
                           'flex items-center gap-4 px-4 py-3 rounded-lg transition-colors sidebar-link',
                           isActiveRoute(item.route)
                             ? 'bg-accent text-btn-text font-bold hover:bg-accent/90'
                             : 'nav-item text-body font-semibold cursor-pointer'
                         ]"
                         :title="collapsed ? item.label : ''">
              <span class="material-symbols-outlined text-[26px] shrink-0">{{ item.icon }}</span>
              <span class="sidebar-label text-base leading-normal">{{ item.label }}</span>
            </router-link>
          </template>
        </nav>
      </simplebar>
    </div>

    <div class="pt-4 shrink-0">
      <button @click="handleLogout"
              class="flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-accent text-btn-text font-bold transition-colors hover:bg-accent/90 w-full shadow-[0_0_15px_rgba(251,191,36,0.3)] cursor-pointer active:scale-95"
              :title="collapsed ? 'Logout' : ''">
        <span class="material-symbols-outlined text-[20px] shrink-0">logout</span>
        <span v-if="!collapsed">Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import { useAuthStore } from '../../../stores/auth'

defineProps({ collapsed: { type: Boolean, default: false } })
defineEmits(['close-sidebar', 'toggle-collapse'])

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const navSections = [
  {
    label: '',
    items: [
      { icon: 'dashboard', label: 'Dashboard', route: '/administrator/dashboard' },
    ],
  },
  {
    label: 'Konten',
    items: [
      { icon: 'menu_book', label: 'Books', route: '/administrator/books' },
      { icon: 'category', label: 'Kategori Buku', route: '/administrator/book-categories' },
      { icon: 'edit_square', label: 'Authors', route: '/administrator/authors' },
      { icon: 'assignment', label: 'Submissions', route: '/administrator/submissions' },
      { icon: 'newspaper', label: 'News', route: '/administrator/news' },
    ],
  },
  {
    label: 'Manajemen',
    items: [
      { icon: 'group', label: 'Manajemen User', route: '/administrator/manajemen-user' },
      { icon: 'admin_panel_settings', label: 'Manajemen Role', route: '/administrator/manajemen-role' },
    ],
  },
  {
    label: 'Website',
    items: [
      { icon: 'person', label: 'Profile', route: '/administrator/profile' },
      { icon: 'settings', label: 'Pengaturan', route: '/administrator/pengaturan' },
    ],
  },
]

function isActiveRoute(itemRoute) {
  return route.path === itemRoute
    || (itemRoute === '/administrator/news' && route.path.startsWith('/administrator/news'))
    || (itemRoute === '/administrator/books' && route.path.startsWith('/administrator/books'))
    || (itemRoute === '/administrator/submissions' && route.path.startsWith('/administrator/submissions'))
}

function handleLogout() {
  authStore.logout()
}
</script>

<style scoped>
.sidebar :deep(.simplebar-scrollbar::before) {
  background: var(--border-light);
  border-radius: 999px;
  opacity: 0;
  transition: opacity 0.4s ease;
}
.sidebar :deep(.simplebar-scrollbar.simplebar-visible::before) { opacity: 0.7; }
.sidebar:hover :deep(.simplebar-scrollbar::before) { opacity: 0.5; }
.sidebar :deep(.simplebar-track.simplebar-vertical) { width: 8px; right: 0; }
.sidebar :deep(.simplebar-track.simplebar-horizontal) { display: none; }

.sidebar { background: var(--bg-sidebar); }
.close-btn { color: var(--text-muted); }
.close-btn:hover { background: var(--bg-input); color: var(--color-accent); }
.text-heading { color: var(--text-heading); }
.text-body { color: var(--text-body); }
.text-muted { color: var(--text-muted); }
.text-btn-text { color: var(--text-btn); }

.nav-item {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border-left: 0 solid transparent;
  position: relative;
}
.nav-item:hover {
  background: var(--hover-nav-bg);
  color: var(--color-accent);
  transform: translateX(4px);
}
.nav-item:active { transform: translateX(2px) scale(0.98); }

.section-heading:first-child {
  margin-top: 0;
}
.section-heading.is-collapsed {
  height: 1px;
  margin: 18px 12px 10px;
  padding: 0;
  background: var(--border);
}
.section-heading.is-collapsed:first-child {
  display: none;
}

.sidebar-collapsed .sidebar-label {
  width: 0;
  opacity: 0;
  overflow: hidden;
  white-space: nowrap;
  transition: width 0.3s ease, opacity 0.2s ease;
}
.sidebar-label {
  transition: width 0.3s ease, opacity 0.2s ease 0.1s;
  white-space: nowrap;
}
.sidebar-collapsed .sidebar-link {
  justify-content: center;
  gap: 0;
  padding: 8px;
  width: 44px;
  height: 44px;
  margin: 0 auto;
  border-radius: 12px;
}
.sidebar-collapsed nav.flex { align-items: center; }
</style>
