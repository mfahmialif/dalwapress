<template>
  <section id="news" class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-5 lg:px-8">
      <div class="grid gap-10 lg:grid-cols-[0.75fr_1.25fr]">
        <div data-aos="fade-right">
          <p class="section-kicker">UII Dalwa Press Update</p>
          <h2 class="section-title mt-4">Berita, promo, dan informasi penerbitan.</h2>
          <p class="mt-5 leading-8 text-slate-600">
            Kanal update dipakai untuk mengabarkan penerbitan buku baru, agenda literasi, informasi layanan, dan pengumuman redaksi.
          </p>
          <router-link to="/news" class="cta-primary mt-8">
            Semua News
            <span class="material-symbols-outlined text-[20px]">newspaper</span>
          </router-link>
        </div>

        <div class="grid gap-4">
          <article v-for="(item, index) in updates" :key="item.id || item.title" class="update-row" data-aos="fade-left" :data-aos-delay="index * 100">
            <div class="date-badge">
              <span>{{ item.day }}</span>
              <small>{{ item.month }}</small>
            </div>
            <div>
              <p class="text-xs font-black uppercase tracking-[0.18em] text-sky-700">{{ item.category }}</p>
              <router-link v-if="item.id" :to="`/news/${item.id}`" class="group inline-flex">
                <h3 class="mt-2 text-xl font-black text-[#101418] transition-colors group-hover:text-sky-700">{{ item.title }}</h3>
              </router-link>
              <h3 v-else class="mt-2 text-xl font-black text-[#101418]">{{ item.title }}</h3>
              <p class="mt-2 leading-7 text-slate-600">{{ item.body }}</p>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  updates: {
    type: Array,
    required: true,
  },
})
</script>
