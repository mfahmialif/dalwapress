<template>
  <section class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
    <h2 class="text-xl font-black" style="color: var(--text-heading)">Profile Kepala Penulis</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
      <div v-for="item in info" :key="item.label" class="rounded-xl p-4" style="background: var(--bg-input); border: 1px solid var(--border)">
        <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">{{ item.label }}</p>
        <p class="mt-1 font-black" style="color: var(--text-heading)">{{ item.value }}</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../../axios'
const user = ref({})
const info = computed(() => [
  { label: 'Nama', value: user.value.name || '-' },
  { label: 'Username', value: user.value.username || '-' },
  { label: 'Email', value: user.value.email || '-' },
  { label: 'Role', value: user.value.role?.name || '-' },
])
onMounted(async () => { user.value = (await api.get('/kepala-penulis/profile')).data })
</script>
