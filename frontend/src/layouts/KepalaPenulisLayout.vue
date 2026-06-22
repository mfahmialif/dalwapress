<template>
  <div :data-theme="isDark ? 'dark' : 'light'" class="portal-root relative flex h-screen w-screen font-display overflow-hidden transition-colors duration-500">
    <Transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>
    </Transition>
    <div class="hidden lg:block relative shrink-0 transition-all duration-300" :style="{ width: sidebarCollapsed ? '72px' : '256px' }">
      <KepalaPenulisSidebar :collapsed="sidebarCollapsed" class="h-full" @close-sidebar="sidebarOpen = false" />
      <button @click="toggleCollapse" class="collapse-pill absolute -right-3 top-8 z-50 hidden h-6 w-6 items-center justify-center rounded-full lg:flex"><span class="material-symbols-outlined text-[14px]" :class="sidebarCollapsed ? 'rotate-180' : ''">chevron_left</span></button>
    </div>
    <KepalaPenulisSidebar :collapsed="false" :class="['fixed z-40 h-screen lg:hidden transition-transform duration-300', sidebarOpen ? 'translate-x-0' : '-translate-x-full']" style="width: 256px" @close-sidebar="sidebarOpen = false" />
    <main class="flex min-w-0 flex-1 flex-col h-screen overflow-hidden" :style="{ background: 'var(--bg-main)' }">
      <KepalaPenulisNavbar :page-title="pageTitle" :is-dark="isDark" @toggle-theme="toggleTheme" @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      <div class="flex-1 min-h-0">
        <simplebar class="h-full content-scroll" :auto-hide="true">
          <div class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8">
            <router-view v-slot="{ Component, route: viewRoute }">
              <Transition name="page" mode="out-in"><component :is="Component" :key="viewRoute.path" /></Transition>
            </router-view>
          </div>
        </simplebar>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import KepalaPenulisSidebar from '../components/layouts/kepalaPenulis/KepalaPenulisSidebar.vue'
import KepalaPenulisNavbar from '../components/layouts/kepalaPenulis/KepalaPenulisNavbar.vue'
import { readThemePreference, resolveThemePreference, writeThemePreference } from '../composables/useThemePreference'

const route = useRoute()
const pageTitle = computed(() => route.meta.pageTitle || 'Dashboard Kepala Penulis')
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const isDark = ref(true)
onMounted(() => {
  sidebarCollapsed.value = localStorage.getItem('kepala-penulis-sidebar-collapsed') === 'true'
  const savedTheme = readThemePreference({
    allowSystem: true,
    fallback: 'system',
    legacyKeys: ['kepala-penulis-theme'],
  })
  isDark.value = resolveThemePreference(savedTheme) === 'dark'
})
function toggleCollapse(){sidebarCollapsed.value=!sidebarCollapsed.value;localStorage.setItem('kepala-penulis-sidebar-collapsed',String(sidebarCollapsed.value))}
function toggleTheme(){isDark.value=!isDark.value;writeThemePreference(isDark.value?'dark':'light',{legacyKeys:['kepala-penulis-theme']})}
watch(() => route.path, () => { sidebarOpen.value = false })
</script>

<style scoped>
.portal-root[data-theme="dark"]{--bg-main:#0B1120;--bg-sidebar:#0B1120;--bg-header:rgba(11,17,32,.92);--bg-card:#141d38;--bg-input:#141d38;--border:#1c2850;--border-light:#243362;--text-heading:#fff;--text-body:#cbd5e1;--text-muted:#64748b;--text-btn:#ffffff;--color-accent:#2563eb;--hover-nav-bg:rgba(37,99,235,.08);--shadow-card:0 4px 24px rgba(0,0,0,.4),0 1px 3px rgba(0,0,0,.2);--toggle-bg:#141d38;--toggle-text:#2563eb}
.portal-root[data-theme="light"]{--bg-main:#eef2f7;--bg-sidebar:#eef2f7;--bg-header:rgba(238,242,247,.92);--bg-card:#fff;--bg-input:#ffffff;--border:#dce3ec;--border-light:#cbd5e1;--text-heading:#0f172a;--text-body:#475569;--text-muted:#94a3b8;--text-btn:#ffffff;--color-accent:#2563eb;--hover-nav-bg:rgba(37,99,235,.12);--shadow-card:0 4px 24px rgba(0,0,0,.07),0 1px 3px rgba(0,0,0,.04);--toggle-bg:#e2e8f0;--toggle-text:#475569}
.fade-enter-active,.fade-leave-active{transition:opacity .3s ease}.fade-enter-from,.fade-leave-to{opacity:0}.page-enter-active{transition:opacity .3s,transform .3s}.page-leave-active{transition:opacity .15s}.page-enter-from{opacity:0;transform:translateY(12px)}.page-leave-to{opacity:0}.collapse-pill{background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted);box-shadow:0 2px 8px rgba(0,0,0,.25)}.collapse-pill:hover{background:var(--color-accent);color:var(--text-btn);border-color:var(--color-accent)}.content-scroll :deep(.simplebar-scrollbar::before){background:var(--border-light);border-radius:999px}.portal-root{background:var(--bg-main);color:var(--text-body)}.portal-root :deep(.stat-card){background:var(--bg-card);box-shadow:var(--shadow-card);transition:all .3s}.portal-root :deep(.stat-card:hover){transform:translateY(-4px);border-color:rgba(37,99,235,.65)}
</style>
