<template>
  <div :data-theme="isDark ? 'dark' : 'light'"
       class="admin-root relative flex h-screen w-screen font-display overflow-hidden transition-colors duration-500">

    <!-- ═══════ MOBILE OVERLAY ═══════ -->
    <Transition name="fade">
      <div v-if="sidebarOpen"
           class="sidebar-overlay fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden"
           @click="sidebarOpen = false"></div>
    </Transition>

    <!-- ═══════ SIDEBAR + TOGGLE (vertical only) ═══════ -->
    <Transition name="sidebar-slide">
      <div v-if="layoutMode === 'vertical'" class="hidden lg:block relative group/sidebar shrink-0 transition-all duration-300"
           :style="{ width: sidebarCollapsed ? '72px' : '256px' }">
        <AdminSidebar :collapsed="sidebarCollapsed"
          class="h-full"
          @close-sidebar="sidebarOpen = false"
          @toggle-collapse="toggleCollapse" />
        <!-- Edge toggle pill -->
        <button @click="toggleCollapse"
                class="collapse-pill absolute -right-3 top-8 z-50 w-6 h-6 rounded-full hidden lg:flex items-center justify-center cursor-pointer transition-all duration-300"
                :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
          <span class="material-symbols-outlined text-[14px] transition-transform duration-300"
                :class="sidebarCollapsed ? 'rotate-180' : ''">chevron_left</span>
        </button>
      </div>
    </Transition>

    <!-- Mobile sidebar (always vertical) -->
    <AdminSidebar :collapsed="false"
      :class="[
        'sidebar-mobile fixed z-40 lg:hidden transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
      style="width: 256px"
      @close-sidebar="sidebarOpen = false" />

    <!-- ═══════ MAIN CONTENT ═══════ -->
    <main class="flex flex-1 flex-col h-screen overflow-hidden transition-colors duration-500 min-w-0"
          :style="{ background: 'var(--bg-main)' }">

      <!-- ═══════ NAVBAR ═══════ -->
      <AdminNavbar :page-title="pageTitle" :is-dark="isDark" :layout-mode="layoutMode"
                   @toggle-theme="toggleTheme"
                   @toggle-sidebar="sidebarOpen = !sidebarOpen"
                   @toggle-layout="toggleLayout" />

      <!-- ═══════ HORIZONTAL NAV (horizontal only) ═══════ -->
      <Transition name="horiz-slide">
        <AdminHorizontalNav v-if="layoutMode === 'horizontal'" />
      </Transition>

      <!-- Scrollable content area with SimpleBar -->
      <div class="flex-1 min-h-0 relative z-10">
        <simplebar class="h-full content-scroll" :auto-hide="true">
          <!-- ★ Page Content (router-view) ★ -->
          <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
            <router-view v-slot="{ Component, route: viewRoute }">
              <Transition name="page" mode="out-in">
                <component :is="Component" :key="viewRoute.path" />
              </Transition>
            </router-view>
          </div>

          <!-- ═══════ FOOTER ═══════ -->
          <AdminFooter />
        </simplebar>
      </div>
    </main>
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import AdminSidebar from '../components/layouts/admin/AdminSidebar.vue'
import AdminNavbar from '../components/layouts/admin/AdminNavbar.vue'
import AdminHorizontalNav from '../components/layouts/admin/AdminHorizontalNav.vue'
import AdminFooter from '../components/layouts/admin/AdminFooter.vue'
import ToastContainer from '../components/common/ToastContainer.vue'
import api from '../axios'
import { readThemePreference, resolveThemePreference, writeThemePreference } from '../composables/useThemePreference'
import { syncPublicSettings } from '../services/publicSettings'

const route = useRoute()
const pageTitle = computed(() => route.meta.pageTitle || 'Dashboard')

// ── Sidebar ──
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const layoutMode = ref('vertical')

onMounted(() => {
  const savedCollapsed = localStorage.getItem('admin-sidebar-collapsed')
  if (savedCollapsed) sidebarCollapsed.value = savedCollapsed === 'true'
  const savedLayout = localStorage.getItem('admin-layout-mode')
  if (savedLayout) layoutMode.value = savedLayout
  loadAppearanceSettings()
})

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('admin-sidebar-collapsed', String(sidebarCollapsed.value))
}

function toggleLayout() {
  layoutMode.value = layoutMode.value === 'vertical' ? 'horizontal' : 'vertical'
  localStorage.setItem('admin-layout-mode', layoutMode.value)
}

// Close sidebar on route change (mobile)
watch(() => route.path, () => {
  sidebarOpen.value = false
})

// ── Theme ──
const isDark = ref(true)

onMounted(() => {
  const saved = readThemePreference({
    allowSystem: true,
    fallback: 'system',
    legacyKeys: ['admin-theme'],
  })
  isDark.value = resolveThemePreference(saved) === 'dark'
})

function toggleTheme() {
  isDark.value = !isDark.value
  writeThemePreference(isDark.value ? 'dark' : 'light', {
    legacyKeys: ['admin-theme'],
  })
}

// ── Dynamic Branding Sync ──
async function loadAppearanceSettings() {
  try {
    const { data } = await api.get('/settings/public')
    syncPublicSettings(data)
  } catch {
    // Keep defaults
  }
}
</script>

<style scoped>
/* ═══════════════════════════════════════════
   THEME VARIABLES — Dark & Light
   ═══════════════════════════════════════════ */
.admin-root[data-theme="dark"] {
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
  --text-btn: #ffffff;
  --uptime-bg: #0e1628;
  --uptime-globe: #1c2850;
  --uptime-gradient: linear-gradient(to top, #111a33, transparent);
  --progress-track: #0e1628;
  --hover-nav-bg: rgba(37, 99, 235, 0.08);
  --shadow-card: 0 4px 24px rgba(0,0,0,0.4), 0 1px 3px rgba(0,0,0,0.2);
  --toggle-bg: #141d38;
  --toggle-text: #2563eb;
  --color-scheme: dark;
  --status-aktif-text: #4ade80;
  --status-aktif-bg: rgba(74, 222, 128, 0.1);
  --status-aktif-border: rgba(74, 222, 128, 0.3);
  --cat-video-text: #f87171;
  --cat-video-bg: rgba(248, 113, 113, 0.1);
  --cat-video-border: rgba(248, 113, 113, 0.3);
  --cat-gambar-text: #60a5fa;
  --cat-gambar-bg: rgba(96, 165, 250, 0.1);
  --cat-gambar-border: rgba(96, 165, 250, 0.3);
}

.admin-root[data-theme="light"] {
  --bg-main: #eef2f7;
  --bg-sidebar: #eef2f7;
  --bg-header: rgba(238, 242, 247, 0.92);
  --bg-card: #ffffff;
  --bg-input: #ffffff;
  --bg-table-head: #e8ecf1;
  --bg-table-body: #ffffff;
  --border: #dce3ec;
  --border-light: #cbd5e1;
  --text-heading: #0f172a;
  --text-body: #475569;
  --text-muted: #94a3b8;
  --text-btn: #ffffff;
  --uptime-bg: #e2e8f0;
  --uptime-globe: #cbd5e1;
  --uptime-gradient: linear-gradient(to top, #ffffff, transparent);
  --progress-track: #e2e8f0;
  --hover-nav-bg: rgba(37, 99, 235, 0.12);
  --shadow-card: 0 4px 24px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.04);
  --toggle-bg: #e2e8f0;
  --toggle-text: #475569;
  --color-scheme: light;
  --status-aktif-text: #16a34a;
  --status-aktif-bg: rgba(22, 163, 74, 0.1);
  --status-aktif-border: rgba(22, 163, 74, 0.3);
  --cat-video-text: #dc2626;
  --cat-video-bg: rgba(220, 38, 38, 0.1);
  --cat-video-border: rgba(220, 38, 38, 0.3);
  --cat-gambar-text: #2563eb;
  --cat-gambar-bg: rgba(37, 99, 235, 0.1);
  --cat-gambar-border: rgba(37, 99, 235, 0.3);
}

/* Base font & theme rules */
.admin-root {
  color: var(--text-body);
  background-color: var(--bg-main);
}

/* Mobile sidebar slide */
.sidebar-slide-enter-active,
.sidebar-slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
  transform: translateX(-100%);
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Horizontal nav transition */
.horiz-slide-enter-active {
  transition: opacity 0.3s ease 0.1s, transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) 0.1s;
}
.horiz-slide-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.horiz-slide-enter-from {
  opacity: 0;
  transform: translateY(-100%);
}
.horiz-slide-leave-to {
  opacity: 0;
  transform: translateY(-100%);
}

/* Page Transition */
.page-enter-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}
.page-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.page-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Global scrollbar style for content-scroll */
:deep(.content-scroll .simplebar-content-wrapper) {
  border-radius: 0;
}
:deep(.simplebar-track.simplebar-vertical) {
  width: 8px;
  right: 2px;
}
:deep(.simplebar-track.simplebar-vertical .simplebar-scrollbar) {
  border-radius: 4px;
}
.admin-root[data-theme="dark"] :deep(.simplebar-track.simplebar-vertical .simplebar-scrollbar::before) {
  background: rgba(255, 255, 255, 0.25);
}
.admin-root[data-theme="light"] :deep(.simplebar-track.simplebar-vertical .simplebar-scrollbar::before) {
  background: rgba(0, 0, 0, 0.25);
}

/* Multiselect overrides */
.admin-root :deep(.multiselect) {
  min-height: 40px;
  border-radius: 0.75rem;
}
.admin-root :deep(.multiselect__tags) {
  min-height: 40px;
  border-radius: 0.75rem;
  background: var(--bg-card);
  border-color: var(--border);
  padding: 6px 40px 0 12px;
}
.admin-root :deep(.multiselect__single),
.admin-root :deep(.multiselect__input) {
  background: transparent;
  color: var(--text-heading);
  font-size: 0.875rem;
  margin-bottom: 0;
  padding: 2px 0;
}
.admin-root :deep(.multiselect__placeholder) {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin-bottom: 0;
  padding-top: 2px;
}
.admin-root :deep(.multiselect__select) {
  height: 36px;
  width: 36px;
}
.admin-root :deep(.multiselect__select::before) {
  border-color: var(--text-muted) transparent transparent;
  border-width: 5px 5px 0;
}
.admin-root :deep(.multiselect--active .multiselect__select::before) {
  border-color: var(--color-accent) transparent transparent;
}
.admin-root :deep(.multiselect__content-wrapper) {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
  margin-top: 4px;
  z-index: 100;
}
.admin-root :deep(.multiselect__option) {
  color: var(--text-body);
  font-size: 0.875rem;
  padding: 10px 14px;
  min-height: unset;
  line-height: 1.4;
  transition: background 0.15s ease, color 0.15s ease;
}
.admin-root :deep(.multiselect__option--highlight) {
  background: rgba(37, 99, 235, 0.12);
  color: var(--color-accent);
}
.admin-root :deep(.multiselect__option--selected) {
  background: rgba(37, 99, 235, 0.2);
  color: var(--color-accent);
  font-weight: 700;
}
.admin-root :deep(.multiselect__tag) {
  background: rgba(37, 99, 235, 0.16);
  color: var(--color-accent);
  border-radius: 9999px;
  padding: 3px 26px 3px 10px;
  font-weight: 600;
  font-size: 0.8125rem;
}
.admin-root :deep(.multiselect__tag-icon::after) {
  color: var(--color-accent);
}
.admin-root :deep(.multiselect__tag-icon:hover) {
  background: rgba(37, 99, 235, 0.3);
}
</style>
