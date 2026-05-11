<template>
  <main class="books-page">
    <section class="book-detail-hero">
      <div class="books-shell">
        <div v-if="loading" class="flex justify-center py-16">
          <span class="material-symbols-outlined animate-spin text-4xl text-sky-600">progress_activity</span>
        </div>
        <div v-else-if="error" class="book-empty-state" data-aos="fade-up">
          <span class="material-symbols-outlined">menu_book</span>
          <h1>Buku tidak ditemukan</h1>
          <p>{{ error }}</p>
          <router-link to="/books" class="book-btn secondary">Kembali ke Katalog</router-link>
        </div>
        <article v-else-if="book" class="book-detail-card" data-aos="fade-up">
          <div class="book-detail-cover">
            <img v-if="bookCoverUrl" :src="bookCoverUrl" :alt="book.title" />
            <div v-else class="book-cover-fallback"><span class="material-symbols-outlined text-[58px]">menu_book</span></div>
          </div>
          <div class="book-detail-content">
            <span class="books-pill">{{ bookCategory }}</span>
            <h1 class="book-detail-title mt-5">{{ book.title }}</h1>
            <p class="book-detail-author mt-4 text-lg font-bold">{{ bookAuthor }}</p>
            <p class="book-detail-description mt-3 max-w-2xl leading-8">{{ book.description || 'Deskripsi buku belum tersedia.' }}</p>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <Meta label="ISBN" :value="book.isbn || '-'" />
              <Meta label="Tahun" :value="String(book.year || '-')" />
              <Meta label="Halaman" :value="book.pages ? `${book.pages} hlm` : '-'" />
              <Meta label="Bahasa" :value="book.language || '-'" />
            </div>
            <div class="book-detail-actions">
              <a v-if="book.preview_file" :href="fileUrl(book.preview_file)" target="_blank" class="book-btn secondary">
                <span class="material-symbols-outlined">visibility</span>
                Preview
              </a>
              <a v-if="book.full_file" :href="`/api/books/${book.id}/download`" class="book-btn">
                <span class="material-symbols-outlined">download</span>
                Download
              </a>
              <router-link to="/submissions" class="book-btn secondary">
                <span class="material-symbols-outlined">upload_file</span>
                Kirim Naskah
              </router-link>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section v-if="book" class="book-section">
      <div class="books-shell book-prose">
        <div>
          <h2>Tentang Buku</h2>
          <p class="mt-3 whitespace-pre-line">{{ book.description || '-' }}</p>
        </div>
        <div>
          <h2>Daftar Isi</h2>
          <p class="mt-3 whitespace-pre-line">{{ book.table_of_contents || '-' }}</p>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { computed, defineComponent, h, nextTick, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AOS from 'aos'
import api from '../../../axios'
import { assetUrl, storageUrl } from '../../../utils/asset'
import './styles.css'

const Meta = defineComponent({
  props: { label: String, value: String },
  setup(props) {
    return () => h('div', { class: 'book-meta' }, [
      h('p', { class: 'book-meta-label' }, props.label),
      h('p', { class: 'book-meta-value' }, props.value),
    ])
  },
})

const route = useRoute()
const book = ref(null)
const loading = ref(false)
const error = ref('')

const bookCoverUrl = computed(() => {
  if (book.value?.cover_url) return book.value.cover_url
  if (book.value?.cover) return fileUrl(book.value.cover)
  return ''
})

const bookCategory = computed(() => {
  const category = book.value?.category
  return typeof category === 'string' ? category : category?.name || 'Buku'
})

const bookAuthor = computed(() => {
  const author = book.value?.author
  return typeof author === 'string' ? author : author?.name || '-'
})

function fileUrl(path) {
  if (!path) return ''
  if (path.startsWith('http') || path.startsWith('/')) return assetUrl(path)
  return storageUrl(path)
}

async function loadBook() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get(`/books/${route.params.id}`)
    book.value = data
  } catch {
    book.value = null
    error.value = 'Buku tidak ditemukan.'
  } finally {
    loading.value = false
    await nextTick()
    AOS.refreshHard()
  }
}

onMounted(async () => {
  AOS.init({ duration: 780, easing: 'ease-out-cubic', once: true, offset: 80 })
  await loadBook()
})
</script>
