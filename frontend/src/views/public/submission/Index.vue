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

        <!-- Multi-Author Section -->
        <div class="rounded-2xl p-5 border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/40">
          <div class="flex items-center justify-between mb-4">
            <div>
              <span class="text-sm font-black text-slate-800 dark:text-slate-100">Penulis / Authors *</span>
              <p class="text-xs text-slate-500">Anda dapat mendaftarkan lebih dari satu penulis jika naskah ini merupakan karya bersama.</p>
            </div>
            <button
              type="button"
              @click="addAuthor"
              class="inline-flex items-center gap-1 rounded-xl px-3 py-1.5 text-xs font-bold bg-sky-600 text-white hover:bg-sky-700 transition"
            >
              <span class="material-symbols-outlined text-[16px]">add</span>
              Tambah Penulis
            </button>
          </div>

          <div class="space-y-3">
            <div
              v-for="(auth, idx) in authorsList"
              :key="idx"
              class="rounded-xl p-4 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col gap-3 shadow-sm"
            >
              <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">
                  Penulis #{{ idx + 1 }} {{ idx === 0 ? '(Penulis Utama)' : '(Co-Author)' }}
                </span>
                <button
                  v-if="authorsList.length > 1 && idx > 0"
                  type="button"
                  @click="removeAuthor(idx)"
                  class="text-xs font-bold text-rose-500 hover:text-rose-600 inline-flex items-center gap-0.5"
                >
                  <span class="material-symbols-outlined text-[16px]">delete</span>
                  Hapus
                </button>
              </div>

              <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="field">
                  <span>Nama Lengkap *</span>
                  <input v-model="auth.name" required class="input" placeholder="Nama penulis..." />
                </label>
                <label class="field">
                  <span>Email</span>
                  <input v-model="auth.email" type="email" class="input" placeholder="email@domain.com" />
                </label>
                <label class="field">
                  <span>Phone / WhatsApp</span>
                  <input v-model="auth.phone" class="input" placeholder="08xxxxxxxxxx" />
                </label>
                <label class="field">
                  <span>Instansi / Afiliasi</span>
                  <input v-model="auth.institution" class="input" placeholder="Universitas / Lembaga" />
                </label>
              </div>
            </div>
          </div>
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
const authorsList = ref([
  { name: '', email: '', phone: '', institution: '', role: 'Penulis Utama' }
])
const form = ref({
  category_id: '',
  title: '',
  author_name: '',
  email: '',
  phone: '',
  description: '',
  note: '',
})

function addAuthor() {
  authorsList.value.push({
    name: '',
    email: '',
    phone: '',
    institution: '',
    role: 'Penulis Pendamping'
  })
}

function removeAuthor(index) {
  if (authorsList.value.length > 1 && index > 0) {
    authorsList.value.splice(index, 1)
  }
}

async function submit() {
  saving.value = true
  message.value = ''
  success.value = false
  try {
    const validAuthors = authorsList.value.filter(a => a.name.trim())
    if (!validAuthors.length) {
      message.value = 'Silakan masukkan minimal 1 nama penulis.'
      saving.value = false
      return
    }

    form.value.author_name = validAuthors.map(a => a.name.trim()).join(', ')
    form.value.email = validAuthors[0].email || form.value.email
    form.value.phone = validAuthors[0].phone || form.value.phone

    const fd = new FormData()
    Object.entries(form.value).forEach(([key, value]) => {
      if (value) fd.append(key, value)
    })

    fd.append('author_names_json', JSON.stringify(validAuthors))
    validAuthors.forEach((item, idx) => {
      fd.append(`authors[${idx}][name]`, item.name.trim())
      if (item.email) fd.append(`authors[${idx}][email]`, item.email)
      if (item.phone) fd.append(`authors[${idx}][phone]`, item.phone)
      if (item.institution) fd.append(`authors[${idx}][institution]`, item.institution)
      if (item.role) fd.append(`authors[${idx}][role]`, item.role)
    })

    if (manuscriptFile.value) fd.append('manuscript_file', manuscriptFile.value)
    if (coverFile.value) fd.append('cover_file', coverFile.value)
    await api.post('/submissions', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    success.value = true
    message.value = 'Submission berhasil dikirim. Tim editorial akan meninjau naskah Anda.'
    form.value = { category_id: '', title: '', author_name: '', email: '', phone: '', description: '', note: '' }
    authorsList.value = [{ name: '', email: '', phone: '', institution: '', role: 'Penulis Utama' }]
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
