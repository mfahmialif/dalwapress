<template>
  <main class="auth-page min-h-screen" :data-theme="resolvedTheme">
    <button class="auth-theme-toggle" type="button" @click="toggleTheme">
      <span class="material-symbols-outlined text-[20px]">{{ resolvedTheme === 'dark' ? 'light_mode' : 'dark_mode' }}</span>
    </button>

    <section class="flex min-h-screen items-center justify-center px-5 py-10">
      <div class="auth-card w-full max-w-[480px] rounded-2xl p-6 shadow-2xl sm:p-8">
        <router-link to="/login" class="auth-back mb-7 inline-flex items-center gap-2 text-sm font-black">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Login
        </router-link>

        <div class="mb-8">
          <p class="auth-eyebrow text-sm font-bold uppercase tracking-[0.2em]">Daftar Email</p>
          <h1 class="auth-title mt-3 text-3xl font-black tracking-tight">Buat Akun</h1>
        </div>

        <form class="flex flex-col gap-5" @submit.prevent="handleRegister">
          <label class="flex flex-col gap-2">
            <span class="auth-label text-sm font-bold">Nama</span>
            <div class="field-shell">
              <span class="material-symbols-outlined field-icon">badge</span>
              <input v-model="form.name" class="field-input" placeholder="Nama lengkap" autocomplete="name" />
            </div>
          </label>

          <label class="flex flex-col gap-2">
            <span class="auth-label text-sm font-bold">Email</span>
            <div class="field-shell">
              <span class="material-symbols-outlined field-icon">mail</span>
              <input v-model="form.email" class="field-input" placeholder="nama@email.com" type="email" autocomplete="email" />
            </div>
          </label>

          <label class="flex flex-col gap-2">
            <span class="auth-label text-sm font-bold">Username</span>
            <div class="field-shell">
              <span class="material-symbols-outlined field-icon">alternate_email</span>
              <input v-model="form.username" class="field-input pr-12" placeholder="username" autocomplete="username" @input="scheduleUsernameCheck" />
              <span v-if="checkingUsername" class="status-icon material-symbols-outlined animate-spin">progress_activity</span>
              <span v-else-if="usernameAvailable === true" class="status-icon material-symbols-outlined ok">check_circle</span>
              <span v-else-if="usernameAvailable === false" class="status-icon material-symbols-outlined bad">cancel</span>
            </div>
            <p v-if="usernameAvailable === false" class="text-xs font-bold text-red-400">Username sudah digunakan.</p>
          </label>

          <label class="flex flex-col gap-2">
            <span class="auth-label text-sm font-bold">Password</span>
            <div class="field-shell">
              <span class="material-symbols-outlined field-icon">lock</span>
              <input v-model="form.password" class="field-input" placeholder="Minimal 6 karakter" type="password" autocomplete="new-password" />
            </div>
          </label>

          <label class="flex flex-col gap-2">
            <span class="auth-label text-sm font-bold">Ulangi Password</span>
            <div class="field-shell">
              <span class="material-symbols-outlined field-icon">lock_reset</span>
              <input v-model="form.password_confirmation" class="field-input" placeholder="Ulangi password" type="password" autocomplete="new-password" />
            </div>
          </label>

          <Transition name="fade">
            <div v-if="message.text" class="message-box" :class="message.type">
              <span class="material-symbols-outlined text-[20px]">{{ message.type === 'success' ? 'check_circle' : 'error' }}</span>
              <span>{{ message.text }}</span>
            </div>
          </Transition>

          <button class="auth-submit mt-1 inline-flex h-13 items-center justify-center gap-2 rounded-xl px-5 font-black shadow-lg transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60" :disabled="isLoading">
            <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
            <span>{{ isLoading ? 'Mengirim kode...' : 'Daftar dan Kirim Kode' }}</span>
          </button>
        </form>
      </div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../axios'
import { usePublicTheme } from '../usePublicTheme'

const router = useRouter()
const { resolvedTheme, initTheme, toggleTheme } = usePublicTheme()
const isLoading = ref(false)
const checkingUsername = ref(false)
const usernameAvailable = ref(null)
let usernameTimer = null

const form = reactive({
  name: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
})
const message = reactive({ type: '', text: '' })

function scheduleUsernameCheck() {
  usernameAvailable.value = null
  clearTimeout(usernameTimer)
  if (!form.username || form.username.length < 3) return
  usernameTimer = setTimeout(checkUsername, 450)
}

async function checkUsername() {
  checkingUsername.value = true
  try {
    const { data } = await api.get('/auth/check-username', {
      params: { username: form.username },
      skipAuthRedirect: true,
    })
    usernameAvailable.value = data.available
  } catch {
    usernameAvailable.value = null
  } finally {
    checkingUsername.value = false
  }
}

async function handleRegister() {
  message.text = ''

  if (form.password !== form.password_confirmation) {
    message.type = 'error'
    message.text = 'Konfirmasi password tidak sama.'
    return
  }

  isLoading.value = true
  try {
    const { data } = await api.post('/register', form)
    message.type = 'success'
    message.text = data.message
    router.push({ name: 'VerifyEmail', query: { email: data.email } })
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    message.type = 'error'
    message.text = Object.values(errors)?.[0]?.[0] || 'Pendaftaran gagal. Periksa data dan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  initTheme()
})
</script>

<style scoped src="../login/auth-shared.css"></style>
