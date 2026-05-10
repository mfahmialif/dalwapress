<template>
  <section id="home" class="relative overflow-hidden pt-30">
    <div class="hero-glow"></div>
    <div class="mx-auto grid min-h-screen max-w-7xl items-center gap-12 px-5 pb-16 pt-8 lg:grid-cols-[1fr_0.95fr] lg:px-8 lg:pt-16">
      <div class="reveal-up">
        <p class="mb-7 inline-flex items-center gap-3 rounded-full bg-slate-200/70 px-5 py-2 text-sm font-black tracking-[0.12em] text-slate-600 shadow-sm">
          <span class="size-2.5 rounded-full bg-sky-700"></span>
          PUBLISHING KNOWLEDGE
        </p>
        <h1 class="max-w-4xl text-5xl font-black leading-[1.02] tracking-tight text-[#101418] sm:text-6xl lg:text-7xl">
          Penerbit Perguruan Tinggi untuk Karya Ilmiah dan Literasi Akademik
        </h1>
        <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-600">
          UII Dalwa Press hadir untuk memfasilitasi penerbitan buku, e-book, artikel, dan karya akademik dengan proses editorial yang rapi, cepat, dan mudah diakses.
        </p>
        <div class="mt-9 flex flex-wrap items-center gap-4">
          <a href="#books" class="cta-primary">
            Lihat Buku
            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
          </a>
          <a href="#flow" class="cta-secondary">
            Terbitkan Karya
            <span class="material-symbols-outlined text-[20px]">edit_note</span>
          </a>
        </div>
      </div>

      <div class="hero-card reveal-up delay-2">
        <div class="relative overflow-hidden rounded-[2rem] bg-[#0b1020] shadow-2xl shadow-slate-900/25">
          <img src="/img/hero-bg.jpg" alt="UII Dalwa Press editorial" class="h-[500px] w-full object-cover opacity-80" />
          <div class="absolute inset-0 bg-linear-to-t from-[#0b1020] via-[#0b1020]/30 to-transparent"></div>
          <div class="absolute bottom-0 left-0 right-0 p-7 text-white">
            <p class="text-sm font-black uppercase tracking-[0.22em] text-sky-300">Academic Publisher</p>
            <h2 class="mt-3 text-3xl font-black leading-tight">Menerbitkan ilmu, menguatkan tradisi menulis.</h2>
          </div>
          <div class="floating-card">
            <span class="material-symbols-outlined text-sky-500">verified</span>
            <div>
              <p class="text-sm font-black text-slate-950">ISBN ready</p>
              <p class="text-xs font-semibold text-slate-500">Editorial, layout, and publication support</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
