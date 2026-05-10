<template>
  <section id="books" class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <SectionHeader kicker="Buku Terbaru" title="Katalog karya akademik pilihan.">
        <a href="#contact" class="text-sm font-black text-sky-700 hover:text-[#0b1020]">Ajukan naskah</a>
      </SectionHeader>

      <div class="book-swiper-wrap mt-10" data-aos="fade-up" data-aos-delay="120">
        <Swiper
          :modules="swiperModules"
          :slides-per-view="1.08"
          :space-between="18"
          :loop="true"
          :autoplay="{ delay: 2600, disableOnInteraction: false, pauseOnMouseEnter: true }"
          :pagination="{ clickable: true }"
          :navigation="{ nextEl: '.book-next', prevEl: '.book-prev' }"
          :breakpoints="bookBreakpoints"
          class="book-swiper"
        >
          <SwiperSlide v-for="book in books" :key="book.title">
            <article class="book-card">
              <div class="book-cover">
                <img :src="book.image" :alt="book.title" class="h-60 w-full object-cover" />
                <span>{{ book.category }}</span>
              </div>
              <div class="p-5">
                <h3 class="text-lg font-black leading-snug text-[#101418]">{{ book.title }}</h3>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ book.author }}</p>
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
</script>
