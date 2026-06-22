<template>
  <section class="news-hero pt-32">
    <div class="mx-auto grid max-w-7xl gap-10 px-5 pb-12 pt-10 lg:grid-cols-[0.9fr_1.1fr] lg:px-8 lg:pt-16">
      <div data-aos="fade-right">
        <p class="news-kicker">
          <span class="size-2.5 rounded-full bg-sky-600"></span>
          UII DALWA PRESS NEWSROOM
        </p>
        <h1 class="mt-6 max-w-3xl text-5xl font-black leading-[1.02] tracking-tight text-[#101418] md:text-7xl">
          Kabar akademik, publikasi, dan literasi kampus.
        </h1>
        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
          Ikuti berita terbaru dari UII Dalwa Press: penerbitan buku, artikel editorial, kegiatan literasi, dan informasi layanan redaksi.
        </p>
      </div>

      <article v-if="featured" class="featured-card group" data-aos="fade-left" @click="$emit('open', featured)">
        <img :src="featured.image" :alt="featured.title" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105" />
        <div class="absolute inset-0 bg-linear-to-t from-[#06111f] via-[#06111f]/62 to-transparent"></div>
        <div class="relative z-10 mt-auto p-7 text-white">
          <div class="flex flex-wrap gap-1.5">
            <span v-for="category in visibleCategories(featured)" :key="category.id || category.name" class="news-badge news-badge--sm bg-white/12 text-sky-100 ring-white/15">
              {{ category.name }}
            </span>
            <span v-if="hiddenCategoryCount(featured)" class="news-badge news-badge--sm bg-white/12 text-sky-100 ring-white/15">
              +{{ hiddenCategoryCount(featured) }}
            </span>
          </div>
          <h2 class="mt-4 text-3xl font-black leading-tight">{{ featured.title }}</h2>
          <p class="mt-3 line-clamp-2 text-slate-200">{{ featured.excerpt }}</p>
          <div class="mt-5 flex items-center gap-3 text-sm font-bold text-sky-200">
            <span class="material-symbols-outlined text-[19px]">schedule</span>
            {{ formatRelative(featured.created_at) }}
            <span class="material-symbols-outlined text-[19px]">edit</span>
            {{ featured.authorName }}
          </div>
        </div>
      </article>

      <div v-else class="featured-card skeleton-card" data-aos="fade-left">
        <div class="relative z-10 mt-auto p-7">
          <div class="h-4 w-28 rounded-full bg-white/20"></div>
          <div class="mt-5 h-8 w-4/5 rounded-full bg-white/20"></div>
          <div class="mt-3 h-4 w-full rounded-full bg-white/15"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { formatRelative } from './utils'

defineProps({
  featured: {
    type: Object,
    default: null,
  },
})

defineEmits(['open'])

function categoryList(item) {
  return item?.categories?.length ? item.categories : (item?.category ? [item.category] : [])
}

function visibleCategories(item) {
  return categoryList(item).slice(0, 2)
}

function hiddenCategoryCount(item) {
  return Math.max(categoryList(item).length - 2, 0)
}
</script>
