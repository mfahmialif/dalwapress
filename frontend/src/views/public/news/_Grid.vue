<template>
  <section class="bg-[#f6f8f7] py-14">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <div v-if="loading" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" :key="i" class="news-card animate-pulse">
          <div class="h-56 rounded-[1.35rem] bg-slate-200"></div>
          <div class="p-5">
            <div class="h-4 w-24 rounded bg-slate-200"></div>
            <div class="mt-4 h-6 w-4/5 rounded bg-slate-200"></div>
            <div class="mt-3 h-4 w-full rounded bg-slate-200"></div>
          </div>
        </div>
      </div>

      <div v-else-if="items.length" class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <article v-for="(item, index) in items" :key="item.id" class="news-card group flex flex-col" data-aos="fade-up" :data-aos-delay="(index % 3) * 70" @click="$emit('open', item)">
          <div class="relative h-56 overflow-hidden rounded-[1.35rem] bg-slate-200 flex-shrink-0">
            <img :src="item.image" :alt="item.title" class="h-full w-full object-cover transition duration-700 group-hover:scale-110" />
            <div class="absolute inset-0 bg-linear-to-t from-[#06111f]/76 via-transparent to-transparent opacity-80"></div>
            <span class="news-badge absolute left-4 top-4" :class="categoryClass(item.categoryType)">{{ item.categoryName }}</span>
            <span v-if="item.categoryType === 'Video'" class="play-badge">
              <span class="material-symbols-outlined">play_arrow</span>
            </span>
          </div>
          <div class="p-5 flex flex-col flex-grow">
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-[0.16em] text-sky-700">
              <span class="material-symbols-outlined text-[17px]">schedule</span>
              {{ formatRelative(item.created_at) }}
            </div>
            <h2 class="mt-3 line-clamp-2 text-xl font-black leading-tight text-[#101418]">{{ item.title }}</h2>
            <p class="mt-3 line-clamp-3 leading-7 text-slate-600">{{ item.excerpt }}</p>
            <button class="mt-auto pt-4 inline-flex items-center gap-2 text-sm font-black text-sky-700" type="button">
              Baca selengkapnya
              <span class="material-symbols-outlined text-[18px] transition group-hover:translate-x-1">arrow_forward</span>
            </button>
          </div>
        </article>
      </div>

      <div v-else class="empty-news">
        <span class="material-symbols-outlined text-[56px] text-sky-500">article</span>
        <h2 class="mt-4 text-2xl font-black text-[#101418]">News belum ditemukan</h2>
        <p class="mt-2 text-slate-600">Coba ubah kata kunci atau kategori pencarian.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { categoryClass, formatRelative } from './utils'

defineProps({
  items: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['open'])
</script>
