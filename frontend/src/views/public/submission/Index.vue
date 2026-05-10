<template>
  <main class="submission-page">
    <section class="submission-shell">
      <div class="submission-intro" data-aos="fade-up">
        <span class="submission-pill">
          <span class="material-symbols-outlined text-[18px]">upload_file</span>
          Author Submission
        </span>
        <h1>Kirim naskah terbaikmu ke UII Dalwa Press.</h1>
        <p>
          Author dapat mengirim proposal atau manuskrip lengkap untuk direview oleh tim editorial. Status awal akan tercatat sebagai submitted.
        </p>
      </div>

      <form class="submission-form" data-aos="fade-up" data-aos-delay="120" @submit.prevent="submit">
        <div class="grid gap-4 lg:grid-cols-2">
          <label class="field"><span>Judul Naskah *</span><input v-model="form.title" required class="input" /></label>
          <label class="field">
            <span>Kategori *</span>
            <select v-model="form.category_id" required class="input">
              <option value="">Pilih kategori</option>
              <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
            </select>
          </label>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
          <label class="field"><span>Nama Author *</span><input v-model="form.author_name" required class="input" /></label>
          <label class="field"><span>Email</span><input v-model="form.email" type="email" class="input" /></label>
          <label class="field"><span>Phone</span><input v-model="form.phone" class="input" /></label>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
          <label class="field"><span>File Manuskrip</span><input class="input" type="file" accept=".pdf,.doc,.docx" @change="manuscriptFile = $event.target.files[0]" /></label>
          <label class="field"><span>Cover</span><input class="input" type="file" accept="image/*" @change="coverFile = $event.target.files[0]" /></label>
        </div>

        <label class="field"><span>Deskripsi</span><textarea v-model="form.description" class="input min-h-[130px]"></textarea></label>
        <label class="field"><span>Catatan untuk Redaksi</span><textarea v-model="form.note" class="input min-h-[110px]"></textarea></label>

        <div v-if="message" class="submission-message" :class="{ success }">{{ message }}</div>

        <button :disabled="saving" class="submission-btn">
          <span class="material-symbols-outlined">{{ saving ? 'progress_activity' : 'send' }}</span>
          {{ saving ? 'Mengirim...' : 'Kirim Submission' }}
        </button>
      </form>
    </section>
  </main>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import AOS from 'aos'
import api from '../../../axios'

const categories = ref([])
const manuscriptFile = ref(null)
const coverFile = ref(null)
const saving = ref(false)
const message = ref('')
const success = ref(false)
const form = ref({
  category_id: '',
  title: '',
  author_name: '',
  email: '',
  phone: '',
  description: '',
  note: '',
})

async function submit() {
  saving.value = true
  message.value = ''
  success.value = false
  try {
    const fd = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
      if (value) fd.append(key, value)
    })
    if (manuscriptFile.value) fd.append('manuscript_file', manuscriptFile.value)
    if (coverFile.value) fd.append('cover_file', coverFile.value)
    await api.post('/submissions', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    success.value = true
    message.value = 'Submission berhasil dikirim. Tim editorial akan meninjau naskah Anda.'
    form.value = { category_id: '', title: '', author_name: '', email: '', phone: '', description: '', note: '' }
    manuscriptFile.value = null
    coverFile.value = null
  } catch (e) {
    const errors = e.response?.data?.errors
    message.value = errors ? Object.values(errors).flat().join(' ') : 'Submission gagal dikirim.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  AOS.refresh()
  const { data } = await api.get('/book-categories', { params: { all: 1 } })
  categories.value = data || []
})
</script>

<style scoped>
.submission-page {
  min-height: 100vh;
  background: radial-gradient(circle at 15% 0%, rgba(14, 165, 233, 0.16), transparent 30rem), #f8fbff;
  padding: 9.5rem 1rem 5rem;
}
.submission-shell {
  width: min(1080px, 100%);
  margin: 0 auto;
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: 2rem;
  align-items: start;
}
.submission-intro h1 {
  margin-top: 1.1rem;
  font-size: clamp(2.2rem, 5vw, 4.8rem);
  line-height: 0.98;
  font-weight: 950;
  color: #0f172a;
}
.submission-intro p {
  margin-top: 1rem;
  color: #475569;
  line-height: 1.8;
}
.submission-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 999px;
  background: rgba(14, 165, 233, 0.12);
  color: #0369a1;
  padding: 0.5rem 0.85rem;
  font-weight: 950;
  font-size: 0.82rem;
}
.submission-form {
  display: grid;
  gap: 1rem;
  border-radius: 2rem;
  border: 1px solid rgba(148, 163, 184, 0.24);
  background: rgba(255, 255, 255, 0.9);
  padding: 1.3rem;
  box-shadow: 0 28px 80px rgba(15, 23, 42, 0.1);
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  color: #334155;
  font-weight: 900;
  font-size: 0.9rem;
}
.input {
  width: 100%;
  border: 1px solid #dbe7f2;
  border-radius: 1rem;
  padding: 0.85rem 1rem;
  color: #0f172a;
  background: #fff;
  outline: none;
}
.input:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.12);
}
.submission-message {
  border-radius: 1rem;
  border: 1px solid rgba(239, 68, 68, 0.25);
  background: rgba(239, 68, 68, 0.08);
  color: #b91c1c;
  padding: 0.9rem 1rem;
  font-weight: 800;
}
.submission-message.success {
  border-color: rgba(34, 197, 94, 0.3);
  background: rgba(34, 197, 94, 0.08);
  color: #15803d;
}
.submission-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.55rem;
  border-radius: 999px;
  background: #0f172a;
  color: #fff;
  padding: 0.9rem 1.2rem;
  font-weight: 950;
}
@media (max-width: 900px) {
  .submission-shell { grid-template-columns: 1fr; }
}
</style>
