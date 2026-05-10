<template>
  <div class="font-display antialiased bg-[#0a1128] text-slate-100 h-screen w-screen overflow-hidden flex">
    <div class="flex w-full h-full">

      <!-- ═══════ LEFT: BRANDING PANEL ═══════ -->
      <div class="hidden lg:flex w-[60%] relative bg-cover bg-center"
           style="background-image: url('/img/hero-bg.jpg')">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-[#0a1128]/80 bg-linear-to-t from-[#0a1128] via-transparent to-transparent"></div>
        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-center p-16 h-full w-full">
          <div class="flex items-center gap-3 mb-4">
            <span class="material-symbols-outlined text-accent text-4xl">security</span>
            <h2 class="text-accent text-xl font-medium tracking-widest uppercase">Secure Portal</h2>
          </div>
          <h1 class="font-serif text-7xl text-white font-black leading-tight tracking-tight mb-6 drop-shadow-lg">
            UII Dalwa Press
          </h1>
          <p class="text-white/80 text-2xl font-light max-w-xl leading-relaxed">
            Portal administratif untuk mengelola publikasi, berita, dan artikel UII Dalwa Press.
          </p>
        </div>
      </div>

      <!-- ═══════ RIGHT: LOGIN FORM PANEL ═══════ -->
      <div class="w-full lg:w-[40%] flex flex-col bg-[#111b3d] border-l-0 lg:border-l border-[#1c2c5c] shadow-2xl z-20 overflow-y-auto">
        <!-- Mobile header -->
        <div class="lg:hidden p-8 pb-0 flex flex-col gap-2">
          <h1 class="font-serif text-4xl text-white font-black leading-tight tracking-tight">
            UII Dalwa Press
          </h1>
          <p class="text-accent/80 text-sm font-medium uppercase tracking-wider">
            Secure Portal
          </p>
        </div>

        <!-- Form Content -->
        <div class="flex-1 flex flex-col justify-center p-8 lg:p-16 max-w-md mx-auto w-full">
          <div class="mb-10 text-center lg:text-left">
            <h2 class="text-3xl font-bold text-white mb-2 font-serif">Selamat Datang</h2>
            <p class="text-slate-400">Masukkan kredensial Anda untuk mengakses dashboard admin.</p>
          </div>

          <form @submit.prevent="handleLogin" class="flex flex-col gap-6 w-full">
            <!-- Username -->
            <label class="flex flex-col gap-2">
              <span class="text-sm font-medium text-accent/90">Username</span>
              <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-4 text-slate-500">person</span>
                <input v-model="username"
                       class="w-full h-14 pl-12 pr-4 rounded-xl bg-[#1a254c] border border-[#1c2c5c] text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-shadow"
                       placeholder="admin@uiidalwapress.com"
                       type="text" />
              </div>
            </label>

            <!-- Password -->
            <label class="flex flex-col gap-2">
              <span class="text-sm font-medium text-accent/90">Password</span>
              <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-4 text-slate-500">lock</span>
                <input v-model="password"
                       :type="showPassword ? 'text' : 'password'"
                       class="w-full h-14 pl-12 pr-12 rounded-xl bg-[#1a254c] border border-[#1c2c5c] text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-shadow"
                       placeholder="••••••••" />
                <button @click.prevent="showPassword = !showPassword"
                        class="absolute right-4 text-slate-400 hover:text-white transition-colors cursor-pointer"
                        type="button">
                  <span class="material-symbols-outlined text-sm">{{ showPassword ? 'visibility' : 'visibility_off' }}</span>
                </button>
              </div>
            </label>

            <!-- Remember me / Forgot -->
            <div class="flex items-center justify-between mt-1">
              <label class="flex items-center gap-2 cursor-pointer group">
                <div class="relative flex items-center justify-center w-5 h-5 rounded border border-slate-600 bg-[#1a254c] group-hover:border-accent transition-colors">
                  <input v-model="rememberMe" class="peer sr-only" type="checkbox" />
                  <span class="material-symbols-outlined text-[14px] text-[#0a1128] opacity-0 peer-checked:opacity-100 bg-accent absolute inset-0 flex items-center justify-center rounded transition-opacity">check</span>
                </div>
                <span class="text-sm text-slate-400">Ingat saya</span>
              </label>
              <a class="text-sm font-medium text-accent hover:text-accent/80 transition-colors cursor-pointer">Lupa Password?</a>
            </div>

            <!-- Error message -->
            <Transition name="fade">
              <div v-if="errorMsg" class="flex items-center gap-2 p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                <span class="material-symbols-outlined text-lg">error</span>
                {{ errorMsg }}
              </div>
            </Transition>

            <!-- Submit Button -->
            <div class="mt-2 flex justify-end">
              <button type="submit"
                      :disabled="isLoading"
                      class="group flex items-center justify-center gap-3 bg-accent hover:bg-accent/90 text-[#0a1128] font-bold text-lg h-16 w-16 rounded-full shadow-lg hover:shadow-accent/30 transition-all hover:w-40 hover:rounded-xl cursor-pointer active:scale-95 disabled:opacity-50">
                <span v-if="isLoading" class="material-symbols-outlined animate-spin">progress_activity</span>
                <template v-else>
                  <span class="hidden group-hover:inline-block ml-2">Masuk</span>
                  <span class="material-symbols-outlined">arrow_forward</span>
                </template>
              </button>
            </div>
          </form>

          <!-- Footer -->
          <div class="mt-8 text-center">
            <p class="text-xs text-slate-500">© 2026 UII Dalwa Press. Hak cipta dilindungi.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

// ── Form state ──
const username = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const errorMsg = ref('')
const isLoading = ref(false)

// ── Login handler ──
async function handleLogin() {
  errorMsg.value = ''

  if (!username.value || !password.value) {
    errorMsg.value = 'Username dan password harus diisi.'
    return
  }

  isLoading.value = true

  try {
    await authStore.login(username.value, password.value)
    router.push({ name: 'AdminDashboard' })
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation error dari Laravel
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
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
