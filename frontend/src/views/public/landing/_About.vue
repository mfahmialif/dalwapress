<template>
  <section id="about" class="bg-[#0b1020] py-20 text-white">
    <div class="mx-auto grid max-w-7xl gap-10 px-5 lg:grid-cols-[0.95fr_1.05fr] lg:items-center lg:px-8">
      <div data-aos="fade-right">
        <p class="section-kicker text-sky-300">Tentang Kami</p>
        <h2 class="mt-4 text-4xl font-black tracking-tight md:text-5xl">
          Menjadi ruang penerbitan yang menghidupkan budaya menulis dan membaca.
        </h2>
      </div>
      <div data-aos="fade-left">
        <p class="text-lg leading-9 text-slate-300">
          UII Dalwa Press berfokus pada penerbitan hasil penelitian, buku ajar, naskah keilmuan, serta konten literasi kampus. Kami mendampingi penulis dari persiapan naskah sampai publikasi agar karya akademik dapat dibaca lebih luas.
        </p>
        <div class="stats-strip dark mt-8">
          <div v-for="stat in stats" :key="stat.label" class="px-5 py-5">
            <p class="text-3xl font-black text-white">{{ stat.value }}</p>
            <p class="mt-1 text-sm font-bold text-slate-400">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  stats: {
    type: Array,
    required: true,
  },
})
</script>
