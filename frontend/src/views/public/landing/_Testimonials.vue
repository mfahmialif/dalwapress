<template>
  <section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <SectionHeader kicker="Testimoni Klien" title="Cerita penulis yang tumbuh bersama press." />

      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <article v-for="(testimonial, index) in testimonials" :key="testimonial.name" class="testimonial-card" data-aos="fade-up" :data-aos-delay="index * 100">
          <p class="leading-8 text-slate-600">"{{ testimonial.quote }}"</p>
          <div class="mt-6">
            <p class="font-black text-[#101418]">{{ testimonial.name }}</p>
            <p class="mt-1 text-sm font-bold text-sky-700">{{ testimonial.role }}</p>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import SectionHeader from './_SectionHeader.vue'

defineProps({
  testimonials: {
    type: Array,
    required: true,
  },
})
</script>
