<template>
  <section class="sticky top-24 z-30 mx-auto max-w-7xl px-5 lg:px-8">
    <div class="news-toolbar">
      <div class="relative min-w-0 flex-1">
        <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
        <input
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          class="h-12 w-full rounded-full border border-slate-200 bg-white pl-12 pr-4 text-sm font-bold text-slate-700 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
          placeholder="Cari news, artikel, atau publikasi..."
        />
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          @click="$emit('category', tab.value)"
          class="toolbar-pill"
          :class="{ active: category === tab.value }"
          type="button"
        >
          <span class="material-symbols-outlined text-[18px]">{{ tab.icon }}</span>
          {{ tab.label }}
        </button>
      </div>

      <select
        :value="sortBy"
        @change="$emit('sort', $event.target.value)"
        class="h-12 rounded-full border border-slate-200 bg-white px-4 text-sm font-black text-slate-700 outline-none transition focus:border-sky-300 focus:ring-4 focus:ring-sky-100"
      >
        <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
      </select>
    </div>
  </section>
</template>

<script setup>
import { filterTabs, sortOptions } from './utils'

defineProps({
  search: {
    type: String,
    default: '',
  },
  category: {
    type: String,
    default: 'all',
  },
  sortBy: {
    type: String,
    default: 'created_at',
  },
  tabs: {
    type: Array,
    default: () => filterTabs,
  },
})

defineEmits(['update:search', 'category', 'sort'])

</script>
