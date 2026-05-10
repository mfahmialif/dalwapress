<template>
  <main class="books-page">
    <section class="books-hero">
      <div class="books-shell">
        <span class="books-pill" data-aos="fade-up">
          <span class="material-symbols-outlined text-[18px]">auto_stories</span>
          Katalog UII Dalwa Press
        </span>
        <h1 class="books-title" data-aos="fade-up" data-aos-delay="80">Karya akademik yang rapi, mudah dicari, dan siap dibaca.</h1>
        <p class="books-copy" data-aos="fade-up" data-aos-delay="140">
          Temukan buku, prosiding, dan publikasi resmi UII Dalwa Press dari penulis kampus dan mitra akademik.
        </p>
      </div>
    </section>

    <section class="books-shell">
      <div class="books-toolbar" data-aos="fade-up">
        <input v-model="search" @input="debouncedFetch" class="books-input" placeholder="Cari buku, ISBN, atau author..." />
        <select v-model="categoryId" @change="fetchBooks" class="books-input">
          <option value="">Semua kategori</option>
          <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
        </select>
        <select v-model="sortBy" @change="fetchBooks" class="books-input">
          <option value="published_at">Terbaru</option>
          <option value="title">Judul</option>
          <option value="year">Tahun</option>
          <option value="views">Populer</option>
        </select>
      </div>

      <div v-if="loading" class="flex justify-center py-16">
        <span class="material-symbols-outlined animate-spin text-4xl text-sky-600">progress_activity</span>
      </div>

      <div v-else class="book-grid">
        <router-link v-for="book in books" :key="book.id" :to="`/books/${book.id}`" class="book-card" data-aos="fade-up">
          <div class="book-cover">
            <img v-if="book.cover" :src="storageUrl(book.cover)" :alt="book.title" />
            <div v-else class="book-cover-fallback"><span class="material-symbols-outlined text-[42px]">menu_book</span></div>
          </div>
          <div>
            <p class="book-meta">{{ book.category?.name || 'Buku' }} · {{ book.year || '-' }}</p>
            <h3>{{ book.title }}</h3>
            <p class="book-meta mt-2">{{ book.author?.name || '-' }}</p>
            <p class="book-description line-clamp-3">{{ book.description || 'Deskripsi buku belum tersedia.' }}</p>
            <span class="book-action">Detail <span class="material-symbols-outlined text-[18px]">arrow_forward</span></span>
          </div>
        </router-link>
      </div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AOS from 'aos'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import './styles.css'

const books = ref([])
const categories = ref([])
const loading = ref(false)
const search = ref('')
const categoryId = ref('')
const sortBy = ref('published_at')
let timer = null

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchBooks, 300)
}

async function fetchBooks() {
  loading.value = true
  const params = { status: 'published', per_page: 30, sort_by: sortBy.value }
  if (search.value) params.search = search.value
  if (categoryId.value) params.category_id = categoryId.value
  const { data } = await api.get('/books', { params })
  books.value = data.data || []
  loading.value = false
}

async function fetchCategories() {
  const { data } = await api.get('/book-categories', { params: { all: 1 } })
  categories.value = data || []
}

onMounted(async () => {
  AOS.refresh()
  await fetchCategories()
  await fetchBooks()
})
</script>
