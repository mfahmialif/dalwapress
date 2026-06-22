<template>
  <main class="auth-page min-h-screen" :data-theme="resolvedTheme">
    <button class="auth-theme-toggle" type="button" @click="toggleTheme">
      <span class="material-symbols-outlined text-[20px]">{{ resolvedTheme === 'dark' ? 'light_mode' : 'dark_mode' }}</span>
    </button>

    <section class="flex min-h-screen items-center justify-center px-5 py-10">
      <div class="auth-card w-full max-w-[460px] rounded-2xl p-6 shadow-2xl sm:p-8">
        <router-link to="/register" class="auth-back mb-7 inline-flex items-center gap-2 text-sm font-black">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Daftar
        </router-link>

        <div class="mb-8">
          <p class="auth-eyebrow text-sm font-bold uppercase tracking-[0.2em]">Verifikasi Email</p>
          <h1 class="auth-title mt-3 text-3xl font-black tracking-tight">Masukkan Kode</h1>
          <p class="auth-copy mt-3 leading-7">Kode 6 digit dikirim ke <strong>{{ email }}</strong>.</p>
        </div>

        <form class="flex flex-col gap-5" @submit.prevent="handleVerify">
          <label class="flex flex-col gap-2">
            <span class="auth-label text-sm font-bold">Kode Verifikasi</span>
            <input v-model="code" class="code-input" maxlength="6" placeholder="000000" inputmode="numeric" autocomplete="one-time-code" />
          </label>

          <Transition name="fade">
            <div v-if="message.text" class="message-box" :class="message.type">
              <span class="material-symbols-outlined text-[20px]">{{ message.type === 'success' ? 'check_circle' : 'error' }}</span>
              <span>{{ message.text }}</span>
            </div>
          </Transition>

          <button class="auth-submit mt-1 inline-flex h-13 items-center justify-center gap-2 rounded-xl px-5 font-black shadow-lg transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60" :disabled="isLoading">
            <span v-if="isLoading" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
            <span>{{ isLoading ? 'Memverifikasi...' : 'Verifikasi dan Masuk' }}</span>
          </button>
        </form>
      </div>
    </section>
  </main>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../../axios'
import { useAuthStore } from '../../../stores/auth'
import { usePublicTheme } from '../usePublicTheme'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const { resolvedTheme, initTheme, toggleTheme } = usePublicTheme()
const email = ref(route.query.email || '')
const code = ref('')
const isLoading = ref(false)
const message = reactive({ type: '', text: '' })

function redirectToDashboard(roleName) {
  if (roleName === 'Author') router.push({ name: 'AuthorDashboard' })
  else if (roleName === 'Editor') router.push({ name: 'EditorDashboard' })
  else if (roleName === 'Penulis') router.push({ name: 'PenulisDashboard' })
  else if (roleName === 'Kepala Penulis') router.push({ name: 'KepalaPenulisDashboard' })
  else router.push({ name: 'AdminDashboard' })
}

async function handleVerify() {
  message.text = ''
  isLoading.value = true
  try {
    const { data } = await api.post('/verify-email-code', { email: email.value, code: code.value })
    authStore.user = data.user
    authStore.hasCheckedSession = true
    redirectToDashboard(data.user?.role?.name)
  } catch (error) {
    const errors = error.response?.data?.errors || {}
    message.type = 'error'
    message.text = Object.values(errors)?.[0]?.[0] || 'Verifikasi gagal. Periksa kode dan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  initTheme()
  if (!email.value) router.replace({ name: 'Register' })
})
</script>

<style scoped src="../login/auth-shared.css"></style>
