<template>
  <header class="header flex h-16 shrink-0 items-center justify-between px-4 sm:px-6 transition-colors duration-300">
    <div class="flex items-center gap-3">
      <button @click="$emit('toggle-sidebar')" class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors cursor-pointer lg:hidden" style="color: var(--text-muted)">
        <span class="material-symbols-outlined text-[24px]">menu</span>
      </button>
      <h1 class="text-base sm:text-lg font-bold truncate max-w-[200px] sm:max-w-none" style="color: var(--text-heading)">{{ pageTitle }}</h1>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
      <button @click="$emit('toggle-theme')" class="theme-toggle-btn flex h-9 w-9 items-center justify-center rounded-lg transition-all cursor-pointer" :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
        <span class="material-symbols-outlined text-[20px]">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
      </button>

      <div class="h-6 w-px bg-border hidden sm:block"></div>

      <div class="flex items-center gap-3">
        <div class="h-8 w-8 rounded-full bg-accent flex items-center justify-center text-btn font-bold text-sm">
          {{ userInitial }}
        </div>
        <span class="text-sm font-semibold hidden md:inline-block" style="color: var(--text-heading)">{{ userName }}</span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../../../stores/auth'

defineProps({
  pageTitle: { type: String, default: 'Dashboard Desain Cover' },
  isDark: { type: Boolean, default: true },
})
defineEmits(['toggle-theme', 'toggle-sidebar'])

const authStore = useAuthStore()
const userName = computed(() => authStore.user?.name || 'Desainer Sampul')
const userInitial = computed(() => (userName.value.charAt(0) || 'C').toUpperCase())
</script>

<style scoped>
.header{background:var(--bg-header);border-bottom:1px solid var(--border)}.theme-toggle-btn{color:var(--text-muted);background:var(--bg-input)}.theme-toggle-btn:hover{color:var(--color-accent)}.text-btn{color:var(--text-btn)}
</style>
