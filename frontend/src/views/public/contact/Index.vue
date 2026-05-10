<template>
  <main class="min-h-screen bg-[#f6f8f7] pt-32 text-[#111827]">
    <section class="contact-hero">
      <div class="mx-auto grid max-w-7xl gap-10 px-5 pb-16 pt-10 lg:grid-cols-[0.95fr_1.05fr] lg:px-8 lg:pt-16">
        <div data-aos="fade-right">
          <p class="contact-kicker">
            <span class="size-2.5 rounded-full bg-sky-600"></span>
            CONTACT UII DALWA PRESS
          </p>
          <h1 class="mt-6 max-w-3xl text-5xl font-black leading-[1.02] tracking-tight text-[#101418] md:text-7xl">
            Mari mulai publikasi karya akademik berikutnya.
          </h1>
          <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
            Hubungi redaksi untuk konsultasi naskah, penerbitan buku, katalog, kerja sama literasi, dan kebutuhan publikasi kampus.
          </p>
        </div>

        <div class="contact-card" data-aos="fade-left">
          <div v-for="item in contactItems" :key="item.label" class="contact-row">
            <span class="contact-icon material-symbols-outlined">{{ item.icon }}</span>
            <div>
              <p class="text-sm font-black uppercase tracking-[0.16em] text-sky-700">{{ item.label }}</p>
              <p class="mt-1 text-lg font-black text-[#101418]">{{ item.value }}</p>
              <p class="mt-1 text-sm leading-6 text-slate-500">{{ item.note }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="bg-white py-16">
      <div class="mx-auto max-w-7xl px-5 lg:px-8" data-aos="fade-up">
        <div class="maps-frame">
          <iframe
            title="Lokasi UII Dalwa Press"
            src="https://www.google.com/maps?q=Universitas%20Islam%20Internasional%20Dalwa%20Pasuruan&output=embed"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="h-full w-full border-0"
          ></iframe>
          <a :href="mapsUrl" target="_blank" rel="noopener" class="maps-link">
            <span class="material-symbols-outlined text-[18px]">open_in_new</span>
            Buka Maps
          </a>
        </div>
      </div>

      <div class="mx-auto mt-8 max-w-7xl px-5 lg:px-8">
        <div class="contact-panel" data-aos="fade-up">
          <p class="contact-kicker compact">
            <span class="size-2.5 rounded-full bg-sky-600"></span>
            Form Kontak
          </p>
          <h2 class="mt-4 text-3xl font-black tracking-tight text-[#101418] md:text-4xl">Kirim pesan ke redaksi.</h2>
          <p class="mt-3 leading-7 text-slate-600">
            Form ini masih frontend/local preview. Untuk produksi, nanti bisa kita sambungkan ke backend atau WhatsApp gateway.
          </p>

          <form class="mt-8 grid gap-4" @submit.prevent="submitMessage">
            <div class="grid gap-4 md:grid-cols-2">
              <input v-model.trim="form.name" class="contact-input" placeholder="Nama lengkap" />
              <input v-model.trim="form.email" class="contact-input" placeholder="Email / WhatsApp" />
            </div>
            <select v-model="form.topic" class="contact-input">
              <option value="Penerbitan Buku">Penerbitan Buku</option>
              <option value="Katalog">Katalog</option>
              <option value="Kerja Sama">Kerja Sama</option>
              <option value="Lainnya">Lainnya</option>
            </select>
            <textarea v-model.trim="form.message" class="contact-input min-h-36 resize-none py-4" placeholder="Tulis kebutuhan publikasi kamu..."></textarea>
            <button class="contact-submit" type="submit">
              Kirim Pesan
              <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
            <p v-if="success" class="rounded-2xl bg-sky-50 px-4 py-3 text-sm font-bold text-sky-700">
              Pesan tersimpan di sesi browser. Integrasi backend bisa ditambahkan setelah API kontak tersedia.
            </p>
          </form>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import AOS from 'aos'
import 'aos/dist/aos.css'
import './styles.css'

const success = ref(false)
const mapsUrl = 'https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6'
const form = reactive({
  name: '',
  email: '',
  topic: 'Penerbitan Buku',
  message: '',
})

const contactItems = [
  { icon: 'mail', label: 'Email', value: 'press@uiidalwa.ac.id', note: 'Untuk naskah, editorial, katalog, dan administrasi penerbitan.' },
  { icon: 'call', label: 'WhatsApp', value: '+62 812 0000 0000', note: 'Konsultasi cepat terkait proses penerbitan dan kerja sama.' },
  { icon: 'schedule', label: 'Jam Layanan', value: 'Senin - Jumat, 08.00 - 16.00', note: 'Pesan di luar jam layanan akan dibalas pada hari kerja berikutnya.' },
]

function submitMessage() {
  const messages = JSON.parse(localStorage.getItem('dalwapress-contact-messages') || '[]')
  messages.unshift({ ...form, createdAt: new Date().toISOString() })
  localStorage.setItem('dalwapress-contact-messages', JSON.stringify(messages))
  form.name = ''
  form.email = ''
  form.topic = 'Penerbitan Buku'
  form.message = ''
  success.value = true
}

onMounted(() => {
  AOS.init({ duration: 780, easing: 'ease-out-cubic', once: true, offset: 80 })
})
</script>
