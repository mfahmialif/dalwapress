<template>
  <div :data-theme="isDark ? 'dark' : 'light'"
       class="author-root relative flex h-screen w-screen font-display overflow-hidden transition-colors duration-500">

    <Transition name="fade">
      <div v-if="sidebarOpen"
           class="sidebar-overlay fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"
           @click="sidebarOpen = false"></div>
    </Transition>

    <Transition name="sidebar-slide">
      <div v-if="layoutMode === 'vertical'" class="hidden lg:block relative group/sidebar shrink-0 transition-all duration-300"
           :style="{ width: sidebarCollapsed ? '72px' : '256px' }">
        <AuthorSidebar :collapsed="sidebarCollapsed"
          class="h-full"
          @close-sidebar="sidebarOpen = false"
          @toggle-collapse="toggleCollapse" />
        <button @click="toggleCollapse"
                class="collapse-pill absolute -right-3 top-8 z-50 w-6 h-6 rounded-full hidden lg:flex items-center justify-center cursor-pointer transition-all duration-300"
                :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
          <span class="material-symbols-outlined text-[14px] transition-transform duration-300"
                :class="sidebarCollapsed ? 'rotate-180' : ''">chevron_left</span>
        </button>
      </div>
    </Transition>

    <AuthorSidebar :collapsed="false"
      :class="[
        'sidebar-mobile fixed z-40 lg:hidden transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
      style="width: 256px"
      @close-sidebar="sidebarOpen = false" />

    <main class="flex flex-1 flex-col h-screen overflow-hidden transition-colors duration-500 min-w-0"
          :style="{ background: 'var(--bg-main)' }">
      <AuthorNavbar :page-title="pageTitle" :is-dark="isDark" :layout-mode="layoutMode"
                   @toggle-theme="toggleTheme"
                   @toggle-sidebar="sidebarOpen = !sidebarOpen"
                   @toggle-layout="toggleLayout" />

      <Transition name="horiz-slide">
        <AuthorHorizontalNav v-if="layoutMode === 'horizontal'" />
      </Transition>

      <div class="flex-1 min-h-0">
        <simplebar class="h-full content-scroll" :auto-hide="true">
          <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            <router-view v-slot="{ Component, route: viewRoute }">
              <Transition name="page" mode="out-in">
                <component :is="Component" :key="viewRoute.path" />
              </Transition>
            </router-view>
          </div>

          <AuthorFooter />
        </simplebar>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import AuthorSidebar from '../components/layouts/author/AuthorSidebar.vue'
import AuthorNavbar from '../components/layouts/author/AuthorNavbar.vue'
import AuthorHorizontalNav from '../components/layouts/author/AuthorHorizontalNav.vue'
import AuthorFooter from '../components/layouts/author/AuthorFooter.vue'

const route = useRoute()
const pageTitle = computed(() => route.meta.pageTitle || 'Dashboard Author')

const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const layoutMode = ref('vertical')
const isDark = ref(true)

onMounted(() => {
  const savedCollapsed = localStorage.getItem('author-sidebar-collapsed')
  if (savedCollapsed) sidebarCollapsed.value = savedCollapsed === 'true'
  const savedLayout = localStorage.getItem('author-layout-mode')
  if (savedLayout) layoutMode.value = savedLayout
  const savedTheme = localStorage.getItem('author-theme')
  if (savedTheme) isDark.value = savedTheme === 'dark'
})

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('author-sidebar-collapsed', String(sidebarCollapsed.value))
}

function toggleLayout() {
  layoutMode.value = layoutMode.value === 'vertical' ? 'horizontal' : 'vertical'
  localStorage.setItem('author-layout-mode', layoutMode.value)
}

function toggleTheme() {
  isDark.value = !isDark.value
  localStorage.setItem('author-theme', isDark.value ? 'dark' : 'light')
}

watch(() => route.path, () => {
  sidebarOpen.value = false
})
</script>

<style scoped>
.author-root[data-theme="dark"] {
  --bg-main: #0B1120;
  --bg-sidebar: #0B1120;
  --bg-header: rgba(11, 17, 32, 0.92);
  --bg-card: #141d38;
  --bg-input: #141d38;
  --bg-table-head: #141d38;
  --bg-table-body: #111a33;
  --border: #1c2850;
  --border-light: #243362;
  --text-heading: #ffffff;
  --text-body: #cbd5e1;
  --text-muted: #64748b;
  --text-btn: #020617;
  --color-accent: #fbbf24;
  --uptime-bg: #0e1628;
  --uptime-globe: #1c2850;
  --uptime-gradient: linear-gradient(to top, #111a33, transparent);
  --progress-track: #0e1628;
  --hover-nav-bg: rgba(251, 191, 36, 0.08);
  --shadow-card: 0 4px 24px rgba(0,0,0,0.4), 0 1px 3px rgba(0,0,0,0.2);
  --toggle-bg: #141d38;
  --toggle-text: #fbbf24;
  --color-scheme: dark;
}

.author-root[data-theme="light"] {
  --bg-main: #eef2f7;
  --bg-sidebar: #eef2f7;
  --bg-header: rgba(238, 242, 247, 0.92);
  --bg-card: #ffffff;
  --bg-input: #e2e8f0;
  --bg-table-head: #e8ecf1;
  --bg-table-body: #ffffff;
  --border: #dce3ec;
  --border-light: #cbd5e1;
  --text-heading: #0f172a;
  --text-body: #475569;
  --text-muted: #94a3b8;
  --text-btn: #0f172a;
  --color-accent: #fbbf24;
  --uptime-bg: #e2e8f0;
  --uptime-globe: #cbd5e1;
  --uptime-gradient: linear-gradient(to top, #ffffff, transparent);
  --progress-track: #dce3ec;
  --hover-nav-bg: rgba(251, 191, 36, 0.12);
  --shadow-card: 0 4px 24px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.04);
  --toggle-bg: #e2e8f0;
  --toggle-text: #475569;
  --color-scheme: light;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.page-enter-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.page-leave-active { transition: opacity 0.15s cubic-bezier(0.4, 0, 1, 1); }
.page-enter-from { opacity: 0; transform: translateY(12px); }
.page-leave-to { opacity: 0; }
.sidebar-slide-enter-active { transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.sidebar-slide-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.sidebar-slide-enter-from { opacity: 0; transform: translateX(-100%); }
.sidebar-slide-leave-to { opacity: 0; transform: translateX(-100%); }
.horiz-slide-enter-active { transition: opacity 0.3s ease 0.1s, transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.1s; }
.horiz-slide-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.horiz-slide-enter-from { opacity: 0; transform: translateY(-100%); }
.horiz-slide-leave-to { opacity: 0; transform: translateY(-100%); }
.sidebar-mobile { height: 100vh; }
.collapse-pill {
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text-muted);
  opacity: 0;
  transform: scale(0.8);
  box-shadow: 0 2px 8px rgba(0,0,0,0.25);
}
.group\/sidebar:hover .collapse-pill {
  opacity: 1;
  transform: scale(1);
}
.collapse-pill:hover {
  background: var(--color-accent);
  color: var(--text-btn);
  border-color: var(--color-accent);
  box-shadow: 0 0 12px rgba(251, 191, 36, 0.4);
  transform: scale(1.15) !important;
}
.content-scroll :deep(.simplebar-scrollbar::before) {
  background: var(--border-light);
  border-radius: 999px;
  opacity: 0;
  transition: opacity 0.4s ease;
}
.content-scroll :deep(.simplebar-scrollbar.simplebar-visible::before) { opacity: 0.6; }
.content-scroll:hover :deep(.simplebar-scrollbar::before) { opacity: 0.4; }
.content-scroll :deep(.simplebar-scrollbar:hover::before) {
  background: var(--text-muted);
  opacity: 1;
}
.content-scroll :deep(.simplebar-track.simplebar-vertical) { width: 8px; right: 0; }
.content-scroll :deep(.simplebar-track.simplebar-horizontal) { display: none; }
.author-root { background: var(--bg-main); color: var(--text-body); }
.author-root :deep(.stat-card) {
  background: var(--bg-card);
  box-shadow: var(--shadow-card);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.author-root :deep(.stat-card:hover) {
  transform: translateY(-6px);
  border-color: rgba(251, 191, 36, 0.7);
  box-shadow:
    0 0 25px rgba(251, 191, 36, 0.15),
    0 20px 40px -15px rgba(0, 0, 0, 0.3),
    inset 0 1px 0 rgba(251, 191, 36, 0.1);
}
.author-root :deep(.table-row-hover) {
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  border-left: 3px solid transparent;
}
.author-root :deep(.table-row-hover:hover) {
  background: var(--hover-nav-bg);
  border-left-color: var(--color-accent);
}
.author-root :deep(.table-row-hover:hover td) { color: var(--text-heading); }
</style>
