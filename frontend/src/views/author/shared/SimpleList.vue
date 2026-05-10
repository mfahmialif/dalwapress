<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">{{ title }}</h2>
      <p class="text-sm" style="color: var(--text-muted)">Riwayat informasi author portal.</p>
    </div>

    <section class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
      <div v-if="loading" class="flex justify-center py-16">
        <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
      </div>
      <div v-else class="grid gap-3">
        <article v-for="(item, index) in items" :key="index" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
          <p class="font-black" style="color: var(--text-heading)">{{ item.message || item.label || item.title || 'Item' }}</p>
          <p v-if="item.title && item.label" class="mt-1 text-sm" style="color: var(--text-body)">{{ item.title }}</p>
          <p v-if="item.note" class="mt-1 text-sm" style="color: var(--text-muted)">{{ item.note }}</p>
          <p v-if="item.created_at" class="mt-2 text-xs font-bold" style="color: var(--text-muted)">{{ formatDate(item.created_at) }}</p>
        </article>
        <p v-if="!items.length" class="rounded-xl p-8 text-center text-sm" style="background: var(--bg-input); color: var(--text-muted)">{{ emptyText }}</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../../axios'

const props = defineProps({
  endpoint: { type: String, required: true },
  emptyText: { type: String, default: 'Belum ada data.' },
})

const route = useRoute()
const items = ref([])
const loading = ref(false)
const title = computed(() => route.meta.pageTitle || 'Data')

async function fetchItems() {
  loading.value = true
  const { data } = await api.get(props.endpoint)
  items.value = Array.isArray(data) ? data : (data.data || [])
  loading.value = false
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString('id-ID') : '-'
}

watch(() => props.endpoint, fetchItems)
onMounted(fetchItems)
</script>
