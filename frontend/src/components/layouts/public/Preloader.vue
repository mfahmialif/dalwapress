<template>
  <Transition name="preloader">
    <div v-if="show" class="fixed inset-0 z-[100] flex items-center justify-center bg-[#f6f8f7]">
      <div class="editorial-loader">
        <div class="loader-orbit">
          <div class="loader-core">
            <span class="material-symbols-outlined text-[34px]">auto_stories</span>
          </div>
        </div>
        <div class="text-center">
          <p class="loader-title">UII Dalwa Press</p>
          <p class="loader-subtitle">Curating academic works</p>
        </div>
        <div class="loader-line">
          <span></span>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
})
</script>
