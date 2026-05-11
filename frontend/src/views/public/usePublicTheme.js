import { computed, onBeforeUnmount, ref } from 'vue'

const STORAGE_KEY = 'dalwapress-public-theme'
const theme = ref('system')
const systemTheme = ref('light')
let mediaQuery = null

function readStoredTheme() {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    return ['light', 'dark'].includes(stored) ? stored : 'system'
  } catch {
    return 'system'
  }
}

function writeStoredTheme(value) {
  try {
    if (value === 'system') localStorage.removeItem(STORAGE_KEY)
    else localStorage.setItem(STORAGE_KEY, value)
  } catch {
    // Ignore storage errors and keep the in-memory theme.
  }
}

function updateSystemTheme() {
  systemTheme.value = mediaQuery?.matches ? 'dark' : 'light'
}

function initTheme() {
  theme.value = readStoredTheme()

  if (typeof window === 'undefined' || !window.matchMedia) return

  mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
  updateSystemTheme()
  mediaQuery.addEventListener?.('change', updateSystemTheme)
}

export function usePublicTheme() {
  const resolvedTheme = computed(() => (theme.value === 'system' ? systemTheme.value : theme.value))

  function setTheme(value) {
    theme.value = ['light', 'dark'].includes(value) ? value : 'system'
    writeStoredTheme(theme.value)
  }

  function toggleTheme() {
    setTheme(resolvedTheme.value === 'dark' ? 'light' : 'dark')
  }

  onBeforeUnmount(() => {
    mediaQuery?.removeEventListener?.('change', updateSystemTheme)
  })

  return {
    theme,
    resolvedTheme,
    initTheme,
    setTheme,
    toggleTheme,
  }
}
