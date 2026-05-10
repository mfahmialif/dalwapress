<template>
  <main class="min-h-screen bg-[#0b1020] text-white">
    <div class="grid min-h-screen lg:grid-cols-[1.08fr_0.92fr]">
      <section class="relative hidden overflow-hidden lg:block">
        <img src="/img/hero-bg.jpg" alt="UII Dalwa Press" class="absolute inset-0 h-full w-full object-cover" />
        <div class="absolute inset-0 bg-[#07111f]/72"></div>
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-linear-to-t from-[#0b1020] to-transparent"></div>

        <div class="relative z-10 flex h-full flex-col justify-between p-12 xl:p-16">
          <router-link to="/" class="inline-flex w-fit items-center gap-3">
            <span class="flex size-11 items-center justify-center rounded-lg bg-white text-[#0b1020]">
              <span class="material-symbols-outlined text-[24px]">menu_book</span>
            </span>
            <span class="text-xl font-black tracking-tight">UII Dalwa Press</span>
          </router-link>

          <div class="max-w-2xl">
            <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-emerald-300/10 px-4 py-2 text-sm font-bold text-emerald-100">
              <span class="material-symbols-outlined text-[18px]">verified_user</span>
              Portal Login
            </p>
            <h1 class="text-5xl font-black leading-[1.03] tracking-tight xl:text-7xl">
              Kelola publikasi dengan rapi dan cepat.
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-slate-200/85">
              Masuk untuk mengatur news, redaksi, dan konten publik UII Dalwa Press dari satu dashboard.
            </p>
          </div>

          <div class="grid max-w-xl grid-cols-3 gap-3">
            <div v-for="item in highlights" :key="item.label" class="rounded-lg border border-white/10 bg-white/8 p-4 backdrop-blur">
              <span class="material-symbols-outlined text-emerald-200">{{ item.icon }}</span>
              <p class="mt-3 text-sm font-bold text-white">{{ item.label }}</p>
            </div>
          </div>
        </div>
      </section>

      <section class="flex min-h-screen items-center justify-center px-5 py-8 sm:px-8">
        <div class="w-full max-w-[460px]">
          <div class="mb-8 flex items-center justify-between gap-4 lg:hidden">
            <router-link to="/" class="flex items-center gap-3">
              <span class="flex size-10 items-center justify-center rounded-lg bg-white text-[#0b1020]">
                <span class="material-symbols-outlined text-[22px]">menu_book</span>
              </span>
              <span class="text-lg font-black">UII Dalwa Press</span>
            </router-link>
          </div>

          <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
            <div class="mb-8">
              <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-300">Secure Login</p>
              <h2 class="mt-3 text-3xl font-black tracking-tight text-white">Selamat Datang</h2>
              <p class="mt-2 leading-7 text-slate-400">Gunakan akun admin, operator, author, atau editor untuk masuk ke dashboard.</p>
            </div>

            <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
              <label class="flex flex-col gap-2">
                <span class="text-sm font-bold text-slate-200">Username</span>
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
                <span class="text-sm font-bold text-slate-200">Password</span>
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
                    class="absolute right-3 flex size-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-white/8 hover:text-white"
                    type="button"
                    :title="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                  >
                    <span class="material-symbols-outlined text-[20px]">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
                  </button>
                </div>
              </label>

              <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                  <span class="relative flex size-5 items-center justify-center rounded border border-white/15 bg-white/5">
                    <input v-model="rememberMe" class="peer sr-only" type="checkbox" />
                    <span class="material-symbols-outlined absolute inset-0 flex items-center justify-center rounded bg-emerald-400 text-[14px] text-[#0b1020] opacity-0 transition peer-checked:opacity-100">check</span>
                  </span>
                  <span class="text-sm font-medium text-slate-400">Ingat saya</span>
                </label>
                <router-link to="/" class="text-sm font-bold text-emerald-300 transition hover:text-emerald-200">
                  Kembali ke web
                </router-link>
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
                class="mt-2 inline-flex h-13 items-center justify-center gap-2 rounded-xl bg-emerald-400 px-5 font-black text-[#07111f] shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-300 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
              >
                <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
                <span>{{ isLoading ? 'Memproses...' : 'Masuk Dashboard' }}</span>
                <span v-if="!isLoading" class="material-symbols-outlined text-[20px]">arrow_forward</span>
              </button>
            </form>
          </div>

          <p class="mt-6 text-center text-xs font-medium text-slate-500">
            © {{ currentYear }} UII Dalwa Press. Hak cipta dilindungi.
          </p>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const username = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const errorMsg = ref('')
const isLoading = ref(false)
const currentYear = new Date().getFullYear()

const highlights = [
  { icon: 'article', label: 'News' },
  { icon: 'edit_note', label: 'Editorial' },
  { icon: 'admin_panel_settings', label: 'Access' },
]

async function handleLogin() {
  errorMsg.value = ''

  if (!username.value || !password.value) {
    errorMsg.value = 'Username dan password harus diisi.'
    return
  }

  isLoading.value = true

  try {
    const data = await authStore.login(username.value, password.value)
    const roleName = data.user?.role?.name
    if (roleName === 'Author') router.push({ name: 'AuthorDashboard' })
    else if (roleName === 'Editor') router.push({ name: 'EditorDashboard' })
    else router.push({ name: 'AdminDashboard' })
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
</script>

<style scoped>
.field-shell {
  position: relative;
  display: flex;
  align-items: center;
}
.field-icon {
  position: absolute;
  left: 1rem;
  color: rgb(148 163 184);
  font-size: 21px;
  pointer-events: none;
}
.field-input {
  height: 3.5rem;
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.06);
  padding-left: 3rem;
  padding-right: 1rem;
  color: white;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}
.field-input::placeholder {
  color: rgb(100 116 139);
}
.field-input:focus {
  border-color: rgb(110 231 183);
  background: rgba(255, 255, 255, 0.08);
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.16);
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
