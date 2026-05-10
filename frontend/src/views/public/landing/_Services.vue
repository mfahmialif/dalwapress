<template>
  <section id="services" class="bg-[#f6f8f7] py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <SectionHeader kicker="Fasilitas Press" title="Layanan cepat untuk penulis dan pembaca." />

      <div class="mt-10 grid gap-4 md:grid-cols-4">
        <a v-for="(service, index) in services" :key="service.title" :href="service.href" class="service-tile" data-aos="zoom-in-up" :data-aos-delay="index * 90">
          <span class="feature-icon">
            <span class="material-symbols-outlined">{{ service.icon }}</span>
          </span>
          <h3 class="mt-5 text-lg font-black text-[#101418]">{{ service.title }}</h3>
          <p class="mt-2 text-sm leading-6 text-slate-600">{{ service.body }}</p>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
import SectionHeader from './_SectionHeader.vue'

defineProps({
  services: {
    type: Array,
    required: true,
  },
})
</script>
