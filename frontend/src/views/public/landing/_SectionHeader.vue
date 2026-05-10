<template>
  <div class="section-head" data-aos="fade-up">
    <div>
      <p class="section-kicker">{{ kicker }}</p>
      <h2 class="section-title" :class="titleClass">{{ title }}</h2>
    </div>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  kicker: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  titleClass: {
    type: String,
    default: '',
  },
})
</script>
