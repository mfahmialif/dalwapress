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
            <img :src="bookCoverUrl(book)" :alt="book.title" />
            <span class="book-category-tag">{{ bookCategory(book) }}</span>
            <div class="book-cover-overlay">
              <div>
                <h3>{{ book.title }}</h3>
                <p>{{ bookAuthor(book) }}</p>
              </div>
              <span class="book-detail-link">
                <span>Detail</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </span>
            </div>
          </div>
        </router-link>
      </div>
    </section>
  </main>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import api from '../../../axios'
import { assetUrl, storageUrl } from '../../../utils/asset'
import './styles.css'

const books = ref([])
const categories = ref([])
const loading = ref(false)
const search = ref('')
const categoryId = ref('')
const sortBy = ref('published_at')
let timer = null

const fallbackCovers = [
  '/img/thumb1.jpg',
  '/img/thumb2.jpg',
  '/img/hero-bg.jpg',
  '/img/galeri-bg.jpg',
  '/img/agenda-bg.jpg',
  '/img/news/news1.jpg',
]

function bookCoverUrl(book) {
  if (book.image) return assetUrl(book.image)

  if (book.cover) {
    if (book.cover.startsWith('http') || book.cover.startsWith('/storage/')) {
      return assetUrl(book.cover)
    }
    return storageUrl(book.cover)
  }

  const fallbackIndex = Number(book.id || 0) % fallbackCovers.length
  return fallbackCovers[fallbackIndex]
}

function bookCategory(book) {
  return book.category?.name || book.category || 'Buku'
}

function bookAuthor(book) {
  return book.author?.name || book.author || '-'
}

function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchBooks, 300)
}

async function fetchBooks() {
  loading.value = true
  try {
    const params = { status: 'published', per_page: 30, sort_by: sortBy.value }
    if (search.value) params.search = search.value
    if (categoryId.value) params.category_id = categoryId.value
    const { data } = await api.get('/books', { params })
    books.value = data.data || []
  } finally {
    loading.value = false
    await nextTick()
    AOS.refreshHard()
  }
}

async function fetchCategories() {
  const { data } = await api.get('/book-categories', { params: { all: 1 } })
  categories.value = data || []
}

onMounted(async () => {
  AOS.init({
    duration: 780,
    easing: 'ease-out-cubic',
    once: true,
    offset: 80,
  })
  await Promise.all([fetchCategories(), fetchBooks()])
})
</script>
