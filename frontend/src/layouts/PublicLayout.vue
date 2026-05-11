<template>
  <div class="public-root min-h-screen overflow-hidden font-display" :data-theme="resolvedTheme">
    <Preloader :show="loading" />
    <Navbar :items="navItems" :active-section="activeSection" :theme="resolvedTheme" @toggle-theme="toggleTheme" />

    <router-view v-slot="{ Component, route: viewRoute }">
      <Transition name="public-page" mode="out-in">
        <component :is="Component" :key="viewRoute.path" />
      </Transition>
    </router-view>

    <Footer :year="year" />
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import Footer from '../components/layouts/public/Footer.vue'
import Navbar from '../components/layouts/public/Navbar.vue'
import Preloader from '../components/layouts/public/Preloader.vue'
import { usePublicTheme } from '../views/public/usePublicTheme'
import '../components/layouts/public/styles.css'

const route = useRoute()
const { resolvedTheme, initTheme, toggleTheme } = usePublicTheme()
const year = new Date().getFullYear()
const loading = ref(true)
const activeSection = ref('home')
let sectionElements = []
let ticking = false

const navItems = [
  { label: 'Home', href: '/', id: 'home' },
  { label: 'Books', href: '/books', id: 'books' },
  { label: 'News', href: '/news', id: 'news' },
  { label: 'Submit', href: '/submissions', id: 'submissions' },
  { label: 'Contact', href: '/contact', id: 'contact' },
]

const isLanding = computed(() => route.name === 'Landing')

function updateRouteActiveSection() {
  if (route.path.startsWith('/news')) {
    activeSection.value = 'news'
    return
  }

  if (route.path.startsWith('/books')) {
    activeSection.value = 'books'
    return
  }

  if (route.path.startsWith('/submissions')) {
    activeSection.value = 'submissions'
    return
  }

  if (route.path.startsWith('/contact')) {
    activeSection.value = 'contact'
    return
  }

  if (!isLanding.value) {
    activeSection.value = route.meta.publicActive || 'home'
  }
}

function collectLandingSections() {
  sectionElements = navItems
    .filter((item) => item.sectionId || item.id === 'home')
    .map((item) => document.getElementById(item.sectionId || item.id))
    .filter(Boolean)
}

function updateLandingActiveSection() {
  if (!isLanding.value) return

  const marker = window.scrollY + 150
  let currentSection = 'home'

  for (const section of sectionElements) {
    if (section.offsetTop <= marker) {
      currentSection = section.id
    }
  }

  activeSection.value = currentSection
}

function onScroll() {
  if (ticking) return

  window.requestAnimationFrame(() => {
    updateLandingActiveSection()
    ticking = false
  })
  ticking = true
}

function refreshPublicNavigation() {
  window.removeEventListener('scroll', onScroll)

  if (isLanding.value) {
    window.requestAnimationFrame(() => {
      collectLandingSections()
      updateLandingActiveSection()
      window.addEventListener('scroll', onScroll, { passive: true })
    })
  } else {
    updateRouteActiveSection()
  }
}

onMounted(() => {
  initTheme()
  refreshPublicNavigation()

  window.setTimeout(() => {
    loading.value = false
  }, 650)
})

watch(() => route.fullPath, () => {
  refreshPublicNavigation()
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>

<style scoped>
.public-page-enter-active {
  transition: opacity 0.32s cubic-bezier(0.4, 0, 0.2, 1), transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
}
.public-page-leave-active {
  transition: opacity 0.16s ease;
}
.public-page-enter-from {
  opacity: 0;
  transform: translateY(14px);
}
.public-page-leave-to {
  opacity: 0;
}
</style>
