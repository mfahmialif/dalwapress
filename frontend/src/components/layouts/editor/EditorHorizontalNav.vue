<template>
  <nav class="flex items-center flex-wrap gap-1 px-4 lg:px-8 py-2" style="background: var(--bg-card); border-bottom: 1px solid var(--border)">
    <router-link v-for="item in navItems" :key="item.label" :to="item.route" :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap', isActiveRoute(item.route) ? 'active' : '']">
      <span class="material-symbols-outlined text-[18px]">{{ item.icon }}</span><span>{{ item.label }}</span>
    </router-link>
  </nav>
</template>
<script setup>
import { useRoute } from 'vue-router'
const route = useRoute()
const navItems = [
  { icon: 'dashboard', label: 'Dashboard', route: '/editor/dashboard' },
  { icon: 'assignment', label: 'Submissions', route: '/editor/submissions' },
  { icon: 'person', label: 'Profile', route: '/editor/profile' },
]
function isActiveRoute(r){ return route.path === r || (r === '/editor/submissions' && route.path.startsWith('/editor/submissions')) }
</script>
<style scoped>
.horiz-link{color:var(--text-muted)}.horiz-link:hover{color:var(--text-heading);background:var(--hover-nav-bg)}.horiz-link.active{color:var(--text-btn);background:var(--color-accent);font-weight:700;box-shadow:0 0 12px rgba(251,191,36,.25)}
</style>
