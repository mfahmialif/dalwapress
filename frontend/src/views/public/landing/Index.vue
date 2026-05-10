<template>
  <main>
    <Hero />
    <Books :books="books" />
    <Services :services="services" />
    <Supporter :supporters="supporters" />
    <NewsUpdate :updates="updates" />
    <About :stats="stats" />
    <PublishCta />
    <Strengths :strengths="strengths" />
    <Flow :publishing-flow="publishingFlow" />
    <Testimonials :testimonials="testimonials" />
    <Contact />
  </main>
</template>

<script setup>
import { onMounted } from 'vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import './styles.css'
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
  books,
  publishingFlow,
  services,
  stats,
  strengths,
  supporters,
  testimonials,
  updates,
} from './data'

onMounted(() => {
  AOS.init({
    duration: 850,
    easing: 'ease-out-cubic',
    once: true,
    offset: 90,
  })
})
</script>
