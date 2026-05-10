<template>
  <header :class="['top-header flex items-center justify-between px-4 sm:px-6 lg:px-8 py-4 shrink-0 z-10 transition-colors duration-500', layoutMode === 'horizontal' ? 'horiz-mode' : '']">
    <div class="flex items-center gap-3">
      <button @click="$emit('toggle-sidebar')"
              class="hamburger-btn p-2 rounded-lg transition-colors cursor-pointer lg:hidden">
        <span class="material-symbols-outlined text-[24px]">menu</span>
      </button>
      <h2 class="text-lg sm:text-xl font-bold text-heading tracking-tight">{{ pageTitle }}</h2>
    </div>
    <div class="flex items-center gap-2 sm:gap-4">
      <router-link to="/author/submissions/create" class="quick-create hidden sm:flex items-center gap-2 rounded-full px-4 py-2 text-sm font-black transition-colors">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Submission
      </router-link>

      <button @click="$emit('toggle-theme')"
              class="theme-toggle relative p-2 rounded-full transition-all duration-500 cursor-pointer"
              :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
        <Transition name="icon-swap" mode="out-in">
          <span v-if="isDark" key="dark" class="material-symbols-outlined text-[22px]">light_mode</span>
          <span v-else key="light" class="material-symbols-outlined text-[22px]">dark_mode</span>
        </Transition>
      </button>

      <button @click="$emit('toggle-layout')"
              class="theme-toggle relative p-2 rounded-full transition-all duration-500 cursor-pointer"
              :title="layoutMode === 'vertical' ? 'Horizontal Layout' : 'Vertical Layout'">
        <Transition name="icon-swap" mode="out-in">
          <span v-if="layoutMode === 'vertical'" key="vert" class="material-symbols-outlined text-[22px]">horizontal_distribute</span>
          <span v-else key="horiz" class="material-symbols-outlined text-[22px]">vertical_distribute</span>
        </Transition>
      </button>

      <router-link to="/author/notifications" class="notif-btn relative p-2 rounded-full transition-colors cursor-pointer">
        <span class="material-symbols-outlined">notifications</span>
        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-accent rounded-full shadow-[0_0_5px_rgba(251,191,36,0.8)]"></span>
      </router-link>

      <div class="relative" ref="profileDropdownRef">
        <div @click="profileOpen = !profileOpen"
             class="avatar-ring w-9 h-9 sm:w-10 sm:h-10 rounded-full border-2 border-accent overflow-hidden shrink-0 cursor-pointer hover:opacity-80 transition-opacity shadow-[0_0_10px_rgba(251,191,36,0.3)] flex items-center justify-center">
          <span class="material-symbols-outlined text-accent text-xl sm:text-2xl">person</span>
        </div>

        <Transition name="dropdown">
          <div v-if="profileOpen" class="profile-dropdown absolute right-0 mt-2 w-64 rounded-xl overflow-hidden z-50">
            <div class="px-4 py-3 border-b profile-dropdown-border">
              <p class="text-sm font-bold text-heading truncate">{{ authStore.user?.name || 'Author' }}</p>
              <p class="text-xs text-muted truncate mt-0.5">{{ authStore.user?.email || '' }}</p>
            </div>
            <div class="py-1.5">
              <router-link to="/author/profile"
                           @click="profileOpen = false"
                           class="profile-dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm transition-colors">
                <span class="material-symbols-outlined text-[20px]">manage_accounts</span>
                <span>Profile Author</span>
              </router-link>
              <button @click="handleLogout"
                      class="profile-dropdown-item flex items-center gap-3 px-4 py-2.5 text-sm transition-colors w-full text-left cursor-pointer logout-item">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                <span>Logout</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '../../../stores/auth'

defineProps({
  pageTitle: { type: String, default: 'Dashboard Author' },
  isDark: { type: Boolean, default: true },
  layoutMode: { type: String, default: 'vertical' }
})

defineEmits(['toggle-theme', 'toggle-sidebar', 'toggle-layout'])

const profileOpen = ref(false)
const profileDropdownRef = ref(null)
const authStore = useAuthStore()

function handleLogout() {
  profileOpen.value = false
  authStore.logout()
}

function handleClickOutside(e) {
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(e.target)) {
    profileOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<style scoped>
.top-header { background: var(--bg-header); backdrop-filter: blur(12px); }
.top-header.horiz-mode { background: var(--bg-card); border-bottom: 1px solid var(--border); }
.quick-create { background: var(--color-accent); color: var(--text-btn); }
.quick-create:hover { box-shadow: 0 0 20px rgba(251, 191, 36, 0.25); }
.avatar-ring { background: var(--bg-input); }
.text-heading { color: var(--text-heading); }
.text-muted { color: var(--text-muted); }
.hamburger-btn { color: var(--text-heading); background: transparent; }
.hamburger-btn:hover { background: var(--bg-input); color: var(--color-accent); }
.theme-toggle { background: var(--toggle-bg); color: var(--toggle-text); }
.theme-toggle:hover {
  background: var(--color-accent);
  color: var(--text-btn);
  box-shadow: 0 0 20px rgba(251, 191, 36, 0.4);
  transform: rotate(15deg) scale(1.1);
}
.theme-toggle:active { transform: rotate(0deg) scale(0.95); }
.notif-btn { color: var(--text-muted); background: transparent; }
.notif-btn:hover { background: var(--bg-input); color: var(--color-accent); }
.profile-dropdown {
  background: var(--bg-card);
  border: 1px solid var(--border);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4), 0 2px 8px rgba(0, 0, 0, 0.2);
}
.profile-dropdown-border { border-color: var(--border); }
.profile-dropdown-item { color: var(--text-body); }
.profile-dropdown-item:hover { background: var(--hover-nav-bg); color: var(--color-accent); }
.profile-dropdown-item.logout-item:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-8px) scale(0.95); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px) scale(0.98); }
.icon-swap-enter-active { transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
.icon-swap-leave-active { transition: all 0.2s ease; }
.icon-swap-enter-from { opacity: 0; transform: rotate(-90deg) scale(0.5); }
.icon-swap-leave-to { opacity: 0; transform: rotate(90deg) scale(0.5); }
</style>
