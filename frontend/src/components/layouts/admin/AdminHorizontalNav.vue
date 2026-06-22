<template>
  <nav class="horiz-nav flex items-center flex-wrap gap-1 px-4 lg:px-8 py-2 transition-colors duration-500">
    <template v-for="group in navGroups" :key="group.label">
      <span v-if="group.label" class="group-label px-2 text-[11px] font-black uppercase tracking-wider">{{ group.label }}</span>
      <router-link v-for="item in group.items" :key="item.label"
                   :to="item.route"
                   :class="['horiz-link flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-all whitespace-nowrap',
                     isActiveRoute(item.route) ? 'active' : '']">
        <span class="material-symbols-outlined text-[18px]">{{ item.icon }}</span>
        <span>{{ item.label }}</span>
      </router-link>
    </template>
  </nav>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()

const navGroups = [
  {
    label: '',
    items: [
      { icon: 'dashboard', label: 'Dashboard', route: '/administrator/dashboard' },
    ],
  },
  {
    label: 'Buku',
    items: [
      { icon: 'menu_book', label: 'Books', route: '/administrator/books' },
      { icon: 'category', label: 'Kategori Buku', route: '/administrator/book-categories' },
      { icon: 'edit_square', label: 'Authors', route: '/administrator/authors' },
      { icon: 'assignment', label: 'Submissions', route: '/administrator/submissions' },
      { icon: 'payments', label: 'Royalti', route: '/administrator/royalties' },
    ],
  },
  {
    label: 'News',
    items: [
      { icon: 'newspaper', label: 'News', route: '/administrator/news' },
      { icon: 'category', label: 'Kategori News', route: '/administrator/news-categories' },
    ],
  },
  {
    label: 'Manajemen',
    items: [
      { icon: 'group', label: 'User', route: '/administrator/manajemen-user' },
      { icon: 'admin_panel_settings', label: 'Role', route: '/administrator/manajemen-role' },
    ],
  },
  {
    label: 'Website',
    items: [
      { icon: 'person', label: 'Profile', route: '/administrator/profile' },
      { icon: 'settings', label: 'Pengaturan', route: '/administrator/pengaturan' },
    ],
  },
]

function isActiveRoute(r) {
  return route.path === r
    || (r === '/administrator/news' && route.path.startsWith('/administrator/news/'))
    || (r === '/administrator/news-categories' && route.path.startsWith('/administrator/news-categories'))
    || (r === '/administrator/books' && route.path.startsWith('/administrator/books'))
    || (r === '/administrator/book-categories' && route.path.startsWith('/administrator/book-categories'))
    || (r === '/administrator/submissions' && route.path.startsWith('/administrator/submissions'))
    || (r === '/administrator/royalties' && route.path.startsWith('/administrator/royalties'))
}
</script>

<style scoped>
.horiz-nav {
  background: var(--bg-card);
  border-bottom: 1px solid var(--border);
}
.horiz-link { color: var(--text-muted); }
.group-label { color: var(--text-muted); opacity: 0.75; }
.horiz-link:hover { color: var(--text-heading); background: var(--hover-nav-bg); }
.horiz-link.active {
  color: var(--text-btn);
  background: var(--color-accent);
  font-weight: 700;
  box-shadow: 0 0 12px rgba(37, 99, 235, 0.25);
}
</style>
