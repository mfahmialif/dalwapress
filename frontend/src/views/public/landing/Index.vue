<template>
  <main>
    <Hero />
    <Books :books="books" />
    <Services :services="services" />
    <Supporter :supporters="supporters" />
    <NewsUpdate :updates="latestNews" />
    <About :stats="stats" />
    <PublishCta />
    <Strengths :strengths="strengths" />
    <Flow :publishing-flow="publishingFlow" />
    <Testimonials :testimonials="testimonials" />
    <Contact />
  </main>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import './styles.css'
import api from '../../../axios'
import { assetUrl, storageUrl } from '../../../utils/asset'
import About from './_About.vue'
import Books from './_Books.vue'
import Contact from './_Contact.vue'
import Flow from './_Flow.vue'
import Hero from './_Hero.vue'
import NewsUpdate from './_NewsUpdate.vue'
import PublishCta from './_PublishCta.vue'
import Services from './_Services.vue'
import Strengths from './_Strengths.vue'
import Supporter from './_Supporter.vue'
import Testimonials from './_Testimonials.vue'
import {
  publishingFlow,
  services,
  stats,
  strengths,
  supporters,
  testimonials,
  updates,
} from './data'
import { normalizeNews } from '../news/utils'

const books = ref([])
const latestNews = ref(updates)
const fallbackCovers = [
  '/img/thumb1.jpg',
  '/img/thumb2.jpg',
  '/img/hero-bg.jpg',
  '/img/galeri-bg.jpg',
  '/img/agenda-bg.jpg',
  '/img/news/news1.jpg',
]

function bookCoverUrl(book) {
  if (book.cover_url) return book.cover_url
  if (book.cover) {
    if (book.cover.startsWith('http') || book.cover.startsWith('/')) {
      return assetUrl(book.cover)
    }
    return storageUrl(book.cover)
  }

  return fallbackCovers[Number(book.id || 0) % fallbackCovers.length]
}

async function fetchBooks() {
  try {
    const { data } = await api.get('/books', {
      params: {
        status: 'published',
        per_page: 8,
        sort_by: 'published_at',
      },
    })

    books.value = (data.data || []).map((book) => ({
      id: book.id,
      image: bookCoverUrl(book),
      category: book.category?.name || 'Buku',
      title: book.title,
      author: book.author?.name || '-',
    }))
  } catch {
    books.value = []
  }
}

function formatNewsDate(dateStr) {
  if (!dateStr) return { day: '', month: '' }
  const date = new Date(dateStr)
  if (Number.isNaN(date.getTime())) return { day: '', month: '' }

  return {
    day: new Intl.DateTimeFormat('id-ID', { day: '2-digit' }).format(date),
    month: new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(date),
  }
}

async function fetchLatestNews() {
  try {
    const { data } = await api.get('/news', {
      params: {
        status: 'Published',
        per_page: 3,
        sort_by: 'created_at',
        sort_dir: 'desc',
      },
    })

    const items = (data.data || []).map((item) => {
      const normalized = normalizeNews(item)
      const date = formatNewsDate(normalized.created_at)

      return {
        id: normalized.id,
        day: date.day,
        month: date.month,
        category: normalized.categoryName,
        title: normalized.title,
        body: normalized.excerpt,
      }
    })

    latestNews.value = items.length ? items : updates
  } catch {
    latestNews.value = updates
  }
}

onMounted(async () => {
  AOS.init({
    duration: 850,
    easing: 'ease-out-cubic',
    once: true,
    offset: 90,
  })
  await Promise.all([fetchBooks(), fetchLatestNews()])
  await nextTick()
  AOS.refreshHard()
})
</script>
