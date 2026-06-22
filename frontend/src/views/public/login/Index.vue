<template>
  <main class="login-page min-h-screen" :data-theme="resolvedTheme">
    <button
      class="login-theme-toggle"
      type="button"
      :title="resolvedTheme === 'dark' ? 'Aktifkan light mode' : 'Aktifkan dark mode'"
      @click="toggleTheme"
    >
      <span class="material-symbols-outlined text-[20px]">{{ resolvedTheme === 'dark' ? 'light_mode' : 'dark_mode' }}</span>
    </button>

    <div class="grid min-h-screen lg:grid-cols-[1.08fr_0.92fr]">
      <section class="login-visual-panel relative hidden overflow-hidden lg:block">
        <img :src="loginBackgroundImageUrl" alt="UII Dalwa Press" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover" />
        <div class="login-visual-overlay absolute inset-0"></div>
        <div class="login-visual-gradient absolute inset-x-0 bottom-0 h-1/2"></div>

        <div class="relative z-10 flex h-full flex-col justify-between p-12 xl:p-16">
          <router-link to="/" class="inline-flex w-fit items-center gap-3">
            <span class="login-visual-logo-icon flex size-11 items-center justify-center rounded-lg">
              <span class="material-symbols-outlined text-[24px]">menu_book</span>
            </span>
            <span class="login-visual-brand text-xl font-black tracking-tight">UII Dalwa Press</span>
          </router-link>

          <div class="max-w-2xl">
            <p class="login-kicker mb-5 inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold">
              <span class="material-symbols-outlined text-[18px]">verified_user</span>
              Portal Login
            </p>
            <h1 class="login-visual-title text-5xl font-black leading-[1.03] tracking-tight xl:text-7xl">
              Kelola publikasi dengan rapi dan cepat.
            </h1>
            <p class="login-visual-copy mt-6 max-w-xl text-lg leading-8">
              Masuk untuk mengatur news, redaksi, dan konten publik UII Dalwa Press dari satu dashboard.
            </p>
          </div>

          <div class="grid max-w-xl grid-cols-3 gap-3">
            <div v-for="item in highlights" :key="item.label" class="login-highlight rounded-lg p-4 backdrop-blur">
              <span class="material-symbols-outlined">{{ item.icon }}</span>
              <p class="mt-3 text-sm font-bold">{{ item.label }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8">
        <div class="w-full max-w-[460px]">
          <div class="mb-8 flex items-center justify-between gap-4 lg:hidden">
            <router-link to="/" class="flex items-center gap-3">
              <span class="login-mobile-logo-icon flex size-10 items-center justify-center rounded-lg">
                <span class="material-symbols-outlined text-[22px]">menu_book</span>
              </span>
              <span class="login-mobile-brand text-lg font-black">UII Dalwa Press</span>
            </router-link>
          </div>

          <div class="login-card rounded-2xl p-6 shadow-2xl backdrop-blur sm:p-8">
            <div class="mb-8">
              <p class="login-eyebrow text-sm font-bold uppercase tracking-[0.2em]">Secure Login</p>
              <h2 class="login-title mt-3 text-3xl font-black tracking-tight">Selamat Datang</h2>
            </div>

            <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
              <label class="flex flex-col gap-2">
                <span class="login-label text-sm font-bold">Username</span>
                <div class="field-shell">
                  <span class="material-symbols-outlined field-icon">person</span>
                  <input
                    v-model="username"
                    class="field-input"
                    placeholder="admin"
                    type="text"
                    autocomplete="username"
                  />
                </div>
              </label>

              <label class="flex flex-col gap-2">
                <span class="login-label text-sm font-bold">Password</span>
                <div class="field-shell">
                  <span class="material-symbols-outlined field-icon">lock</span>
                  <input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="field-input pr-12"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                  />
                  <button
                    @click.prevent="showPassword = !showPassword"
                    class="password-toggle absolute right-3 flex size-9 items-center justify-center rounded-lg transition"
                    type="button"
                    :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                  >
                    <span class="material-symbols-outlined text-[20px]">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
                  </button>
                </div>
              </label>

              <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                  <span class="remember-box relative flex size-5 items-center justify-center rounded">
                    <input v-model="rememberMe" class="peer sr-only" type="checkbox" />
                    <span class="material-symbols-outlined absolute inset-0 flex items-center justify-center rounded bg-sky-400 text-[14px] text-[#0b1020] opacity-0 transition peer-checked:opacity-100">check</span>
                  </span>
                  <span class="login-muted text-sm font-medium">Ingat saya</span>
                </label>
                <router-link to="/" class="login-back-link text-sm font-bold transition">
                  Kembali ke web
                </router-link>
              </div>

              <div class="register-callout">
                <span class="material-symbols-outlined text-[19px]">person_add</span>
                <p>Belum punya akun?</p>
                <router-link to="/register">Daftar dengan email</router-link>
              </div>

              <Transition name="fade">
                <div v-if="errorMsg" class="flex items-start gap-3 rounded-xl border border-red-400/30 bg-red-500/10 p-4 text-sm font-medium text-red-200">
                  <span class="material-symbols-outlined text-[20px] text-red-300">error</span>
                  <span>{{ errorMsg }}</span>
                </div>
              </Transition>

              <button
                type="submit"
                :disabled="isLoading"
                class="login-submit mt-2 inline-flex h-13 items-center justify-center gap-2 rounded-xl px-5 font-black shadow-lg transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
                <span>{{ isLoading ? 'Memproses...' : 'Masuk Dashboard' }}</span>
                <span v-if="!isLoading" class="material-symbols-outlined text-[20px]">arrow_forward</span>
              </button>

              <div v-if="googleConfig.enabled" class="login-divider">
                <span></span>
                <p>atau</p>
                <span></span>
              </div>

              <div v-if="googleConfig.enabled" class="google-login-wrap" :class="{ loading: isGoogleLoading }">
                <div class="google-login-shell">
                  <span class="google-login-icon">G</span>
                  <div class="google-button-frame">
                    <div ref="googleButtonRef" class="google-button-host"></div>
                  </div>
                </div>
                <div v-if="isGoogleLoading" class="google-loading">
                  <span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                  <span>Memproses Google...</span>
                </div>
              </div>
            </form>
          </div>

          <p class="login-footer mt-6 text-center text-xs font-medium">
            © {{ currentYear }} UII Dalwa Press. Hak cipta dilindungi.
          </p>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { nextTick, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../axios'
import { getPublicSettings } from '../../../services/publicSettings'
import { useAuthStore } from '../../../stores/auth'
import { usePublicTheme } from '../usePublicTheme'
import '../../../components/layouts/public/styles.css'

const router = useRouter()
const authStore = useAuthStore()
const { resolvedTheme, initTheme, toggleTheme } = usePublicTheme()

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const errorMsg = ref('')
const isLoading = ref(false)
const isGoogleLoading = ref(false)
const loginBackgroundImageUrl = getPublicSettings().loginBackgroundImageUrl
const googleConfig = ref({
  enabled: false,
  client_id: null,
})
const googleButtonRef = ref(null)
const currentYear = new Date().getFullYear()
let googlePromptInitialized = false

const highlights = [
  { icon: 'article', label: 'News' },
  { icon: 'edit_note', label: 'Editorial' },
  { icon: 'admin_panel_settings', label: 'Access' },
]

function redirectToDashboard(roleName) {
  if (roleName === 'Author') router.push({ name: 'AuthorDashboard' })
  else if (roleName === 'Editor') router.push({ name: 'EditorDashboard' })
  else if (roleName === 'Penulis') router.push({ name: 'PenulisDashboard' })
  else if (roleName === 'Kepala Penulis') router.push({ name: 'KepalaPenulisDashboard' })
  else router.push({ name: 'AdminDashboard' })
}

async function handleLogin() {
  errorMsg.value = ''

  if (!username.value || !password.value) {
    errorMsg.value = 'Username dan password harus diisi.'
    return
  }

  isLoading.value = true

  try {
    const data = await authStore.login(username.value, password.value, rememberMe.value)
    redirectToDashboard(data.user?.role?.name)
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      errorMsg.value = errors?.username?.[0] || 'Username atau password salah.'
    } else if (error.response?.status === 401) {
      errorMsg.value = 'Username atau password salah.'
    } else {
      errorMsg.value = 'Terjadi kesalahan server. Coba lagi nanti.'
    }
  } finally {
    isLoading.value = false
  }
}

async function loadGoogleConfig() {
  try {
    const { data } = await api.get('/auth/google/config', { skipAuthRedirect: true })
    googleConfig.value = data

    if (data.enabled && data.client_id) {
      await loadGoogleScript()
      await nextTick()
      initGoogleLogin()
    }
  } catch {
    googleConfig.value = { enabled: false, client_id: null }
  }
}

function loadGoogleScript() {
  if (window.google?.accounts?.id) {
    return Promise.resolve()
  }

  return new Promise((resolve, reject) => {
    const existing = document.querySelector('script[src="https://accounts.google.com/gsi/client"]')
    if (existing) {
      existing.addEventListener('load', resolve, { once: true })
      existing.addEventListener('error', reject, { once: true })
      return
    }

    const script = document.createElement('script')
    script.src = 'https://accounts.google.com/gsi/client'
    script.async = true
    script.defer = true
    script.onload = resolve
    script.onerror = reject
    document.head.appendChild(script)
  })
}

function initGoogleLogin() {
  if (googlePromptInitialized || !window.google?.accounts?.id || !googleConfig.value.client_id || !googleButtonRef.value) {
    return
  }

  window.google.accounts.id.initialize({
    client_id: googleConfig.value.client_id,
    callback: handleGoogleCredential,
  })
  window.google.accounts.id.renderButton(googleButtonRef.value, {
    type: 'standard',
    theme: resolvedTheme.value === 'dark' ? 'filled_black' : 'outline',
    size: 'large',
    text: 'signin_with',
    shape: 'pill',
    logo_alignment: 'center',
    width: 320,
  })
  googlePromptInitialized = true
}

async function handleGoogleCredential(response) {
  if (!response?.credential) {
    errorMsg.value = 'Login Google dibatalkan atau tidak valid.'
    return
  }

  errorMsg.value = ''
  isGoogleLoading.value = true

  try {
    const data = await authStore.loginWithGoogle(response.credential)
    redirectToDashboard(data.user?.role?.name)
  } catch (error) {
    const errors = error.response?.data?.errors
    errorMsg.value = errors?.google?.[0] || error.response?.data?.message || 'Login Google gagal. Coba lagi nanti.'
  } finally {
    isGoogleLoading.value = false
  }
}

onMounted(async () => {
  initTheme()
  await loadGoogleConfig()
})
</script>

<style scoped>
.login-page {
  --login-bg: #f6f8fb;
  --login-panel-bg: #ffffff;
  --login-panel-border: #e2e8f0;
  --login-panel-shadow: 0 24px 70px rgba(15, 23, 42, 0.1);
  --login-heading: #0f172a;
  --login-body: #334155;
  --login-muted: #64748b;
  --login-accent: #0284c7;
  --login-accent-strong: #0369a1;
  --login-accent-soft: #e0f2fe;
  --login-input-bg: #ffffff;
  --login-input-border: #cbd5e1;
  --login-input-text: #0f172a;
  --login-input-focus-bg: #ffffff;
  --login-visual-heading: #f8fafc;
  --login-visual-body: rgba(226, 232, 240, 0.9);
  background:
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 26rem),
    linear-gradient(180deg, #ffffff 0%, var(--login-bg) 100%);
  color: var(--login-heading);
}
.login-page[data-theme='dark'] {
  --login-bg: #0b1020;
  --login-panel-bg: rgba(255, 255, 255, 0.06);
  --login-panel-border: rgba(255, 255, 255, 0.1);
  --login-panel-shadow: 0 24px 70px rgba(0, 0, 0, 0.3);
  --login-heading: #ffffff;
  --login-body: #cbd5e1;
  --login-muted: #94a3b8;
  --login-accent: #38bdf8;
  --login-accent-strong: #7dd3fc;
  --login-accent-soft: rgba(56, 189, 248, 0.12);
  --login-input-bg: rgba(255, 255, 255, 0.06);
  --login-input-border: rgba(255, 255, 255, 0.12);
  --login-input-text: #ffffff;
  --login-input-focus-bg: rgba(255, 255, 255, 0.08);
  --login-visual-heading: #ffffff;
  --login-visual-body: rgba(226, 232, 240, 0.85);
  background: #0b1020;
  color: var(--login-heading);
}
.login-page[data-theme='light'] {
  color-scheme: light;
}
.login-page[data-theme='dark'] {
  color-scheme: dark;
}
.login-theme-toggle {
  position: fixed;
  right: 1rem;
  top: 1rem;
  z-index: 30;
  display: inline-flex;
  width: 2.6rem;
  height: 2.6rem;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  border: 1px solid var(--login-panel-border);
  background: var(--login-panel-bg);
  color: var(--login-heading);
  box-shadow: var(--login-panel-shadow);
  transition: transform 0.2s ease, background 0.2s ease;
}
.login-theme-toggle:hover {
  transform: translateY(-2px);
  background: var(--login-accent-soft);
}
.login-visual-panel {
  background: #0f172a;
}
.login-visual-overlay {
  background: rgba(7, 17, 31, 0.72);
}
.login-visual-gradient {
  background: linear-gradient(to top, #0b1020, transparent);
}
.login-page[data-theme='light'] .login-visual-overlay {
  background: linear-gradient(120deg, rgba(15, 23, 42, 0.76), rgba(3, 105, 161, 0.38));
}
.login-page[data-theme='light'] .login-visual-gradient {
  background: linear-gradient(to top, rgba(15, 23, 42, 0.7), transparent);
}
.login-visual-logo-icon,
.login-mobile-logo-icon {
  background: #ffffff;
  color: #0b1020;
}
.login-mobile-logo-icon {
  border: 1px solid var(--login-panel-border);
}
.login-visual-brand,
.login-visual-title {
  color: var(--login-visual-heading);
}
.login-mobile-brand,
.login-title {
  color: var(--login-heading);
}
.login-kicker {
  border: 1px solid rgba(125, 211, 252, 0.32);
  background: rgba(14, 165, 233, 0.14);
  color: #e0f2fe;
}
.login-visual-copy {
  color: var(--login-visual-body);
}
.login-highlight {
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}
.login-highlight .material-symbols-outlined {
  color: #bae6fd;
}
.login-card {
  border: 1px solid var(--login-panel-border);
  background: var(--login-panel-bg);
  box-shadow: var(--login-panel-shadow);
}
.login-eyebrow,
.login-back-link {
  color: var(--login-accent);
}
.login-back-link:hover {
  color: var(--login-accent-strong);
}
.login-copy,
.login-muted,
.login-footer {
  color: var(--login-muted);
}
.login-label {
  color: var(--login-body);
}
.field-shell {
  position: relative;
  display: flex;
  align-items: center;
}
.field-icon {
  position: absolute;
  left: 1rem;
  color: var(--login-muted);
  font-size: 21px;
  pointer-events: none;
}
.field-input {
  height: 3.5rem;
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid var(--login-input-border);
  background: var(--login-input-bg);
  padding-left: 3rem;
  padding-right: 1rem;
  color: var(--login-input-text);
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}
.field-input::placeholder {
  color: var(--login-muted);
}
.field-input:focus {
  border-color: rgb(125 211 252);
  background: var(--login-input-focus-bg);
  box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.16);
}
.password-toggle {
  color: var(--login-muted);
}
.password-toggle:hover {
  background: var(--login-accent-soft);
  color: var(--login-heading);
}
.remember-box {
  border: 1px solid var(--login-input-border);
  background: var(--login-input-bg);
}
.login-submit {
  background: #38bdf8;
  color: #07111f;
  box-shadow: 0 18px 36px rgba(2, 132, 199, 0.22);
}
.login-submit:hover {
  background: #7dd3fc;
}
.login-divider {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 0.85rem;
  color: var(--login-muted);
  font-size: 0.78rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.16em;
}
.login-divider span {
  height: 1px;
  background: var(--login-panel-border);
}
.register-callout {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  border: 1px solid var(--login-panel-border);
  border-radius: 0.9rem;
  background: var(--login-input-bg);
  padding: 0.8rem 1rem;
  color: var(--login-muted);
  font-size: 0.9rem;
  font-weight: 750;
}
.register-callout a,
.register-callout span {
  color: var(--login-accent);
}
.register-callout a {
  font-weight: 950;
}
.google-login-wrap {
  position: relative;
  min-height: 3.4rem;
}
.google-login-wrap.loading .google-login-shell {
  opacity: 0.35;
  pointer-events: none;
}
.google-login-shell {
  display: grid;
  grid-template-columns: auto 1fr;
  align-items: center;
  gap: 0.85rem;
  min-height: 3.4rem;
  border: 1px solid var(--login-panel-border);
  border-radius: 1rem;
  background:
    linear-gradient(180deg, color-mix(in srgb, var(--login-input-bg) 92%, white), var(--login-input-bg));
  padding: 0.42rem 0.55rem;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}
.login-page[data-theme='dark'] .google-login-shell {
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.075), rgba(255, 255, 255, 0.045));
  box-shadow: 0 16px 34px rgba(0, 0, 0, 0.22);
}
.google-login-shell:hover {
  border-color: color-mix(in srgb, var(--login-accent) 58%, var(--login-panel-border));
  box-shadow: 0 16px 36px rgba(2, 132, 199, 0.14);
  transform: translateY(-1px);
}
.google-login-icon {
  display: inline-flex;
  width: 2.45rem;
  height: 2.45rem;
  align-items: center;
  justify-content: center;
  border-radius: 0.85rem;
  background: #ffffff;
  color: #1f1f1f;
  font-family: Arial, sans-serif;
  font-size: 1rem;
  font-weight: 900;
  box-shadow: inset 0 0 0 1px #e2e8f0, 0 8px 18px rgba(15, 23, 42, 0.08);
}
.google-button-frame {
  display: flex;
  min-width: 0;
  justify-content: center;
  overflow: hidden;
  border-radius: 999px;
}
.google-button-host {
  display: flex;
  justify-content: center;
  width: 100%;
  min-height: 2.5rem;
}
.google-button-host :deep(iframe) {
  margin: 0 auto !important;
}
.google-loading {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border-radius: 0.75rem;
  background: color-mix(in srgb, var(--login-panel-bg) 86%, transparent);
  color: var(--login-heading);
  font-size: 0.9rem;
  font-weight: 900;
  backdrop-filter: blur(8px);
}
.fade-enter-active,
.fade-leave-active {
  transition: all 0.24s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>
