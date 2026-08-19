<template>
  <div :data-theme="isDark ? 'dark' : 'light'" class="cover-root relative flex h-screen w-screen font-display overflow-hidden transition-colors duration-500">
    <Transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>
    </Transition>

    <Transition name="sidebar-slide">
      <div v-if="layoutMode === 'vertical'" class="hidden lg:block relative group/sidebar shrink-0 transition-all duration-300" :style="{ width: sidebarCollapsed ? '72px' : '256px' }">
        <CoverSidebar :collapsed="sidebarCollapsed" class="h-full" @close-sidebar="sidebarOpen = false" />
        <button @click="toggleCollapse" class="collapse-pill absolute -right-3 top-8 z-50 hidden h-6 w-6 items-center justify-center rounded-full lg:flex" :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
          <span class="material-symbols-outlined text-[14px]" :class="sidebarCollapsed ? 'rotate-180' : ''">chevron_left</span>
        </button>
      </div>
    </Transition>

    <CoverSidebar :collapsed="false" :class="['fixed z-40 h-screen lg:hidden transition-transform duration-300', sidebarOpen ? 'translate-x-0' : '-translate-x-full']" style="width: 256px" @close-sidebar="sidebarOpen = false" />

    <main class="flex min-w-0 flex-1 flex-col h-screen overflow-hidden" :style="{ background: 'var(--bg-main)' }">
      <CoverNavbar :page-title="pageTitle" :is-dark="isDark" :layout-mode="layoutMode" @toggle-theme="toggleTheme" @toggle-sidebar="sidebarOpen = !sidebarOpen" @toggle-layout="toggleLayout" />
      <Transition name="horiz-slide">
        <CoverHorizontalNav v-if="layoutMode === 'horizontal'" />
      </Transition>
      <div class="flex-1 min-h-0">
        <simplebar class="h-full content-scroll" :auto-hide="true">
          <div class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8">
            <router-view v-slot="{ Component, route: viewRoute }">
              <Transition name="page" mode="out-in">
                <component :is="Component" :key="viewRoute.path" />
              </Transition>
            </router-view>
          </div>
          <CoverFooter />
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
import CoverSidebar from '../components/layouts/cover/CoverSidebar.vue'
import CoverNavbar from '../components/layouts/cover/CoverNavbar.vue'
import CoverHorizontalNav from '../components/layouts/cover/CoverHorizontalNav.vue'
import CoverFooter from '../components/layouts/cover/CoverFooter.vue'
import { readThemePreference, resolveThemePreference, writeThemePreference } from '../composables/useThemePreference'

const route = useRoute()
const pageTitle = computed(() => route.meta.pageTitle || 'Dashboard Desain Cover')
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const layoutMode = ref('vertical')
const isDark = ref(true)

onMounted(() => {
  sidebarCollapsed.value = localStorage.getItem('cover-sidebar-collapsed') === 'true'
  layoutMode.value = localStorage.getItem('cover-layout-mode') || 'vertical'
  const savedTheme = readThemePreference({ legacyKey: 'cover-theme', defaultTheme: 'dark' })
  isDark.value = resolveThemePreference(savedTheme) === 'dark'
})

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('cover-sidebar-collapsed', sidebarCollapsed.value)
}

function toggleLayout() {
  layoutMode.value = layoutMode.value === 'vertical' ? 'horizontal' : 'vertical'
  localStorage.setItem('cover-layout-mode', layoutMode.value)
}

function toggleTheme() {
  isDark.value = !isDark.value
  writeThemePreference(isDark.value ? 'dark' : 'light', { legacyKey: 'cover-theme' })
}

watch(route, () => {
  sidebarOpen.value = false
})
</script>

<style scoped>
.cover-root{color:var(--text-body)}.collapse-pill{background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted);cursor:pointer;transition:all .2s}.collapse-pill:hover{color:var(--color-accent);border-color:var(--color-accent)}.fade-enter-active,.fade-leave-active{transition:opacity .3s}.fade-enter-from,.fade-leave-to{opacity:0}.page-enter-active,.page-leave-active{transition:opacity .2s,transform .2s}.page-enter-from{opacity:0;transform:translateY(8px)}.page-leave-to{opacity:0;transform:translateY(-8px)}.horiz-slide-enter-active,.horiz-slide-leave-active{transition:all .3s ease}.horiz-slide-enter-from,.horiz-slide-leave-to{max-height:0;opacity:0;overflow:hidden}.sidebar-slide-enter-active,.sidebar-slide-leave-active{transition:all .3s ease}.sidebar-slide-enter-from,.sidebar-slide-leave-to{width:0!important;opacity:0;overflow:hidden}
</style>
