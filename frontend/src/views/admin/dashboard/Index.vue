<template>
  <div class="flex flex-col gap-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="stat in stats" :key="stat.label" class="stat-card rounded-xl p-6 border border-accent/20">
        <div class="flex items-center justify-between">
          <p class="text-sm font-bold uppercase tracking-wider text-accent">{{ stat.label }}</p>
          <span class="material-symbols-outlined text-accent text-[28px]">{{ stat.icon }}</span>
        </div>
        <p class="mt-4 text-3xl font-black" style="color: var(--text-heading)">{{ stat.value }}</p>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
      <section class="table-wrapper rounded-xl p-6">
        <div class="flex items-center justify-between gap-4">
          <div>
            <h2 class="text-xl font-bold" style="color: var(--text-heading)">Ringkasan UII Dalwa Press</h2>
            <p class="mt-1 text-sm" style="color: var(--text-muted)">Kelola publikasi melalui modul News dan pengaturan akun admin.</p>
          </div>
          <router-link to="/administrator/news/create" class="inline-flex h-10 items-center gap-2 rounded-lg bg-accent px-4 text-sm font-bold" style="color: var(--text-btn)">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            Tambah News
          </router-link>
        </div>

        <div class="mt-6 grid gap-3 md:grid-cols-3">
          <div v-for="item in modules" :key="item.title" class="rounded-lg border p-4" style="border-color: var(--border); background: var(--bg-card)">
            <span class="material-symbols-outlined text-accent">{{ item.icon }}</span>
            <h3 class="mt-3 font-bold" style="color: var(--text-heading)">{{ item.title }}</h3>
            <p class="mt-2 text-sm leading-6" style="color: var(--text-muted)">{{ item.body }}</p>
          </div>
        </div>
      </section>

      <aside class="network-card rounded-xl p-6">
        <h2 class="text-xl font-bold" style="color: var(--text-heading)">Modul Aktif</h2>
        <div class="mt-5 flex flex-col gap-3">
          <div v-for="item in activeModules" :key="item" class="flex items-center gap-3 rounded-lg p-3" style="background: var(--bg-input)">
            <span class="material-symbols-outlined text-green-400 text-[20px]">check_circle</span>
            <span class="text-sm font-semibold" style="color: var(--text-body)">{{ item }}</span>
          </div>
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../../../axios'

const stats = ref([
  { label: 'Total News', icon: 'article', value: '0' },
  { label: 'Published', icon: 'task_alt', value: '0' },
  { label: 'Draft', icon: 'draft', value: '0' },
  { label: 'Users', icon: 'manage_accounts', value: '0' }
])

const modules = [
  { icon: 'newspaper', title: 'News', body: 'Modul utama untuk artikel, gambar, dan video publikasi.' },
  { icon: 'group', title: 'User & Role', body: 'Kelola akses admin dan operator aplikasi.' },
  { icon: 'settings', title: 'Pengaturan', body: 'Atur profil sistem dan preferensi aplikasi.' },
]

const activeModules = ['News', 'Manajemen User', 'Manajemen Role', 'Profile', 'Pengaturan']

onMounted(async () => {
  try {
    const [all, published, draft, users] = await Promise.all([
      api.get('/news', { params: { per_page: 1 } }),
      api.get('/news', { params: { per_page: 1, status: 'Published' } }),
      api.get('/news', { params: { per_page: 1, status: 'Draft' } }),
      api.get('/users', { params: { per_page: 1 } }),
    ])

    stats.value[0].value = String(all.data.total ?? 0)
    stats.value[1].value = String(published.data.total ?? 0)
    stats.value[2].value = String(draft.data.total ?? 0)
    stats.value[3].value = String(users.data.total ?? 0)
  } catch {
    /* Keep zero values when API is unavailable. */
  }
})
</script>
