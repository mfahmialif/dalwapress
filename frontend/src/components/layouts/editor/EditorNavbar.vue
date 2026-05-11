<template>
  <header class="top-header flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4 shrink-0 z-10" :class="layoutMode === 'horizontal' ? 'horiz-mode' : ''">
    <div class="flex items-center gap-3">
      <button @click="$emit('toggle-sidebar')" class="hamburger-btn p-2 rounded-lg lg:hidden"><span class="material-symbols-outlined text-[24px]">menu</span></button>
      <h2 class="text-lg sm:text-xl font-bold tracking-tight" style="color: var(--text-heading)">{{ pageTitle }}</h2>
    </div>
    <div class="flex items-center gap-2 sm:gap-4">
      <router-link to="/editor/submissions" class="hidden sm:flex items-center gap-2 rounded-full bg-accent px-4 py-2 text-sm font-black" style="color: var(--text-btn)"><span class="material-symbols-outlined text-[18px]">rate_review</span>Review</router-link>
      <button @click="$emit('toggle-theme')" class="theme-toggle p-2 rounded-full"><span class="material-symbols-outlined text-[22px]">{{ isDark ? 'light_mode' : 'dark_mode' }}</span></button>
      <button @click="$emit('toggle-layout')" class="theme-toggle p-2 rounded-full"><span class="material-symbols-outlined text-[22px]">{{ layoutMode === 'vertical' ? 'horizontal_distribute' : 'vertical_distribute' }}</span></button>
      <router-link to="/editor/profile" class="avatar-ring flex h-10 w-10 items-center justify-center rounded-full border-2 border-accent"><span class="material-symbols-outlined text-accent">person</span></router-link>
    </div>
  </header>
</template>
<script setup>
defineProps({ pageTitle: { type: String, default: 'Dashboard Editor' }, isDark: { type: Boolean, default: true }, layoutMode: { type: String, default: 'vertical' } })
defineEmits(['toggle-theme', 'toggle-sidebar', 'toggle-layout'])
</script>
<style scoped>
.top-header{background:var(--bg-header);backdrop-filter:blur(12px)}.top-header.horiz-mode{background:var(--bg-card);border-bottom:1px solid var(--border)}.theme-toggle,.avatar-ring{background:var(--toggle-bg);color:var(--toggle-text)}.theme-toggle:hover{background:var(--color-accent);color:var(--text-btn);box-shadow:0 0 20px rgba(37, 99, 235,.4)}.hamburger-btn{color:var(--text-heading)}.hamburger-btn:hover{background:var(--bg-input);color:var(--color-accent)}
</style>
