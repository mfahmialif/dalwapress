<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-black animate-fadeIn select-none">
    <!-- ═══════ FULLSCREEN IMAGE ═══════ -->
    <img :src="item.image" :alt="item.title"
         class="w-full h-full object-contain" />

    <!-- ═══════ TAP AREA (entire screen toggles info) ═══════ -->
    <div class="absolute inset-0 z-30 cursor-pointer" @click="toggleInfo"></div>

    <!-- ═══════ TOP GRADIENT + INFO ═══════ -->
    <Transition name="slide-down">
      <div v-show="showInfo"
           class="absolute top-0 left-0 w-full z-40 p-8 md:p-12 bg-linear-to-b from-black/80 to-transparent pointer-events-none">
        <div class="flex items-start justify-between">
          <div class="flex flex-col gap-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 rounded text-xs font-bold bg-accent text-[#0a192f] uppercase tracking-wider">{{ item.category }}</span>
              <span class="text-white/80 text-sm font-medium tracking-wide">UII Dalwa Press</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-bold text-white drop-shadow-md leading-tight">
              {{ item.title }}
            </h1>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ═══════ BOTTOM BAR ═══════ -->
    <Transition name="slide-up">
      <div v-show="showInfo"
           class="absolute bottom-0 left-0 w-full z-40 bg-linear-to-t from-black/90 to-transparent pt-16 pb-8 px-12 flex items-center justify-between">
        <!-- Toggle hint -->
        <span class="text-white/50 text-sm flex items-center gap-2">
          <span class="material-symbols-outlined text-base">touch_app</span>
          Ketuk layar untuk sembunyikan informasi
        </span>
        <!-- Nav + Close -->
        <div class="flex items-center gap-3 pointer-events-auto">
          <!-- Prev -->
          <button v-if="hasPrev" @click.stop="$emit('prev')"
                  class="flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/5 hover:bg-white/10 border border-white/20 hover:border-accent transition-all active:scale-95 backdrop-blur-sm cursor-pointer group">
            <span class="material-symbols-outlined text-white group-hover:text-accent">chevron_left</span>
            <span class="text-sm font-medium text-white group-hover:text-accent transition-colors">Sebelumnya</span>
          </button>
          <!-- Next -->
          <button v-if="hasNext" @click.stop="$emit('next')"
                  class="flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/5 hover:bg-white/10 border border-white/20 hover:border-accent transition-all active:scale-95 backdrop-blur-sm cursor-pointer group">
            <span class="text-sm font-medium text-white group-hover:text-accent transition-colors">Selanjutnya</span>
            <span class="material-symbols-outlined text-white group-hover:text-accent">chevron_right</span>
          </button>
          <!-- Close -->
          <button @click.stop="$emit('close')"
                  class="group flex items-center gap-3 px-6 py-2 rounded-full bg-white/5 hover:bg-white/10 border border-accent/50 transition-all active:scale-95 backdrop-blur-sm">
            <span class="text-lg font-medium text-white group-hover:text-accent transition-colors">Tutup</span>
            <span class="material-symbols-outlined text-accent">close</span>
          </button>
        </div>
      </div>
    </Transition>


  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  item: { type: Object, required: true },
  hasPrev: { type: Boolean, default: false },
  hasNext: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'prev', 'next', 'toggleUI'])

const showInfo = ref(true)

function toggleInfo() {
  showInfo.value = !showInfo.value
  emit('toggleUI', showInfo.value)
}
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-down-enter-from, .slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

.slide-up-enter-active, .slide-up-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}
.slide-up-enter-from, .slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
