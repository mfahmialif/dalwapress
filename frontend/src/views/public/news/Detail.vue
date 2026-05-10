<template>
  <main class="pt-32">
    <section v-if="loading" class="mx-auto max-w-5xl px-5 py-24 text-center lg:px-8">
      <div class="mx-auto size-16 animate-spin rounded-full border-4 border-sky-100 border-t-sky-600"></div>
      <p class="mt-5 font-bold text-slate-500">Memuat news...</p>
    </section>

      <section v-else-if="error" class="mx-auto max-w-3xl px-5 py-24 text-center lg:px-8">
        <span class="material-symbols-outlined text-[64px] text-red-500">error</span>
        <h1 class="mt-4 text-3xl font-black text-[#101418]">News tidak ditemukan</h1>
        <p class="mt-3 text-slate-600">{{ error }}</p>
        <router-link to="/news" class="cta-primary mt-7">
          Kembali ke News
          <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </router-link>
      </section>

      <template v-else-if="newsItem">
        <article>
          <header class="mx-auto max-w-5xl px-5 pb-10 pt-8 text-center lg:px-8" data-aos="fade-up">
            <router-link to="/news" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-black text-slate-600 shadow-sm ring-1 ring-slate-200 transition hover:-translate-y-0.5 hover:text-sky-700">
              <span class="material-symbols-outlined text-[18px]">arrow_back</span>
              Kembali ke News
            </router-link>
            <div class="mt-8 flex justify-center">
              <span class="news-badge" :class="categoryClass(newsItem.categoryType)">{{ newsItem.categoryName }}</span>
            </div>
            <h1 class="mx-auto mt-5 max-w-4xl text-4xl font-black leading-tight tracking-tight text-[#101418] md:text-6xl">
              {{ newsItem.title }}
            </h1>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-sm font-bold text-slate-500">
              <span class="inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-sky-600">calendar_month</span>
                {{ formatDate(newsItem.created_at, { weekday: 'long' }) }}
              </span>
              <span class="inline-flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-sky-600">schedule</span>
                {{ formatRelative(newsItem.created_at) }}
              </span>
            </div>
          </header>

          <div class="mx-auto max-w-6xl px-5 lg:px-8" data-aos="zoom-in">
            <div class="detail-media">
              <video v-if="newsItem.categoryType === 'Video' && newsItem.video" :src="newsItem.video" controls class="h-full w-full object-cover"></video>
              <img v-else :src="newsItem.image" :alt="newsItem.title" class="h-full w-full object-cover" />
            </div>
          </div>

          <div class="mx-auto grid max-w-6xl gap-8 px-5 py-14 lg:grid-cols-[1fr_18rem] lg:px-8">
            <div class="article-body" data-aos="fade-up">
              <div v-if="newsItem.bodyHtml" v-html="newsItem.bodyHtml"></div>
              <p v-else>Konten news belum tersedia.</p>
            </div>

            <aside class="detail-sidebar" data-aos="fade-left">
              <div class="sidebar-card">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-700">Ringkasan</p>
                <p class="mt-3 leading-7 text-slate-600">{{ newsItem.excerpt }}</p>
              </div>
              <div class="sidebar-card">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-700">Bagikan</p>
                <div class="mt-4 grid gap-2">
                  <button @click="copyLink" class="share-button" type="button">
                    <span class="material-symbols-outlined text-[19px]">link</span>
                    {{ copied ? 'Link tersalin' : 'Salin link' }}
                  </button>
                  <a :href="`https://wa.me/?text=${encodedShareText}`" target="_blank" rel="noopener" class="share-button">
                    <span class="material-symbols-outlined text-[19px]">chat</span>
                    WhatsApp
                  </a>
                </div>
              </div>
            </aside>
          </div>
        </article>

        <section v-if="relatedNews.length" class="bg-white py-16">
          <div class="mx-auto max-w-6xl px-5 lg:px-8">
            <div class="mb-8 flex items-end justify-between gap-5">
              <div>
                <p class="news-kicker compact">
                  <span class="size-2.5 rounded-full bg-sky-600"></span>
                  Baca juga
                </p>
                <h2 class="mt-3 text-3xl font-black text-[#101418]">News terkait</h2>
              </div>
              <router-link to="/news" class="text-sm font-black text-sky-700">Semua News</router-link>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
              <article v-for="item in relatedNews" :key="item.id" class="news-card group" @click="openDetail(item)">
                <div class="relative h-40 overflow-hidden rounded-[1.25rem]">
                  <img :src="item.image" :alt="item.title" class="h-full w-full object-cover transition duration-700 group-hover:scale-110" />
                </div>
                <div class="p-5">
                  <span class="news-badge" :class="categoryClass(item.categoryType)">{{ item.categoryName }}</span>
                  <h3 class="mt-3 line-clamp-2 text-lg font-black text-[#101418]">{{ item.title }}</h3>
                </div>
              </article>
            </div>
          </div>
        </section>

        <section class="bg-[#f6f8f7] py-16">
          <div class="mx-auto max-w-5xl px-5 lg:px-8">
            <Comments :news-id="newsItem.id" />
          </div>
        </section>
      </template>
  </main>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AOS from 'aos'
import 'aos/dist/aos.css'
import './styles.css'
import api from '../../../axios'
import Comments from './_Comments.vue'
import { categoryClass, formatDate, formatRelative, normalizeNews } from './utils'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const error = ref('')
const newsItem = ref(null)
const relatedNews = ref([])
const copied = ref(false)

const encodedShareText = computed(() => encodeURIComponent(`${newsItem.value?.title || 'UII Dalwa Press News'} ${window.location.href}`))

async function loadDetail() {
  loading.value = true
  error.value = ''
  copied.value = false

  try {
    const { data } = await api.get(`/news/${route.params.id}`)
    newsItem.value = normalizeNews(data.data || data)
    await loadRelated()
  } catch (err) {
    console.error(err)
    error.value = err.response?.status === 404
      ? 'News tidak ditemukan atau sudah dihapus.'
      : 'Terjadi kesalahan saat memuat news.'
  } finally {
    loading.value = false
  }
}

async function loadRelated() {
  try {
    const { data } = await api.get('/news', {
      params: {
        per_page: 4,
        status: 'Published',
        category: newsItem.value?.category?.slug,
        sort_by: 'created_at',
        sort_dir: 'desc',
      },
    })
    relatedNews.value = (data.data || [])
      .filter((item) => String(item.id) !== String(route.params.id))
      .slice(0, 3)
      .map(normalizeNews)
  } catch {
    relatedNews.value = []
  }
}

async function copyLink() {
  try {
    await navigator.clipboard.writeText(window.location.href)
    copied.value = true
    window.setTimeout(() => {
      copied.value = false
    }, 1800)
  } catch {
    copied.value = false
  }
}

function openDetail(item) {
  router.push({ name: 'DetailNews', params: { id: item.id } })
}

onMounted(() => {
  AOS.init({ duration: 780, easing: 'ease-out-cubic', once: true, offset: 80 })
  loadDetail()
})

watch(() => route.params.id, () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
  loadDetail()
})
</script>
