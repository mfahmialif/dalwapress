<template>
  <div class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none">
    <TransitionGroup name="toast-slide">
      <div v-for="toast in toastStore.toasts" :key="toast.id"
           class="toast-item flex items-center justify-between gap-3 px-4 py-3.5 rounded-xl border pointer-events-auto shadow-lg backdrop-blur-md transition-all duration-300"
           :class="typeClasses(toast.type)">
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-[20px] shrink-0" :class="iconAnimClass(toast.type)">
            {{ getIcon(toast.type) }}
          </span>
          <p class="text-sm font-bold leading-normal">{{ toast.message }}</p>
        </div>
        <button @click="toastStore.dismiss(toast.id)" class="text-current/60 hover:text-current transition shrink-0 cursor-pointer">
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToastStore } from '../../stores/toast'

const toastStore = useToastStore()

function getIcon(type) {
  switch (type) {
    case 'success': return 'check_circle'
    case 'error': return 'cancel'
    case 'warning': return 'warning'
    case 'loading': return 'progress_activity'
    default: return 'info'
  }
}

function iconAnimClass(type) {
  return type === 'loading' ? 'animate-spin' : ''
}

function typeClasses(type) {
  switch (type) {
    case 'success':
      return 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
    case 'error':
      return 'bg-red-500/10 border-red-500/30 text-red-400'
    case 'warning':
      return 'bg-amber-500/10 border-amber-500/30 text-amber-400'
    case 'loading':
      return 'bg-blue-500/10 border-blue-500/30 text-blue-400'
    default:
      return 'bg-slate-500/10 border-slate-500/30 text-slate-300'
  }
}
</script>

<style scoped>
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-slide-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.9);
}
.toast-slide-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
