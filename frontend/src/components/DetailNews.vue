<template>
  <div class="fixed inset-0 z-50 flex flex-row overflow-hidden bg-[#0B1120] animate-fadeIn">
    <!-- ═══════ LEFT: IMAGE PANEL (60%) ═══════ -->
    <div class="relative w-[60%] h-full bg-black/50 overflow-hidden group">
      <div class="absolute inset-0 bg-cover bg-center transition-transform duration-1000 ease-in-out group-hover:scale-105"
           :style="{ backgroundImage: `url('${item.image}')` }"></div>
      <div class="absolute inset-0 bg-linear-to-r from-black/40 to-transparent"></div>
      <!-- Fullscreen Badge -->
      <div class="absolute top-8 left-8 bg-black/40 backdrop-blur-md border border-white/10 text-white px-4 py-2 rounded-full flex items-center gap-2">
        <span class="material-symbols-outlined text-accent text-[24px]">fullscreen</span>
        <span class="text-sm uppercase tracking-widest font-bold">Fullscreen View</span>
      </div>
    </div>

    <!-- ═══════ RIGHT: ARTICLE PANEL (40%) ═══════ -->
    <div class="w-[40%] h-full flex flex-col relative bg-linear-to-b from-[#0f172a] to-[#172554] border-l border-white/5 shadow-2xl z-10">
      <!-- Pattern Overlay -->
      <div class="absolute inset-0 opacity-100 pointer-events-none z-0"
           :style="{ backgroundImage: patternBg }"></div>

      <!-- Header -->
      <div class="relative z-10 flex flex-col px-12 pt-16 pb-8 border-b border-white/10 bg-linear-to-b from-[#0f172a] via-[#0f172a] to-transparent">
        <div class="flex items-center gap-3 mb-6">
          <span class="px-3 py-1 bg-accent/20 text-accent border border-accent/40 rounded text-sm uppercase tracking-wider font-bold">{{ item.category }}</span>
          <span class="text-white/80 text-lg flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">schedule</span>
            {{ item.time }}
          </span>
        </div>
        <h1 class="text-accent text-5xl font-bold leading-[1.15] tracking-tight drop-shadow-sm font-serif">
          {{ item.title }}
        </h1>
      </div>

      <!-- Article Body -->
      <div class="relative z-10 flex-1 overflow-y-auto no-scrollbar px-12 py-8">
        <div class="max-w-none text-white/90 leading-relaxed text-xl">
          <!-- Rich HTML body from TinyMCE -->
          <div v-if="typeof item.body === 'string'" class="prose prose-invert prose-lg max-w-none" v-html="fixHtmlAssetUrls(item.body)"></div>
          <!-- Legacy: array of paragraphs -->
          <template v-else-if="Array.isArray(item.body)">
            <p v-for="(paragraph, i) in item.body" :key="i"
               class="mb-8"
               :class="{ 'first-letter:text-5xl first-letter:font-bold first-letter:text-accent first-letter:mr-1 first-letter:float-left': i === 0 }">
              {{ paragraph }}
            </p>
          </template>
          <!-- Fallback: description -->
          <div v-else-if="item.description" class="prose prose-invert prose-lg max-w-none" v-html="fixHtmlAssetUrls(item.description)"></div>
          <!-- Blockquote -->
          <div v-if="item.quote" class="my-8 p-6 bg-accent/10 border-l-4 border-accent rounded-r-lg">
            <p class="italic text-accent text-xl font-medium">"{{ item.quote.text }}"</p>
            <p class="text-sm text-accent/80 mt-2 uppercase tracking-widest">— {{ item.quote.author }}</p>
          </div>
        </div>
      </div>

      <!-- Close Button -->
      <div class="relative z-20 mt-auto bg-[#0f172a]/95 backdrop-blur border-t border-white/10 px-12 py-8 flex flex-col gap-4">
        <div class="flex items-center justify-end text-slate-300">
          <button @click="$emit('close')"
                  class="group flex items-center gap-3 px-6 py-2 rounded-full bg-white/5 hover:bg-white/10 border border-accent/50 transition-all active:scale-95">
            <span class="text-lg font-medium text-white group-hover:text-accent transition-colors">Tutup</span>
            <span class="material-symbols-outlined text-accent group-hover:text-accent transition-colors">close</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { fixHtmlAssetUrls } from '../utils/asset'
defineProps({
  item: {
    type: Object,
    required: true
  }
})

defineEmits(['close'])

const patternBg = `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23FFD700' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`
</script>
