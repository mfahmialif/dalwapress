<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">Dashboard Penulis</h2>
      <p class="text-sm" style="color: var(--text-muted)">Ringkasan artikel berita yang Anda tulis.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
      <article v-for="item in cards" :key="item.label" class="stat-card rounded-2xl border p-5" style="border-color: var(--border)">
        <span class="material-symbols-outlined text-accent">{{ item.icon }}</span>
        <p class="mt-4 text-sm font-bold" style="color: var(--text-muted)">{{ item.label }}</p>
        <strong class="mt-1 block text-3xl font-black" style="color: var(--text-heading)">{{ item.value }}</strong>
      </article>
    </div>
    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg font-black" style="color: var(--text-heading)">Artikel Terbaru</h3>
        <router-link to="/penulis/news" class="rounded-lg px-3 py-2 text-sm font-bold text-accent hover:bg-white/5">Lihat Semua</router-link>
      </div>
      <div class="mt-4 grid gap-3">
        <router-link v-for="item in latest" :key="item.id" :to="`/penulis/news/${item.id}/edit`" class="rounded-xl p-4 transition hover:bg-white/5" style="background: var(--bg-input); border: 1px solid var(--border)">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="font-black" style="color: var(--text-heading)">{{ item.title }}</p>
              <p class="text-sm" style="color: var(--text-muted)">{{ categoryNames(item) || '-' }}</p>
            </div>
            <span :class="statusBadge(item.status)">{{ item.status }}</span>
          </div>
        </router-link>
        <p v-if="!latest.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">Belum ada artikel.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../../axios'

const data = ref({ stats: {}, latest_news: [] })
const latest = computed(() => data.value.latest_news || [])
const cards = computed(() => [
  { icon: 'article', label: 'Total Artikel', value: data.value.stats?.total || 0 },
  { icon: 'task_alt', label: 'Published', value: data.value.stats?.published || 0 },
  { icon: 'draft', label: 'Draft', value: data.value.stats?.draft || 0 },
])
function statusBadge(status) {
  const base = 'app-badge'
  return status === 'Published' ? `${base} app-badge--success` : `${base} app-badge--blue`
}
function categoryNames(item){
  const list = item.categories?.length ? item.categories : (item.category ? [item.category] : [])
  return list.map(category => category.name).join(', ')
}
onMounted(async () => { data.value = (await api.get('/penulis/dashboard')).data })
</script>
