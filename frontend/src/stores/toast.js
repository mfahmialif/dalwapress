import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  const toasts = ref([])

  function show(message, type = 'info', duration = 3000) {
    const id = Date.now() + Math.random()
    const toast = { id, message, type }
    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        dismiss(id)
      }, duration)
    }

    return id
  }

  function dismiss(id) {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index !== -1) {
      toasts.value.splice(index, 1)
    }
  }

  function success(message, duration = 3000) {
    return show(message, 'success', duration)
  }

  function error(message, duration = 4000) {
    return show(message, 'error', duration)
  }

  function warning(message, duration = 4500) {
    return show(message, 'warning', duration)
  }

  function info(message, duration = 3000) {
    return show(message, 'info', duration)
  }

  function loading(message) {
    // Duration is 0, must be dismissed manually
    return show(message, 'loading', 0)
  }

  return { toasts, show, dismiss, success, error, warning, info, loading }
})
