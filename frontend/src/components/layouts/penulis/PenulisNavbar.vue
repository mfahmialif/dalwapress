<template>
  <header class="top-header flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4 shrink-0 z-10">
    <div class="flex items-center gap-3">
      <button @click="$emit('toggle-sidebar')" class="hamburger-btn p-2 rounded-lg lg:hidden"><span class="material-symbols-outlined text-[24px]">menu</span></button>
      <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--text-heading)">{{ pageTitle }}</h2>
    </div>
    <div class="flex items-center gap-2 sm:gap-4">
      <button @click="searchOpen = true" class="theme-toggle p-2 rounded-full" type="button" title="Search"><span class="material-symbols-outlined text-[22px]">search</span></button>
      <router-link to="/penulis/news/create" class="hidden sm:flex items-center gap-2 rounded-full bg-accent px-4 py-2 text-sm font-black" style="color: var(--text-btn)"><span class="material-symbols-outlined text-[18px]">add_circle</span>Tulis</router-link>
      <button @click="$emit('toggle-theme')" class="theme-toggle p-2 rounded-full"><span class="material-symbols-outlined text-[22px]">{{ isDark ? 'light_mode' : 'dark_mode' }}</span></button>
      <router-link to="/penulis/profile" class="avatar-ring flex h-10 w-10 items-center justify-center rounded-full border-2 border-accent"><span class="material-symbols-outlined text-accent">person</span></router-link>
    </div>
    <DashboardSearchOverlay :open="searchOpen" portal="penulis" @close="searchOpen = false" />
  </header>
</template>
<script setup>
import { ref } from 'vue'
import DashboardSearchOverlay from '../shared/DashboardSearchOverlay.vue'

defineProps({ pageTitle: { type: String, default: 'Dashboard Penulis' }, isDark: { type: Boolean, default: true } })
defineEmits(['toggle-theme', 'toggle-sidebar'])

const searchOpen = ref(false)
</script>
<style scoped>
.top-header{background:var(--bg-header);backdrop-filter:blur(12px)}.theme-toggle,.avatar-ring{background:var(--toggle-bg);color:var(--toggle-text)}.theme-toggle:hover{background:var(--color-accent);color:var(--text-btn);box-shadow:0 0 20px rgba(37,99,235,.4)}.hamburger-btn{color:var(--text-heading)}.hamburger-btn:hover{background:var(--bg-input);color:var(--color-accent)}
</style>
