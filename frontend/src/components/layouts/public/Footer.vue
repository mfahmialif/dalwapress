<template>
  <footer class="public-footer">
    <div class="footer-glow"></div>

    <div class="relative mx-auto max-w-7xl px-5 py-14 lg:px-8">
      <div class="footer-main">
        <div class="footer-brand">
          <router-link to="/" class="footer-logo">
            <span class="footer-logo-icon">
              <span class="material-symbols-outlined text-[26px]">auto_stories</span>
            </span>
            <span>
              <span class="block text-xl font-black text-white">UII Dalwa Press</span>
              <span class="block text-xs font-black uppercase tracking-[0.2em] text-sky-300">Academic Publisher</span>
            </span>
          </router-link>
          <p class="mt-6 max-w-xl text-base leading-8 text-slate-300">
            Ruang penerbitan akademik untuk buku, artikel, e-book, dan karya literasi kampus yang dikelola dengan proses editorial yang rapi.
          </p>
          <div class="mt-7 flex flex-wrap gap-3">
            <router-link to="/books" class="footer-cta">
              Lihat Katalog
              <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </router-link>
            <router-link to="/submissions" class="footer-ghost">
              Kirim Naskah
            </router-link>
          </div>
        </div>

        <div class="footer-column">
          <p class="footer-heading">Navigasi</p>
          <router-link v-for="item in navigation" :key="item.to" :to="item.to" class="footer-link">
            {{ item.label }}
          </router-link>
        </div>

        <div class="footer-column">
          <p class="footer-heading">Layanan</p>
          <a v-for="item in services" :key="item.href" :href="item.href" class="footer-link">
            {{ item.label }}
          </a>
        </div>

        <div class="footer-contact">
          <p class="footer-heading">Kontak</p>
          <a href="mailto:press@uiidalwa.ac.id" class="footer-contact-row">
            <span class="material-symbols-outlined">mail</span>
            press@uiidalwa.ac.id
          </a>
          <a href="https://wa.me/6281200000000" target="_blank" rel="noopener" class="footer-contact-row">
            <span class="material-symbols-outlined">chat</span>
            +62 812 0000 0000
          </a>
          <a href="https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6" target="_blank" rel="noopener" class="footer-contact-row">
            <span class="material-symbols-outlined">location_on</span>
            Pasuruan, Jawa Timur
          </a>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© {{ year }} UII Dalwa Press. All rights reserved.</p>
        <div class="flex flex-wrap items-center gap-4">
          <router-link to="/login" class="footer-bottom-link">Admin Login</router-link>
          <a href="https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6" target="_blank" rel="noopener" class="footer-bottom-link">Maps</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
defineProps({
  year: {
    type: Number,
    required: true,
  },
})

const navigation = [
  { label: 'Home', to: '/' },
  { label: 'Books', to: '/books' },
  { label: 'News', to: '/news' },
  { label: 'Submit', to: '/submissions' },
  { label: 'Contact', to: '/contact' },
]

const services = [
  { label: 'Penerbitan Buku', href: '/books' },
  { label: 'Konsultasi Naskah', href: '/submissions' },
  { label: 'Alur Penerbitan', href: '/#flow' },
  { label: 'Katalog Karya', href: '/books' },
]
</script>
