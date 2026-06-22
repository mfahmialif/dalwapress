import { computed, onBeforeUnmount, ref } from 'vue'
import {
  getSystemTheme,
  readThemePreference,
  writeThemePreference,
} from '../../composables/useThemePreference'

const theme = ref('system')
const systemTheme = ref('light')
let mediaQuery = null

function readStoredTheme() {
  return readThemePreference({
    allowSystem: true,
    fallback: 'system',
    legacyKeys: ['dalwapress-public-theme'],
  })
}

function writeStoredTheme(value) {
  writeThemePreference(value, {
    legacyKeys: ['dalwapress-public-theme'],
  })
}

function updateSystemTheme() {
  systemTheme.value = mediaQuery?.matches ? 'dark' : getSystemTheme()
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
