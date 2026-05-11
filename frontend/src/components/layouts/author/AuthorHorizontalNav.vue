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
  { icon: 'dashboard', label: 'Dashboard', route: '/author/dashboard' },
  { icon: 'assignment', label: 'Submissions', route: '/author/submissions' },
  { icon: 'upload_file', label: 'Create', route: '/author/submissions/create' },
  { icon: 'library_books', label: 'Books', route: '/author/books' },
  { icon: 'payments', label: 'Royalti', route: '/author/royalties' },
  { icon: 'notifications', label: 'Notifications', route: '/author/notifications' },
  { icon: 'history', label: 'Activity', route: '/author/activity' },
  { icon: 'bookmark', label: 'Bookmarks', route: '/author/bookmarks' },
  { icon: 'person', label: 'Profile', route: '/author/profile' },
]

function isActiveRoute(r) {
  return route.path === r
    || (r === '/author/submissions' && route.path.startsWith('/author/submissions') && route.path !== '/author/submissions/create')
    || (r === '/author/books' && route.path.startsWith('/author/books'))
    || (r === '/author/royalties' && route.path.startsWith('/author/royalties'))
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
  box-shadow: 0 0 12px rgba(37, 99, 235, 0.25);
}
</style>
