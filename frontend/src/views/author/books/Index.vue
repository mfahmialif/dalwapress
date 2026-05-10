<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">My Published Books</h2>
      <p class="text-sm" style="color: var(--text-muted)">Buku terbit dari submission Anda.</p>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      <article v-for="book in books.data" :key="book.id" class="overflow-hidden rounded-2xl" style="background: var(--bg-card); border: 1px solid var(--border)">
        <img v-if="book.cover" :src="storageUrl(book.cover)" :alt="book.title" class="h-56 w-full object-cover" />
        <div v-else class="grid h-56 place-items-center" style="background: var(--bg-input); color: var(--text-muted)">
          <span class="material-symbols-outlined text-5xl">menu_book</span>
        </div>
        <div class="p-5">
          <h3 class="text-lg font-black" style="color: var(--text-heading)">{{ book.title }}</h3>
          <p class="mt-1 text-sm font-bold" style="color: var(--text-muted)">ISBN: {{ book.isbn || '-' }}</p>
          <p class="mt-1 text-sm" style="color: var(--text-muted)">Publish: {{ formatDate(book.published_at) }}</p>
          <div class="mt-4 flex flex-wrap gap-2 text-sm font-black" style="color: var(--text-body)">
            <span>{{ book.views || 0 }} views</span>
            <span>{{ book.downloads || 0 }} downloads</span>
          </div>
          <router-link class="btn-primary mt-4" :to="`/books/${book.id}`">Link Detail Buku</router-link>
        </div>
      </article>
    </div>
    <p v-if="!books.data?.length" class="rounded-2xl p-8 text-center text-sm" style="background: var(--bg-card); border: 1px solid var(--border); color: var(--text-muted)">Belum ada buku yang publish.</p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const books = ref({ data: [] })

function formatDate(value) {
  return value ? new Date(value).toLocaleDateString('id-ID') : '-'
}

onMounted(async () => {
  const { data } = await api.get('/author/books')
  books.value = data
})
</script>

<style scoped>
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.7rem 0.9rem; font-weight: 900; background: var(--color-accent); color: var(--text-btn); }
</style>
