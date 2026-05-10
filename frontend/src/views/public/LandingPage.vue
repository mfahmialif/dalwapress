<template>
  <div class="min-h-screen bg-[#f7f8fb] text-slate-950">
    <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
        <router-link to="/" class="flex items-center gap-3">
          <span class="flex size-10 items-center justify-center rounded-lg bg-slate-950 text-white">
            <span class="material-symbols-outlined text-[22px]">menu_book</span>
          </span>
          <span class="text-lg font-bold tracking-tight">UII Dalwa Press</span>
        </router-link>
        <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 md:flex">
          <a href="#tentang" class="hover:text-slate-950">Tentang</a>
          <a href="#fokus" class="hover:text-slate-950">Fokus</a>
          <router-link to="/news" class="hover:text-slate-950">News</router-link>
        </nav>
        <router-link to="/login" class="inline-flex h-10 items-center gap-2 rounded-lg bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-slate-800">
          <span class="material-symbols-outlined text-[18px]">login</span>
          Admin
        </router-link>
      </div>
    </header>

    <main>
      <section class="relative overflow-hidden bg-white">
        <div class="mx-auto grid min-h-[calc(100vh-73px)] max-w-7xl items-center gap-12 px-5 py-14 lg:grid-cols-[1.05fr_0.95fr] lg:px-8">
          <div class="max-w-3xl">
            <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-800">
              <span class="material-symbols-outlined text-[18px]">auto_stories</span>
              Pusat publikasi dan literasi kampus
            </p>
            <h1 class="text-5xl font-black leading-[1.02] tracking-tight text-slate-950 md:text-7xl">
              UII Dalwa Press
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
              Media penerbitan dan informasi akademik yang menghadirkan berita, artikel, dan dokumentasi karya civitas UII Dalwa dengan tampilan yang bersih, cepat, dan mudah dikelola.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
              <router-link to="/news" class="inline-flex h-12 items-center gap-2 rounded-lg bg-emerald-700 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800">
                Baca News
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </router-link>
              <a href="#fokus" class="inline-flex h-12 items-center gap-2 rounded-lg border border-slate-300 bg-white px-6 text-sm font-bold text-slate-800 transition hover:border-slate-500">
                Lihat Fokus
              </a>
            </div>
          </div>

          <div class="relative">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-950 shadow-2xl shadow-slate-950/20">
              <img src="/img/hero-bg.jpg" alt="UII Dalwa Press" class="h-[420px] w-full object-cover opacity-85" />
              <div class="border-t border-white/10 bg-slate-950 p-6 text-white">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-emerald-300">Editorial Desk</p>
                <h2 class="mt-2 text-2xl font-bold">Publikasi yang rapi, terukur, dan siap tumbuh.</h2>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section id="tentang" class="border-y border-slate-200 bg-[#f7f8fb] py-18">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 lg:grid-cols-[0.8fr_1.2fr] lg:px-8">
          <div>
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">Tentang</p>
            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Remake dari sistem display menjadi portal press.</h2>
          </div>
          <p class="text-lg leading-8 text-slate-600">
            Aplikasi ini disederhanakan untuk kebutuhan UII Dalwa Press: landing page publik, halaman news, dan panel admin untuk mengelola konten. Modul display TV, agenda, gallery, log, control, dan pengumuman tidak lagi menjadi bagian navigasi maupun API utama.
          </p>
        </div>
      </section>

      <section id="fokus" class="bg-white py-18">
        <div class="mx-auto max-w-7xl px-5 lg:px-8">
          <div class="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
              <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">Fokus</p>
              <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950">Konten utama yang tersisa.</h2>
            </div>
            <router-link to="/news" class="inline-flex h-11 items-center gap-2 rounded-lg border border-slate-300 px-4 text-sm font-bold text-slate-800 hover:border-slate-500">
              Semua News
              <span class="material-symbols-outlined text-[18px]">open_in_new</span>
            </router-link>
          </div>

          <div class="grid gap-4 md:grid-cols-3">
            <article v-for="item in focusItems" :key="item.title" class="rounded-xl border border-slate-200 bg-[#f7f8fb] p-6">
              <span class="flex size-11 items-center justify-center rounded-lg bg-white text-emerald-700 shadow-sm">
                <span class="material-symbols-outlined">{{ item.icon }}</span>
              </span>
              <h3 class="mt-5 text-xl font-bold text-slate-950">{{ item.title }}</h3>
              <p class="mt-3 leading-7 text-slate-600">{{ item.body }}</p>
            </article>
          </div>
        </div>
      </section>
    </main>

    <footer class="border-t border-slate-200 bg-slate-950 px-5 py-8 text-sm text-slate-300">
      <div class="mx-auto flex max-w-7xl flex-col justify-between gap-3 md:flex-row md:items-center">
        <p class="font-bold text-white">UII Dalwa Press</p>
        <p>© {{ year }} UII Dalwa Press. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
const year = new Date().getFullYear()

const focusItems = [
  {
    icon: 'article',
    title: 'News',
    body: 'Publikasi berita dan artikel resmi dalam satu modul yang mudah dicari dan dikelola.'
  },
  {
    icon: 'edit_note',
    title: 'Editorial CMS',
    body: 'Admin dapat membuat, menyunting, menerbitkan, atau menyimpan draft konten.'
  },
  {
    icon: 'public',
    title: 'Portal Publik',
    body: 'Landing page modern dan elegan untuk identitas baru UII Dalwa Press.'
  }
]
</script>
