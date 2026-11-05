<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">{{ isEdit ? 'Edit Submission' : 'Create Submission' }}</h2>
      <p class="text-sm" style="color: var(--text-muted)">Upload naskah hanya tersedia untuk author yang sudah login.</p>
    </div>

    <form class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)" @submit.prevent="submit">
      <div class="grid gap-4 lg:grid-cols-2">
        <label class="field"><span>Judul buku *</span><input v-model="form.title" class="input" required placeholder="Masukkan judul buku..." /></label>
        <label class="field">
          <span>Kategori *</span>
          <select v-model="form.category_id" class="input" required>
            <option value="">Pilih kategori</option>
            <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </label>
      </div>

      <!-- Multi-Author Section -->
      <div class="mt-4 rounded-xl p-4 border" style="border-color: var(--border); background: var(--bg-input)">
        <div class="flex items-center justify-between mb-3">
          <div>
            <span class="text-sm font-black" style="color: var(--text-heading)">Penulis / Authors *</span>
            <p class="text-xs" style="color: var(--text-muted)">Anda dapat menambahkan rekan penulis (co-author) bila buku ini ditulis bersama.</p>
          </div>
          <button type="button" @click="addAuthor" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold bg-accent text-[var(--text-btn)]">
            <span class="material-symbols-outlined text-[16px]">add</span>
            Tambah Penulis
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(auth, idx) in authorsList" :key="idx" class="rounded-xl p-3 border bg-[var(--bg-card)] flex flex-col gap-3" style="border-color: var(--border)">
            <div class="flex items-center justify-between border-b pb-2" style="border-color: var(--border)">
              <span class="text-xs font-bold text-accent">Penulis #{{ idx + 1 }} {{ idx === 0 ? '(Penulis Utama / Anda)' : '(Co-Author)' }}</span>
              <button
                v-if="authorsList.length > 1 && idx > 0"
                type="button"
                @click="removeAuthor(idx)"
                class="text-xs font-bold text-red-400 hover:text-red-300 inline-flex items-center gap-0.5"
              >
                <span class="material-symbols-outlined text-[16px]">delete</span>
                Hapus
              </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
              <label class="field">
                <span>Nama Lengkap *</span>
                <input v-model="auth.name" type="text" class="input py-2 text-sm" required placeholder="Nama lengkap" />
              </label>
              <label class="field">
                <span>Email</span>
                <input v-model="auth.email" type="email" class="input py-2 text-sm" placeholder="Email" />
              </label>
              <label class="field">
                <span>No. HP / WhatsApp</span>
                <input v-model="auth.phone" type="text" class="input py-2 text-sm" placeholder="Nomor telepon" />
              </label>
              <label class="field">
                <span>Peran</span>
                <select v-model="auth.role" class="input py-2 text-sm">
                  <option value="Penulis Utama">Penulis Utama</option>
                  <option value="Penulis Pendamping">Penulis Pendamping</option>
                  <option value="Editor / Penyusun">Editor / Penyusun</option>
                  <option value="Kontributor">Kontributor</option>
                </select>
              </label>
            </div>
          </div>
        </div>
      </div>

      <label class="field mt-4"><span>Deskripsi</span><textarea v-model="form.description" class="input min-h-32"></textarea></label>

      <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <label class="field">
          <span>Upload naskah {{ isEdit ? '' : '*' }}</span>
          <input class="input" type="file" accept=".pdf,.doc,.docx" :required="!isEdit" @change="manuscriptFile = $event.target.files[0]" />
          <small>PDF/DOCX/DOC, maksimal 50MB.</small>
        </label>
        <label class="field">
          <span>Upload cover</span>
          <input class="input" type="file" accept="image/*" @change="coverFile = $event.target.files[0]" />
        </label>
      </div>

      <label class="field mt-4"><span>Catatan tambahan</span><textarea v-model="form.note" class="input min-h-28"></textarea></label>

      <div v-if="message" class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm font-bold text-red-400">{{ message }}</div>

      <div class="mt-5 flex flex-wrap gap-3">
        <button class="btn-primary" :disabled="saving">
          <span class="material-symbols-outlined text-[18px]">{{ saving ? 'progress_activity' : 'send' }}</span>
          {{ saving ? 'Menyimpan...' : 'Simpan Submission' }}
        </button>
        <router-link to="/author/submissions" class="btn-soft">Batal</router-link>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../../axios'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const categories = ref([])
const manuscriptFile = ref(null)
const coverFile = ref(null)
const saving = ref(false)
const message = ref('')
const authorsList = ref([
  { name: '', email: '', phone: '', role: 'Penulis Utama' }
])
const form = ref({ author_name: '', email: '', phone: '', category_id: '', title: '', description: '', note: '' })

function addAuthor() {
  authorsList.value.push({
    name: '',
    email: '',
    phone: '',
    role: 'Penulis Pendamping'
  })
}

function removeAuthor(index) {
  if (authorsList.value.length > 1 && index > 0) {
    authorsList.value.splice(index, 1)
  }
}

async function loadData() {
  const { data } = await api.get('/book-categories', { params: { all: 1 } })
  categories.value = data || []

  if (isEdit.value) {
    const res = await api.get(`/author/submissions/${route.params.id}`)
    const d = res.data
    form.value = {
      author_name: d.author_name || '',
      email: d.email || '',
      phone: d.phone || '',
      category_id: d.category_id,
      title: d.title,
      description: d.description || '',
      note: d.note || '',
    }

    if (d.submission_authors && d.submission_authors.length > 0) {
      authorsList.value = d.submission_authors.map((sa, idx) => ({
        author_id: sa.author_id || null,
        name: sa.name || '',
        email: sa.email || '',
        phone: sa.phone || '',
        role: sa.role || (idx === 0 ? 'Penulis Utama' : 'Penulis Pendamping')
      }))
    } else if (d.author_name) {
      authorsList.value = [
        { name: d.author_name, email: d.email || '', phone: d.phone || '', role: 'Penulis Utama' }
      ]
    }
  } else {
    try {
      const { data: user } = await api.get('/user')
      const authorName = user.author?.name || user.name || ''
      const authorEmail = user.email || ''
      const authorPhone = user.author?.phone || ''
      form.value.author_name = authorName
      form.value.email = authorEmail
      form.value.phone = authorPhone

      authorsList.value = [
        {
          author_id: user.author?.id || null,
          name: authorName,
          email: authorEmail,
          phone: authorPhone,
          role: 'Penulis Utama'
        }
      ]
    } catch (e) {
      console.error('Failed to fetch user info', e)
    }
  }
}

async function submit() {
  saving.value = true
  message.value = ''
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
      if (value !== null && value !== undefined) fd.append(key, value)
    })

    fd.append('author_names_json', JSON.stringify(validAuthors))
    validAuthors.forEach((item, idx) => {
      fd.append(`authors[${idx}][name]`, item.name.trim())
      if (item.author_id) fd.append(`authors[${idx}][author_id]`, item.author_id)
      if (item.email) fd.append(`authors[${idx}][email]`, item.email)
      if (item.phone) fd.append(`authors[${idx}][phone]`, item.phone)
      if (item.role) fd.append(`authors[${idx}][role]`, item.role)
    })

    if (manuscriptFile.value) fd.append('manuscript_file', manuscriptFile.value)
    if (coverFile.value) fd.append('cover_file', coverFile.value)

    if (isEdit.value) {
      await api.post(`/author/submissions/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/author/submissions', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    router.push('/author/submissions')
  } catch (e) {
    const errors = e.response?.data?.errors
    message.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Submission gagal disimpan.')
  } finally {
    saving.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.field { display: flex; flex-direction: column; gap: 0.45rem; color: var(--text-body); font-size: 0.9rem; font-weight: 900; }
.field small { color: var(--text-muted); font-weight: 700; }
.input { border-radius: 0.85rem; background: var(--bg-input); border: 1px solid var(--border); color: var(--text-heading); padding: 0.75rem 0.9rem; outline: none; }
.input:focus { border-color: var(--color-accent); box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15); }
.btn-primary, .btn-soft { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; border-radius: 0.85rem; padding: 0.75rem 1rem; font-weight: 900; }
.btn-primary { background: var(--color-accent); color: var(--text-btn); }
.btn-soft { background: var(--bg-input); color: var(--text-heading); border: 1px solid var(--border); }
</style>
