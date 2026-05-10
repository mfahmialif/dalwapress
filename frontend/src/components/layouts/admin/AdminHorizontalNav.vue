<template>
  <nav class="horiz-nav flex items-center flex-wrap gap-1 px-4 lg:px-8 py-2 transition-colors duration-500">
    <router-link v-for="item in navItems" :key="item.label"
                 :to="item.route"
                 :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                   isActiveRoute(item.route) ? 'active' : '']">
      <span class="material-symbols-outlined text-[18px]">{{ item.icon }}</span>
      <span>{{ item.label }}</span>
    </router-link>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()

const navItems = [
  { icon: 'dashboard', label: 'Dashboard', route: '/administrator/dashboard' },
  { icon: 'newspaper', label: 'News', route: '/administrator/news' },
  { icon: 'group', label: 'User', route: '/administrator/manajemen-user' },
  { icon: 'admin_panel_settings', label: 'Role', route: '/administrator/manajemen-role' },
  { icon: 'person', label: 'Profile', route: '/administrator/profile' },
  { icon: 'settings', label: 'Pengaturan', route: '/administrator/pengaturan' },
]

function isActiveRoute(r) {
  return route.path === r || (r === '/administrator/news' && route.path.startsWith('/administrator/news'))
}
</script>

<style scoped>
.horiz-nav {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
}
.horiz-link { color: var(--text-muted); }
.horiz-link:hover { color: var(--text-heading); background: var(--hover-nav-bg); }
.horiz-link.active {
  color: var(--text-btn);
  background: var(--color-accent);
  font-weight: 700;
  box-shadow: 0 0 12px rgba(251, 191, 36, 0.25);
}
</style>
