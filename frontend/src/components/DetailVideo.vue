<template>
  <div class="fixed inset-0 z-50 group overflow-hidden bg-black animate-fadeIn">
    <!-- ═══════ REAL VIDEO PLAYER ═══════ -->
    <video
      ref="videoEl"
      class="absolute inset-0 w-full h-full object-contain z-0"
      :src="item.videoSrc"
      :poster="item.image || '/img/default-video.png'"
      @timeupdate="onTimeUpdate"
      @loadedmetadata="onLoaded"
      @ended="onEnded"
      @click="togglePlay"
    ></video>

    <!-- ═══════ FALLBACK: No video file ═══════ -->
    <div v-if="!item.videoSrc" class="absolute inset-0 z-0 flex items-center justify-center bg-black">
      <img :src="item.image || '/img/default-video.png'" :alt="item.title" class="w-full h-full object-cover opacity-60" />
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="text-center">
          <span class="material-symbols-outlined text-[80px] text-white/30">videocam_off</span>
          <p class="text-white/40 text-lg mt-2">Video belum tersedia</p>
        </div>
      </div>
    </div>

    <!-- ═══════ TOP INFO HUD ═══════ -->
    <Transition name="slide-down">
      <div v-show="showControls"
           class="absolute top-0 left-0 z-20 w-full p-8 md:p-12 bg-linear-to-b from-black/80 to-transparent transition-opacity duration-300">
        <div class="flex items-start justify-between">
          <div class="flex flex-col gap-2 max-w-2xl">
            <div class="inline-flex items-center gap-2 mb-1">
              <span class="px-2 py-0.5 rounded text-xs font-bold bg-accent text-[#0a192f] uppercase tracking-wider">{{ item.videoTag || 'Video' }}</span>
              <span class="text-white/80 text-sm font-medium tracking-wide">UII Dalwa Press</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-bold text-white drop-shadow-md leading-tight">
              {{ item.title }}
            </h1>
            <p v-if="item.speaker" class="text-lg md:text-2xl text-white/90 font-medium drop-shadow-sm mt-1">
              {{ item.speaker }}
            </p>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ═══════ CENTER PLAY BUTTON (when paused) ═══════ -->
    <Transition name="fade">
      <div v-if="!isPlaying && item.videoSrc"
           class="absolute inset-0 z-10 flex items-center justify-center cursor-pointer"
           @click="togglePlay">
        <button class="bg-accent/90 text-[#221f10] rounded-full p-5 hover:scale-110 transition-transform shadow-[0_0_40px_rgba(251,191,36,0.5)]">
          <span class="material-symbols-outlined text-6xl" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
        </button>
      </div>
    </Transition>

    <!-- ═══════ BOTTOM CONTROLS ═══════ -->
    <Transition name="slide-up">
      <div v-show="showControls"
           class="absolute bottom-0 left-0 w-full z-20 bg-linear-to-t from-black/95 via-black/70 to-transparent pt-20 pb-8 md:pb-10 px-8 md:px-12 flex flex-col gap-4">
        <!-- Progress Bar -->
        <div class="w-full cursor-pointer py-1" @click="seekTo">
          <div class="relative h-1.5 w-full bg-white/20 rounded-full overflow-hidden hover:h-2.5 transition-all">
            <div class="absolute top-0 left-0 h-full bg-white/30 rounded-full"
                 :style="{ width: bufferPercent + '%' }"></div>
            <div class="absolute top-0 left-0 h-full bg-accent rounded-full shadow-[0_0_10px_rgba(251,191,36,0.5)]"
                 :style="{ width: progressPercent + '%' }"></div>
          </div>
        </div>

        <!-- Controls Row -->
        <div class="flex items-center justify-between w-full">
          <!-- Left -->
          <div class="flex items-center gap-4">
            <button @click="$emit('close')"
                    class="flex items-center gap-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg px-3 py-2 transition-all cursor-pointer">
              <span class="material-symbols-outlined text-3xl">arrow_back</span>
              <span class="text-lg font-medium hidden md:inline">Back</span>
            </button>
            <!-- Prev -->
            <button v-if="hasPrev" @click.stop="$emit('prev')"
                    class="flex items-center justify-center size-10 rounded-full bg-white/10 hover:bg-accent/90 text-white hover:text-[#0a192f] transition-all cursor-pointer" title="Sebelumnya">
              <span class="material-symbols-outlined text-2xl">skip_previous</span>
            </button>
            <button @click="togglePlay"
                    class="bg-accent text-[#221f10] rounded-full p-2.5 hover:scale-110 transition-transform flex items-center justify-center cursor-pointer">
              <span class="material-symbols-outlined text-3xl" style="font-variation-settings: 'FILL' 1;">{{ isPlaying ? 'pause' : 'play_arrow' }}</span>
            </button>
            <!-- Next -->
            <button v-if="hasNext" @click.stop="$emit('next')"
                    class="flex items-center justify-center size-10 rounded-full bg-white/10 hover:bg-accent/90 text-white hover:text-[#0a192f] transition-all cursor-pointer" title="Selanjutnya">
              <span class="material-symbols-outlined text-2xl">skip_next</span>
            </button>
            <!-- Volume -->
            <div class="flex items-center gap-2">
              <button @click="toggleMute" class="text-white hover:text-accent transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-2xl">{{ isMuted ? 'volume_off' : 'volume_up' }}</span>
              </button>
            </div>
            <!-- Time -->
            <div class="text-base font-medium tracking-wide font-mono">
              <span class="text-white">{{ formatTime(currentTimeSec) }}</span>
              <span class="text-white/40 mx-1">/</span>
              <span class="text-white/70">{{ formatTime(durationSec) }}</span>
            </div>
          </div>

          <!-- Right -->
          <div class="flex items-center gap-4">
            <button @click="toggleFullscreen" class="text-white hover:text-accent transition-colors p-2 cursor-pointer">
              <span class="material-symbols-outlined text-2xl">{{ isFullscreen ? 'fullscreen_exit' : 'fullscreen' }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  item: { type: Object, required: true },
  hasPrev: { type: Boolean, default: false },
  hasNext: { type: Boolean, default: false },
})

const emit = defineEmits(['close', 'prev', 'next', 'toggleUI'])

const videoEl = ref(null)
const isPlaying = ref(false)
const isMuted = ref(false)
const isFullscreen = ref(false)
const showControls = ref(true)
const currentTimeSec = ref(0)
const durationSec = ref(0)
const progressPercent = ref(0)
const bufferPercent = ref(0)

let controlsTimeout = null

function togglePlay() {
  if (!videoEl.value || !props.item.videoSrc) return
  if (isPlaying.value) {
    videoEl.value.pause()
  } else {
    videoEl.value.play()
  }
  isPlaying.value = !isPlaying.value
  showControlsBriefly()
}

function toggleMute() {
  if (!videoEl.value) return
  videoEl.value.muted = !videoEl.value.muted
  isMuted.value = videoEl.value.muted
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
    isFullscreen.value = true
  } else {
    document.exitFullscreen()
    isFullscreen.value = false
  }
}

function onTimeUpdate() {
  if (!videoEl.value) return
  currentTimeSec.value = Math.floor(videoEl.value.currentTime)
  if (durationSec.value > 0) {
    progressPercent.value = (videoEl.value.currentTime / durationSec.value) * 100
  }
  // Buffer
  if (videoEl.value.buffered.length > 0) {
    bufferPercent.value = (videoEl.value.buffered.end(videoEl.value.buffered.length - 1) / durationSec.value) * 100
  }
}

function onLoaded() {
  if (!videoEl.value) return
  durationSec.value = Math.floor(videoEl.value.duration)
}

function onEnded() {
  isPlaying.value = false
  showControls.value = true
}

function seekTo(e) {
  if (!videoEl.value || !durationSec.value) return
  const rect = e.currentTarget.getBoundingClientRect()
  const percent = (e.clientX - rect.left) / rect.width
  videoEl.value.currentTime = percent * durationSec.value
}

function showControlsBriefly() {
  showControls.value = true
  emit('toggleUI', true)
  clearTimeout(controlsTimeout)
  if (isPlaying.value) {
    controlsTimeout = setTimeout(() => { showControls.value = false; emit('toggleUI', false) }, 3000)
  }
}

function onMouseMove() {
  showControlsBriefly()
}

function formatTime(seconds) {
  const m = Math.floor(seconds / 60)
  const s = seconds % 60
  return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

onMounted(() => {
  document.addEventListener('mousemove', onMouseMove)
})
onUnmounted(() => {
  clearTimeout(controlsTimeout)
  document.removeEventListener('mousemove', onMouseMove)
})
</script>

<style scoped>
.slide-down-enter-active, .slide-down-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-down-enter-from, .slide-down-leave-to { transform: translateY(-100%); opacity: 0; }
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease, opacity 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
