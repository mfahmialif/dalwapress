<template>
  <main class="books-page">
    <section class="book-detail-hero">
      <div class="books-shell">
        <div v-if="loading" class="flex justify-center py-16">
          <span class="material-symbols-outlined animate-spin text-4xl text-sky-600">progress_activity</span>
        </div>
        <article v-else-if="book" class="book-detail-card" data-aos="fade-up">
          <div class="book-detail-cover">
            <img v-if="book.cover" :src="storageUrl(book.cover)" :alt="book.title" />
            <div v-else class="book-cover-fallback"><span class="material-symbols-outlined text-[58px]">menu_book</span></div>
          </div>
          <div>
            <span class="books-pill">{{ book.category?.name || 'Buku' }}</span>
            <h1 class="book-detail-title mt-5">{{ book.title }}</h1>
            <p class="mt-4 text-lg font-bold text-slate-600">{{ book.author?.name || '-' }}</p>
            <p class="mt-3 max-w-2xl leading-8 text-slate-600">{{ book.description || 'Deskripsi buku belum tersedia.' }}</p>
            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
              <Meta label="ISBN" :value="book.isbn || '-'" />
              <Meta label="Tahun" :value="String(book.year || '-')" />
              <Meta label="Halaman" :value="book.pages ? `${book.pages} hlm` : '-'" />
              <Meta label="Bahasa" :value="book.language || '-'" />
            </div>
            <div class="book-detail-actions">
              <a v-if="book.preview_file" :href="storageUrl(book.preview_file)" target="_blank" class="book-btn secondary">
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
          <h2 class="text-2xl font-black text-slate-950">Tentang Buku</h2>
          <p class="mt-3 whitespace-pre-line">{{ book.description || '-' }}</p>
        </div>
        <div>
          <h2 class="text-2xl font-black text-slate-950">Daftar Isi</h2>
          <p class="mt-3 whitespace-pre-line">{{ book.table_of_contents || '-' }}</p>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { defineComponent, h, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import AOS from 'aos'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'
import './styles.css'

const Meta = defineComponent({
  props: { label: String, value: String },
  setup(props) {
    return () => h('div', { class: 'rounded-2xl bg-sky-50 p-4' }, [
      h('p', { class: 'text-xs font-black uppercase tracking-wider text-sky-700' }, props.label),
      h('p', { class: 'mt-1 font-black text-slate-950' }, props.value),
    ])
  },
})

const route = useRoute()
const book = ref(null)
const loading = ref(false)

async function loadBook() {
  loading.value = true
  const { data } = await api.get(`/books/${route.params.id}`)
  book.value = data
  loading.value = false
}

onMounted(async () => {
  AOS.refresh()
  await loadBook()
})
</script>
