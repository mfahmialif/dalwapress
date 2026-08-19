<template>
  <nav class="horizontal-nav flex items-center gap-1 border-b px-4 py-2 overflow-x-auto">
    <router-link v-for="item in navItems" :key="item.label" :to="item.route"
      :class="['flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors', isActiveRoute(item.route) ? 'bg-accent text-btn-text font-bold' : 'hover:bg-accent/10']" style="color: var(--text-body)">
      <span class="material-symbols-outlined text-[20px]">{{ item.icon }}</span>
      <span>{{ item.label }}</span>
    </router-link>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'
const route = useRoute()
const navItems = [
  { icon: 'dashboard', label: 'Dashboard', route: '/cover/dashboard' },
  { icon: 'palette', label: 'Submissions', route: '/cover/submissions' },
  { icon: 'person', label: 'Profile', route: '/cover/profile' },
]
function isActiveRoute(itemRoute) {
  return route.path === itemRoute || (itemRoute === '/cover/submissions' && route.path.startsWith('/cover/submissions'))
}
</script>

<style scoped>
.horizontal-nav{background:var(--bg-sidebar);border-color:var(--border)}.text-btn-text{color:var(--text-btn)}
</style>
