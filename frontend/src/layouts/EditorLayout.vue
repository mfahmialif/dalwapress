<template>
  <div :data-theme="isDark ? 'dark' : 'light'" class="editor-root relative flex h-screen w-screen font-display overflow-hidden transition-colors duration-500">
    <Transition name="fade">
      <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false"></div>
    </Transition>

    <Transition name="sidebar-slide">
      <div v-if="layoutMode === 'vertical'" class="hidden lg:block relative group/sidebar shrink-0 transition-all duration-300" :style="{ width: sidebarCollapsed ? '72px' : '256px' }">
        <EditorSidebar :collapsed="sidebarCollapsed" class="h-full" @close-sidebar="sidebarOpen = false" />
        <button @click="toggleCollapse" class="collapse-pill absolute -right-3 top-8 z-50 hidden h-6 w-6 items-center justify-center rounded-full lg:flex" :title="sidebarCollapsed ? 'Expand' : 'Collapse'">
          <span class="material-symbols-outlined text-[14px]" :class="sidebarCollapsed ? 'rotate-180' : ''">chevron_left</span>
        </button>
      </div>
    </Transition>

    <EditorSidebar :collapsed="false" :class="['fixed z-40 h-screen lg:hidden transition-transform duration-300', sidebarOpen ? 'translate-x-0' : '-translate-x-full']" style="width: 256px" @close-sidebar="sidebarOpen = false" />

    <main class="flex min-w-0 flex-1 flex-col h-screen overflow-hidden" :style="{ background: 'var(--bg-main)' }">
      <EditorNavbar :page-title="pageTitle" :is-dark="isDark" :layout-mode="layoutMode" @toggle-theme="toggleTheme" @toggle-sidebar="sidebarOpen = !sidebarOpen" @toggle-layout="toggleLayout" />
      <Transition name="horiz-slide">
        <EditorHorizontalNav v-if="layoutMode === 'horizontal'" />
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
          <EditorFooter />
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
import EditorSidebar from '../components/layouts/editor/EditorSidebar.vue'
import EditorNavbar from '../components/layouts/editor/EditorNavbar.vue'
import EditorHorizontalNav from '../components/layouts/editor/EditorHorizontalNav.vue'
import EditorFooter from '../components/layouts/editor/EditorFooter.vue'

const route = useRoute()
const pageTitle = computed(() => route.meta.pageTitle || 'Dashboard Editor')
const sidebarOpen = ref(false)
const sidebarCollapsed = ref(false)
const layoutMode = ref('vertical')
const isDark = ref(true)

onMounted(() => {
  sidebarCollapsed.value = localStorage.getItem('editor-sidebar-collapsed') === 'true'
  layoutMode.value = localStorage.getItem('editor-layout-mode') || 'vertical'
  isDark.value = (localStorage.getItem('editor-theme') || 'dark') === 'dark'
})

function toggleCollapse() {
  sidebarCollapsed.value = !sidebarCollapsed.value
  localStorage.setItem('editor-sidebar-collapsed', String(sidebarCollapsed.value))
}
function toggleLayout() {
  layoutMode.value = layoutMode.value === 'vertical' ? 'horizontal' : 'vertical'
  localStorage.setItem('editor-layout-mode', layoutMode.value)
}
function toggleTheme() {
  isDark.value = !isDark.value
  localStorage.setItem('editor-theme', isDark.value ? 'dark' : 'light')
}
watch(() => route.path, () => { sidebarOpen.value = false })
</script>

<style scoped>
.editor-root[data-theme="dark"] {
  --bg-main: #0B1120; --bg-sidebar: #0B1120; --bg-header: rgba(11,17,32,.92); --bg-card: #141d38; --bg-input: #141d38; --border: #1c2850; --border-light: #243362; --text-heading: #fff; --text-body: #cbd5e1; --text-muted: #64748b; --text-btn: #ffffff; --color-accent: #2563eb; --hover-nav-bg: rgba(37, 99, 235,.08); --shadow-card: 0 4px 24px rgba(0,0,0,.4),0 1px 3px rgba(0,0,0,.2); --toggle-bg: #141d38; --toggle-text: #2563eb;
}
.editor-root[data-theme="light"] {
  --bg-main: #eef2f7; --bg-sidebar: #eef2f7; --bg-header: rgba(238,242,247,.92); --bg-card: #fff; --bg-input: #ffffff; --border: #dce3ec; --border-light: #cbd5e1; --text-heading: #0f172a; --text-body: #475569; --text-muted: #94a3b8; --text-btn: #ffffff; --color-accent: #2563eb; --hover-nav-bg: rgba(37, 99, 235,.12); --shadow-card: 0 4px 24px rgba(0,0,0,.07),0 1px 3px rgba(0,0,0,.04); --toggle-bg: #e2e8f0; --toggle-text: #475569;
}
.fade-enter-active,.fade-leave-active{transition:opacity .3s ease}.fade-enter-from,.fade-leave-to{opacity:0}
.page-enter-active{transition:opacity .3s,transform .3s}.page-leave-active{transition:opacity .15s}.page-enter-from{opacity:0;transform:translateY(12px)}.page-leave-to{opacity:0}
.sidebar-slide-enter-active,.horiz-slide-enter-active{transition:opacity .3s,transform .3s}.sidebar-slide-leave-active,.horiz-slide-leave-active{transition:opacity .2s,transform .2s}.sidebar-slide-enter-from,.sidebar-slide-leave-to{opacity:0;transform:translateX(-100%)}.horiz-slide-enter-from,.horiz-slide-leave-to{opacity:0;transform:translateY(-100%)}
.collapse-pill{background:var(--bg-card);border:1px solid var(--border);color:var(--text-muted);opacity:0;transform:scale(.8);box-shadow:0 2px 8px rgba(0,0,0,.25)}.group\/sidebar:hover .collapse-pill{opacity:1;transform:scale(1)}.collapse-pill:hover{background:var(--color-accent);color:var(--text-btn);border-color:var(--color-accent);box-shadow:0 0 12px rgba(37, 99, 235,.4);transform:scale(1.15)!important}
.content-scroll :deep(.simplebar-scrollbar::before){background:var(--border-light);border-radius:999px;opacity:0;transition:opacity .4s}.content-scroll :deep(.simplebar-scrollbar.simplebar-visible::before){opacity:.6}.content-scroll:hover :deep(.simplebar-scrollbar::before){opacity:.4}.content-scroll :deep(.simplebar-track.simplebar-vertical){width:8px;right:0}.content-scroll :deep(.simplebar-track.simplebar-horizontal){display:none}
.editor-root{background:var(--bg-main);color:var(--text-body)}.editor-root :deep(.stat-card){background:var(--bg-card);box-shadow:var(--shadow-card);transition:all .4s}.editor-root :deep(.stat-card:hover){transform:translateY(-6px);border-color:rgba(37, 99, 235,.7);box-shadow:0 0 25px rgba(37, 99, 235,.15),0 20px 40px -15px rgba(0,0,0,.3)}
</style>
