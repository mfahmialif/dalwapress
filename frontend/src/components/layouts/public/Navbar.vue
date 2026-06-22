<template>
  <header
    class="public-header inset-x-0 top-0 z-50 px-4 pt-4 sm:px-6 lg:pt-6"
    :class="isFloating ? 'is-floating' : 'is-origin'"
  >
    <div class="public-nav-shell mx-auto flex max-w-7xl items-center justify-between gap-4 rounded-[1.7rem] px-5 py-3 backdrop-blur-xl lg:px-7">
      <router-link
        to="/"
        class="logo-pill group"
        :class="{ 'logo-mode': navbarBrand.navbarBrandMode === 'logo' && navbarBrand.navbarBrandLogoUrl }"
        :aria-label="navbarBrand.navbarBrandTitle"
      >
        <img
          v-if="navbarBrand.navbarBrandMode === 'logo' && navbarBrand.navbarBrandLogoUrl"
          :src="navbarBrand.navbarBrandLogoUrl"
          :alt="navbarBrand.navbarBrandTitle"
          class="navbar-brand-logo"
        />
        <template v-else>
          <span class="logo-icon">
            <span class="material-symbols-outlined text-[22px]">{{ navbarBrand.navbarBrandIcon }}</span>
          </span>
          <span class="leading-tight">
            <span class="block text-base font-black text-white">{{ navbarBrand.navbarBrandTitle }}</span>
            <span v-if="navbarBrand.navbarBrandSubtitle" class="block text-[10px] font-black uppercase tracking-wide text-sky-300">{{ navbarBrand.navbarBrandSubtitle }}</span>
          </span>
        </template>
      </router-link>

      <nav class="public-nav-links hidden items-center gap-7 text-[14px] font-bold md:flex">
        <router-link v-for="item in items" :key="item.href" :to="item.href" class="nav-link" :class="{ active: activeSection === item.id }">
          {{ item.label }}
        </router-link>
      </nav>

      <div class="hidden items-center gap-2 md:flex">
        <button
          class="public-theme-toggle"
          type="button"
          :title="theme === 'dark' ? 'Aktifkan light mode' : 'Aktifkan dark mode'"
          @click="$emit('toggle-theme')"
        >
          <span class="material-symbols-outlined text-[19px]">{{ theme === 'dark' ? 'light_mode' : 'dark_mode' }}</span>
        </button>
        <router-link :to="authLink.to" class="public-signin hidden h-9 items-center gap-2 rounded-full px-3.5 text-sm font-black transition md:inline-flex">
          <span class="material-symbols-outlined text-[19px]">{{ authLink.icon }}</span>
          {{ authLink.label }}
        </router-link>
      </div>

      <button @click="mobileOpen = !mobileOpen" class="public-mobile-menu-button flex size-10 items-center justify-center rounded-full md:hidden">
        <span class="material-symbols-outlined">{{ mobileOpen ? 'close' : 'menu' }}</span>
      </button>
    </div>

    <Transition name="mobile-menu">
      <div v-if="mobileOpen" class="public-mobile-panel mx-auto mt-3 max-w-7xl rounded-3xl p-4 shadow-2xl backdrop-blur-xl md:hidden">
        <router-link v-for="item in items" :key="item.href" :to="item.href" @click="mobileOpen = false" class="public-mobile-link block rounded-2xl px-4 py-3 font-bold" :class="{ active: activeSection === item.id }">
          {{ item.label }}
        </router-link>
        <button class="public-mobile-theme-toggle mt-2 flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 font-black" type="button" @click="$emit('toggle-theme')">
          <span class="material-symbols-outlined text-[20px]">{{ theme === 'dark' ? 'light_mode' : 'dark_mode' }}</span>
          {{ theme === 'dark' ? 'Light Mode' : 'Dark Mode' }}
        </button>
        <router-link :to="authLink.to" class="public-mobile-signin mt-2 flex items-center justify-center gap-2 rounded-2xl px-4 py-3 font-black" @click="mobileOpen = false">
          <span class="material-symbols-outlined text-[20px]">{{ authLink.icon }}</span>
          {{ authLink.label }}
        </router-link>
      </div>
    </Transition>
  </header>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { getPublicSettings } from '../../../services/publicSettings'
import { useAuthStore } from '../../../stores/auth'

defineProps({
  items: {
    type: Array,
    required: true,
  },
  activeSection: {
    type: String,
    default: 'home',
  },
  theme: {
    type: String,
    default: 'light',
  },
})

defineEmits(['toggle-theme'])

const mobileOpen = ref(false)
const fixedTrigger = 320
const isFloating = ref(typeof window !== 'undefined' && window.scrollY > fixedTrigger)
const authStore = useAuthStore()
const navbarBrand = getPublicSettings()
let scrollFrame = null

function updateNavState() {
  isFloating.value = window.scrollY > fixedTrigger
}

function handleScroll() {
  if (scrollFrame !== null) return

  scrollFrame = window.requestAnimationFrame(() => {
    updateNavState()
    scrollFrame = null
  })
}

const dashboardRoute = computed(() => {
  const roleName = authStore.user?.role?.name

  if (roleName === 'Author') {
    return { name: 'AuthorDashboard' }
  }

  if (roleName === 'Editor') {
    return { name: 'EditorDashboard' }
  }

  if (roleName === 'Penulis') {
    return { name: 'PenulisDashboard' }
  }

  if (roleName === 'Kepala Penulis') {
    return { name: 'KepalaPenulisDashboard' }
  }

  return { name: 'AdminDashboard' }
})

const authLink = computed(() => {
  if (authStore.isAuthenticated) {
    return {
      to: dashboardRoute.value,
      icon: 'dashboard',
      label: 'Dashboard',
    }
  }

  return {
    to: '/login',
    icon: 'login',
    label: 'Sign In',
  }
})

onMounted(() => {
  updateNavState()
  window.addEventListener('scroll', handleScroll, { passive: true })

  if (!authStore.hasCheckedSession) {
    authStore.fetchUser()
  }
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', handleScroll)

  if (scrollFrame !== null) {
    window.cancelAnimationFrame(scrollFrame)
  }
})
</script>
