<template>
  <main>
    <NewsHero :featured="featuredNews" @open="openDetail" />
      <NewsToolbar
        v-model:search="searchQuery"
        :category="activeCategory"
        :sort-by="sortBy"
        :tabs="categoryTabs"
        @category="setCategory"
        @sort="setSort"
      />
    <NewsGrid :items="gridItems" :loading="loading" @open="openDetail" />
    <NewsPagination :current-page="currentPage" :total-pages="totalPages" @page="goToPage" />
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import AOS from 'aos'
import 'aos/dist/aos.css'
import './styles.css'
import api from '../../../axios'
import NewsGrid from './_Grid.vue'
import NewsHero from './_Hero.vue'
import NewsPagination from './_Pagination.vue'
import NewsToolbar from './_Toolbar.vue'
import { normalizeNews } from './utils'

const router = useRouter()
const newsItems = ref([])
const loading = ref(false)
const searchQuery = ref('')
const activeCategory = ref('all')
const categoryTabs = ref([{ value: 'all', label: 'Semua', icon: 'dashboard' }])
const sortBy = ref('created_at')
const sortDir = ref('desc')
const currentPage = ref(1)
const totalPages = ref(1)
let searchTimer = null

const featuredNews = computed(() => newsItems.value[0] || null)
const gridItems = computed(() => newsItems.value.slice(featuredNews.value ? 1 : 0))

async function loadNews() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: 10,
      status: 'Published',
      sort_by: sortBy.value,
      sort_dir: sortDir.value,
    }

    if (activeCategory.value !== 'all') params.category_id = activeCategory.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()

    const { data } = await api.get('/news', { params })
    newsItems.value = (data.data || []).map(normalizeNews)
    totalPages.value = data.last_page || 1
  } catch (error) {
    console.error(error)
    newsItems.value = []
    totalPages.value = 1
  } finally {
    loading.value = false
  }
}

function setCategory(category) {
  activeCategory.value = category
  currentPage.value = 1
  loadNews()
}

function setSort(value) {
  if (sortBy.value === value) {
    sortDir.value = sortDir.value === 'desc' ? 'asc' : 'desc'
  } else {
    sortBy.value = value
    sortDir.value = value === 'created_at' ? 'desc' : 'asc'
  }
  currentPage.value = 1
  loadNews()
}

function goToPage(page) {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadNews()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function openDetail(item) {
  router.push({ name: 'DetailNews', params: { id: item.id } })
}

watch(searchQuery, () => {
  window.clearTimeout(searchTimer)
  searchTimer = window.setTimeout(() => {
    currentPage.value = 1
    loadNews()
  }, 350)
})

onMounted(() => {
  AOS.init({ duration: 780, easing: 'ease-out-cubic', once: true, offset: 80 })
  loadCategories()
  loadNews()
})

async function loadCategories() {
  try {
    const { data } = await api.get('/news-categories')
    categoryTabs.value = [
      { value: 'all', label: 'Semua', icon: 'dashboard' },
      ...(data || []).map((category) => ({
        value: category.id,
        label: category.name,
        icon: category.type === 'Video' ? 'play_circle' : category.type === 'Gambar' ? 'image' : 'article',
      })),
    ]
  } catch {
    categoryTabs.value = [{ value: 'all', label: 'Semua', icon: 'dashboard' }]
  }
}
</script>
