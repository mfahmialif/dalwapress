<template>
  <header class="fixed inset-x-0 top-0 z-50 px-4 pt-4 sm:px-6 lg:pt-6">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 rounded-[1.7rem] bg-white/90 px-5 py-3 shadow-[0_18px_54px_rgba(15,23,42,0.10)] ring-1 ring-white/70 backdrop-blur-xl lg:px-7">
      <router-link to="/" class="logo-pill group">
        <span class="logo-icon">
          <span class="material-symbols-outlined text-[22px]">auto_stories</span>
        </span>
        <span class="leading-tight">
          <span class="block text-base font-black text-white">UII Dalwa</span>
          <span class="block text-[10px] font-black uppercase tracking-wide text-sky-300">Press Publisher</span>
        </span>
      </router-link>

      <nav class="hidden items-center gap-7 text-[14px] font-bold text-slate-600 md:flex">
        <router-link v-for="item in items" :key="item.href" :to="item.href" class="nav-link" :class="{ active: activeSection === item.id }">
          {{ item.label }}
        </router-link>
      </nav>

      <router-link to="/login" class="hidden h-9 items-center gap-2 rounded-full px-3.5 text-sm font-black text-slate-700 transition hover:bg-slate-100 hover:text-[#0b1020] md:inline-flex">
        <span class="material-symbols-outlined text-[19px]">login</span>
        Sign In
      </router-link>

      <button @click="mobileOpen = !mobileOpen" class="flex size-10 items-center justify-center rounded-full bg-[#0b1020] text-white md:hidden">
        <span class="material-symbols-outlined">{{ mobileOpen ? 'close' : 'menu' }}</span>
      </button>
    </div>

    <Transition name="mobile-menu">
      <div v-if="mobileOpen" class="mx-auto mt-3 max-w-7xl rounded-3xl bg-white/95 p-4 shadow-2xl ring-1 ring-slate-200 backdrop-blur-xl md:hidden">
        <router-link v-for="item in items" :key="item.href" :to="item.href" @click="mobileOpen = false" class="block rounded-2xl px-4 py-3 font-bold text-slate-700 hover:bg-sky-50 hover:text-sky-700" :class="{ 'bg-sky-50 text-sky-700': activeSection === item.id }">
          {{ item.label }}
        </router-link>
        <router-link to="/login" class="mt-2 flex items-center justify-center gap-2 rounded-2xl bg-[#0b1020] px-4 py-3 font-black text-white">
          <span class="material-symbols-outlined text-[20px]">login</span>
          Sign In
        </router-link>
      </div>
    </Transition>
  </header>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  items: {
    type: Array,
    required: true,
  },
  activeSection: {
    type: String,
    default: 'home',
  },
})

const mobileOpen = ref(false)
</script>
