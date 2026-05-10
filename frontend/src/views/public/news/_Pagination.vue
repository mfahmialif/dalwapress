<template>
  <div v-if="totalPages > 1" class="flex justify-center bg-[#f6f8f7] pb-16">
    <div class="pagination-shell">
      <button @click="$emit('page', currentPage - 1)" :disabled="currentPage <= 1" class="page-arrow" type="button">
        <span class="material-symbols-outlined">chevron_left</span>
      </button>
      <button
        v-for="page in pageNumbers"
        :key="page"
        @click="typeof page === 'number' && $emit('page', page)"
        class="page-number"
        :class="{ active: page === currentPage, muted: page === '...' }"
        type="button"
      >
        {{ page }}
      </button>
      <button @click="$emit('page', currentPage + 1)" :disabled="currentPage >= totalPages" class="page-arrow" type="button">
        <span class="material-symbols-outlined">chevron_right</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  totalPages: {
    type: Number,
    required: true,
  },
})

defineEmits(['page'])

const pageNumbers = computed(() => {
  const pages = []
  for (let page = 1; page <= props.totalPages; page += 1) {
    if (page === 1 || page === props.totalPages || (page >= props.currentPage - 1 && page <= props.currentPage + 1)) {
      pages.push(page)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }
  return pages
})
</script>
