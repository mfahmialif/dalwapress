<template>
  <section id="books" class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <SectionHeader kicker="Buku Terbaru" title="Katalog karya akademik pilihan.">
        <div class="books-header-actions">
          <RouterLink :to="{ name: 'Books' }" class="books-cta-primary">
            <span>Lihat semua buku</span>
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
          </RouterLink>
          <a href="#contact" class="books-cta-secondary">Ajukan naskah</a>
        </div>
      </SectionHeader>

      <div v-if="books.length" class="book-swiper-wrap mt-10" data-aos="fade-up" data-aos-delay="120">
        <Swiper
          :modules="swiperModules"
          :slides-per-view="1.08"
          :space-between="18"
          :loop="books.length > 4"
          :autoplay="books.length > 1 ? { delay: 2600, disableOnInteraction: false, pauseOnMouseEnter: true } : false"
          :pagination="{ clickable: true }"
          :navigation="{ nextEl: '.book-next', prevEl: '.book-prev' }"
          :breakpoints="bookBreakpoints"
          class="book-swiper"
        >
          <SwiperSlide v-for="book in books" :key="book.title">
            <article class="book-card">
              <div class="book-cover">
                <img :src="book.image" :alt="book.title" />
                <span class="book-category-tag">{{ book.category }}</span>
                <div class="book-cover-overlay">
                  <div>
                    <h3>{{ book.title }}</h3>
                    <p>{{ book.author }}</p>
                  </div>
                  <RouterLink :to="bookDetailTarget(book)" class="book-detail-link">
                    <span>Detail</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                  </RouterLink>
                </div>
              </div>
            </article>
          </SwiperSlide>
        </Swiper>
        <div class="book-nav">
          <button class="book-prev" type="button" aria-label="Previous book">
            <span class="material-symbols-outlined">arrow_back</span>
          </button>
          <button class="book-next" type="button" aria-label="Next book">
            <span class="material-symbols-outlined">arrow_forward</span>
          </button>
        </div>
      </div>

      <div v-else class="books-empty" data-aos="fade-up" data-aos-delay="120">
        <span class="material-symbols-outlined">auto_stories</span>
        <p>Belum ada buku published.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Autoplay, Navigation, Pagination } from 'swiper/modules'
import { Swiper, SwiperSlide } from 'swiper/vue'
import SectionHeader from './_SectionHeader.vue'

defineProps({
  books: {
    type: Array,
    required: true,
  },
})

const swiperModules = [Autoplay, Navigation, Pagination]
const bookBreakpoints = {
  640: { slidesPerView: 2.05 },
  1024: { slidesPerView: 3.1 },
  1280: { slidesPerView: 4 },
}

function bookDetailTarget(book) {
  if (book.href) return book.href
  if (book.id) return { name: 'BookDetail', params: { id: book.id } }
  return { name: 'Books' }
}
</script>
