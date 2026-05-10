<template>
  <section id="flow" class="bg-[#f6f8f7] py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <div class="mx-auto max-w-3xl text-center" data-aos="fade-up">
        <p class="section-kicker">Alur Penerbitan</p>
        <h2 class="section-title mt-4">Proses jelas, penulis lebih tenang.</h2>
      </div>

      <div class="mt-12 grid gap-4 md:grid-cols-5">
        <article v-for="(step, index) in publishingFlow" :key="step.title" class="flow-step" data-aos="flip-left" :data-aos-delay="index * 80">
          <span>{{ step.no }}</span>
          <h3 class="mt-5 text-lg font-black text-[#101418]">{{ step.title }}</h3>
          <p class="mt-3 text-sm leading-6 text-slate-600">{{ step.body }}</p>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  publishingFlow: {
    type: Array,
    required: true,
  },
})
</script>
