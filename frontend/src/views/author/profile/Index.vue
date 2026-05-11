<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">Profile Author</h2>
      <p class="text-sm" style="color: var(--text-muted)">Kelola identitas author, media sosial, dan password.</p>
    </div>

    <form class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)" @submit.prevent="submit">
      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field"><span>Nama</span><input v-model="form.name" required class="input" /></label>
        <label class="field"><span>Email</span><input v-model="form.email" required type="email" class="input" /></label>
        <label class="field"><span>Phone</span><input v-model="form.phone" class="input" /></label>
        <label class="field"><span>Institusi</span><input v-model="form.institution" class="input" /></label>
      </div>
      <label class="field mt-4"><span>Foto</span><input class="input" type="file" accept="image/*" @change="photo = $event.target.files[0]" /></label>
      <label class="field mt-4"><span>Bio</span><textarea v-model="form.bio" class="input min-h-28"></textarea></label>
      <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <label class="field"><span>Website</span><input v-model="form.social_media.website" class="input" /></label>
        <label class="field"><span>Instagram</span><input v-model="form.social_media.instagram" class="input" /></label>
        <label class="field"><span>LinkedIn</span><input v-model="form.social_media.linkedin" class="input" /></label>
      </div>
      <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <label class="field"><span>Password baru</span><input v-model="form.password" type="password" class="input" /></label>
        <label class="field"><span>Konfirmasi password</span><input v-model="form.password_confirmation" type="password" class="input" /></label>
      </div>
      <div v-if="message" class="mt-4 rounded-xl border border-sky-500/30 bg-sky-500/10 p-4 text-sm font-bold text-sky-400">{{ message }}</div>
      <button class="btn-primary mt-5" :disabled="saving">
        <span class="material-symbols-outlined text-[18px]">{{ saving ? 'progress_activity' : 'save' }}</span>
        Simpan Profile
      </button>
    </form>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../../axios'

const saving = ref(false)
const message = ref('')
const photo = ref(null)
const form = ref({
  name: '',
  email: '',
  phone: '',
  institution: '',
  bio: '',
  social_media: { website: '', instagram: '', linkedin: '' },
  password: '',
  password_confirmation: '',
})

async function loadProfile() {
  const { data } = await api.get('/author/profile')
  form.value = {
    name: data.name || '',
    email: data.email || '',
    phone: data.author?.phone || '',
    institution: data.author?.institution || '',
    bio: data.author?.bio || '',
    social_media: {
      website: data.author?.social_media?.website || '',
      instagram: data.author?.social_media?.instagram || '',
      linkedin: data.author?.social_media?.linkedin || '',
    },
    password: '',
    password_confirmation: '',
  }
}

async function submit() {
  saving.value = true
  message.value = ''
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
      if (key === 'social_media') {
        Object.entries(value).forEach(([socialKey, socialValue]) => fd.append(`social_media[${socialKey}]`, socialValue || ''))
      } else if (value) {
        fd.append(key, value)
      }
    })
    if (photo.value) fd.append('photo', photo.value)
    await api.post('/author/profile', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    message.value = 'Profile berhasil diperbarui.'
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (e) {
    const errors = e.response?.data?.errors
    message.value = errors ? Object.values(errors).flat().join(' ') : 'Profile gagal disimpan.'
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.45rem; color: var(--text-body); font-size: 0.9rem; font-weight: 900; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.75rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.75rem 1rem; font-weight: 900; background: var(--color-accent); color: var(--text-btn); }
</style>
