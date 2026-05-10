<template>
  <section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <SectionHeader kicker="Kenapa Harus UII Dalwa Press?" title="Standar penerbitan yang rapi dari naskah sampai distribusi." />

      <div class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <article v-for="(item, index) in strengths" :key="item.title" class="feature-card" data-aos="fade-up" :data-aos-delay="(index % 3) * 90">
          <span class="feature-icon">
            <span class="material-symbols-outlined">{{ item.icon }}</span>
          </span>
          <h3 class="mt-6 text-xl font-black text-[#101418]">{{ item.title }}</h3>
          <p class="mt-3 leading-7 text-slate-600">{{ item.body }}</p>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
import SectionHeader from './_SectionHeader.vue'

defineProps({
  strengths: {
    type: Array,
    required: true,
  },
})
</script>
