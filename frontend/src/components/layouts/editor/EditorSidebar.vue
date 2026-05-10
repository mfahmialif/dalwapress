<template>
  <aside :class="['sidebar flex h-screen flex-col p-4 shrink-0 transition-all duration-300', collapsed ? 'sidebar-collapsed' : '']">
    <div class="flex items-center px-3 pb-4 shrink-0" :class="collapsed ? 'justify-center' : 'justify-between'">
      <div class="overflow-hidden transition-all duration-300" :style="collapsed ? 'width:0;opacity:0' : 'width:auto;opacity:1'">
        <h1 class="text-lg font-bold whitespace-nowrap" style="color: var(--text-heading)">UII Dalwa Press</h1>
        <p class="text-sm font-medium whitespace-nowrap" style="color: var(--text-muted)">Editor Portal</p>
      </div>
      <span v-if="collapsed" class="material-symbols-outlined text-accent text-[28px]">rate_review</span>
      <button @click="$emit('close-sidebar')" class="p-1.5 rounded-lg transition-colors cursor-pointer lg:hidden" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[22px]">close</span>
      </button>
    </div>
    <div class="flex-1 min-h-0">
      <simplebar class="h-full" :auto-hide="true">
        <nav class="flex flex-col gap-1 pr-1">
          <template v-for="section in navSections" :key="section.label">
            <div v-if="section.label" class="section-heading mt-5 mb-1 px-3" :class="collapsed ? 'is-collapsed' : ''">
              <p v-if="!collapsed" class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ section.label }}</p>
            </div>
            <router-link v-for="item in section.items" :key="item.label" :to="item.route"
              :class="['flex items-center gap-4 px-4 py-3 rounded-lg transition-colors sidebar-link', isActiveRoute(item.route) ? 'bg-accent text-btn-text font-bold hover:bg-accent/90' : 'nav-item font-semibold cursor-pointer']"
              :title="collapsed ? item.label : ''">
              <span class="material-symbols-outlined text-[26px] shrink-0">{{ item.icon }}</span>
              <span class="sidebar-label text-base leading-normal">{{ item.label }}</span>
            </router-link>
          </template>
        </nav>
      </simplebar>
    </div>
    <div class="pt-4 shrink-0">
      <button @click="authStore.logout()" class="flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-accent px-4 font-bold text-btn-text shadow-[0_0_15px_rgba(251,191,36,0.3)]">
        <span class="material-symbols-outlined text-[20px]">logout</span><span v-if="!collapsed">Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'
import simplebar from 'simplebar-vue'
import 'simplebar-vue/dist/simplebar.min.css'
import { useAuthStore } from '../../../stores/auth'

defineProps({ collapsed: { type: Boolean, default: false } })
defineEmits(['close-sidebar'])
const route = useRoute()
const authStore = useAuthStore()
const navSections = [
  { label: '', items: [{ icon: 'dashboard', label: 'Dashboard', route: '/editor/dashboard' }] },
  { label: 'Review', items: [{ icon: 'assignment', label: 'Assigned Submissions', route: '/editor/submissions' }] },
  { label: 'Akun', items: [{ icon: 'person', label: 'Profile Editor', route: '/editor/profile' }] },
]
function isActiveRoute(itemRoute) {
  return route.path === itemRoute || (itemRoute === '/editor/submissions' && route.path.startsWith('/editor/submissions'))
}
</script>

<style scoped>
.sidebar{background:var(--bg-sidebar)}.text-btn-text{color:var(--text-btn)}.nav-item{color:var(--text-body);transition:all .3s;border-left:0 solid transparent;position:relative}.nav-item:hover{background:var(--hover-nav-bg);color:var(--color-accent);transform:translateX(4px)}.section-heading.is-collapsed{height:1px;margin:18px 12px 10px;padding:0;background:var(--border)}.sidebar-collapsed .sidebar-label{width:0;opacity:0;overflow:hidden;white-space:nowrap;transition:width .3s,opacity .2s}.sidebar-label{transition:width .3s,opacity .2s .1s;white-space:nowrap}.sidebar-collapsed .sidebar-link{justify-content:center;gap:0;padding:8px;width:44px;height:44px;margin:0 auto;border-radius:12px}.sidebar-collapsed nav.flex{align-items:center}.sidebar :deep(.simplebar-scrollbar::before){background:var(--border-light);border-radius:999px}
</style>
