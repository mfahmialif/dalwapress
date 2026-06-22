<template>
  <footer class="footer-bar flex items-center justify-center px-8 py-4 transition-colors duration-500">
    <p class="text-muted text-xs font-medium">{{ appSettings.systemName }} &copy; {{ currentYear }}</p>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { getPublicSettings } from '../../../services/publicSettings'

const appSettings = getPublicSettings()
const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.footer-bar { background: var(--bg-sidebar); border-top: 1px solid var(--border); }
.text-muted { color: var(--text-muted); }
</style>
