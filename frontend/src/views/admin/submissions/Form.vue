<template>
  <div class="flex flex-col gap-6">
    <div>
      <h2 class="text-xl font-black" style="color: var(--text-heading)">{{ isEdit ? 'Edit Submission' : 'Tambah Submission' }}</h2>
      <p class="text-sm" style="color: var(--text-muted)">Tambah atau edit data submission naskah.</p>
    </div>
    
    <div v-if="loadingInit" class="flex justify-center py-16">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <form v-else @submit.prevent="submitForm" class="flex flex-col gap-6 rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
      
      <!-- Row 1 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="field">
          Judul buku *
          <input v-model="form.title" type="text" class="input" required placeholder="Masukkan judul buku" />
        </label>
        <label class="field">
          Kategori *
          <select v-model="form.category_id" class="input" required>
            <option value="" disabled>Pilih Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </label>
      </div>

      <!-- Row 2: Authors Section -->
      <div class="rounded-xl p-4 border" style="border-color: var(--border); background: var(--bg-input)">
        <div class="flex items-center justify-between mb-3">
          <div>
            <span class="text-sm font-black" style="color: var(--text-heading)">Penulis / Authors *</span>
            <p class="text-xs" style="color: var(--text-muted)">Tambahkan satu atau lebih penulis untuk naskah ini.</p>
          </div>
          <button type="button" @click="addAuthor" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-bold bg-accent text-[var(--text-btn)]">
            <span class="material-symbols-outlined text-[16px]">add</span>
            Tambah Penulis
          </button>
        </div>

        <div class="space-y-3">
          <div v-for="(auth, idx) in authorsList" :key="idx" class="rounded-xl p-3 border bg-[var(--bg-card)] flex flex-col gap-3" style="border-color: var(--border)">
            <div class="flex items-center justify-between border-b pb-2" style="border-color: var(--border)">
              <span class="text-xs font-bold text-accent">Penulis #{{ idx + 1 }} {{ idx === 0 ? '(Penulis Utama)' : '' }}</span>
              <button
                v-if="authorsList.length > 1"
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

      <!-- Row 3 -->
      <label class="field">
        Deskripsi
        <textarea v-model="form.description" class="input min-h-[100px]" placeholder="Deskripsi..."></textarea>
      </label>

      <!-- Row 4 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="field">
          Upload naskah {{ !isEdit ? '*' : '' }}
          <input type="file" @change="e => fileNaskah = e.target.files[0]" class="input" accept=".pdf,.doc,.docx" :required="!isEdit" />
          <small v-if="isEdit" style="color: var(--text-muted)">Kosongkan jika tidak ingin mengubah naskah</small>
        </label>
        <label class="field">
          Upload cover
          <input type="file" @change="e => coverImage = e.target.files[0]" class="input" accept="image/*" />
          <small v-if="isEdit" style="color: var(--text-muted)">Kosongkan jika tidak ingin mengubah cover</small>
        </label>
      </div>

      <!-- Row 5 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4" v-if="isEdit">
        <label class="field">
          Status
          <select v-model="form.status" class="input">
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
            <option value="revision">Revision</option>
            <option value="accepted">Accepted</option>
            <option value="rejected">Rejected</option>
            <option value="published">Published</option>
          </select>
        </label>
      </div>

      <!-- Row 6 -->
      <label class="field">
        Catatan
        <textarea v-model="form.note" class="input min-h-[100px]" placeholder="Catatan..."></textarea>
      </label>

      <!-- Buttons -->
      <div class="flex items-center gap-3 pt-4" style="border-top: 1px solid var(--border)">
        <button type="submit" class="btn-primary" :disabled="submitting">
          <span v-if="submitting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
          {{ submitting ? 'Menyimpan...' : 'Simpan' }}
        </button>
        <router-link to="/administrator/submissions" class="btn-soft">Batal</router-link>
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

const isEdit = computed(() => route.name === 'AdminSubmissionEdit')
const categories = ref([])
const loadingInit = ref(true)
const submitting = ref(false)

const fileNaskah = ref(null)
const coverImage = ref(null)
const authorsList = ref([
  { name: '', email: '', phone: '', role: 'Penulis Utama' }
])

const form = ref({
  title: '',
  category_id: '',
  author_name: '',
  email: '',
  phone: '',
  description: '',
  status: 'submitted',
  note: ''
})

function addAuthor() {
  authorsList.value.push({
    name: '',
    email: '',
    phone: '',
    role: authorsList.value.length === 0 ? 'Penulis Utama' : 'Penulis Pendamping'
  })
}

function removeAuthor(index) {
  if (authorsList.value.length > 1) {
    authorsList.value.splice(index, 1)
  }
}

onMounted(async () => {
  try {
    const catRes = await api.get('/book-categories?all=1')
    categories.value = catRes.data.data || catRes.data || []

    if (isEdit.value) {
      const { data } = await api.get(`/submissions/${route.params.id}`)
      const d = data.data || data
      form.value = {
        title: d.title || '',
        category_id: d.category_id || '',
        author_name: d.author_name || '',
        email: d.email || '',
        phone: d.phone || '',
        description: d.description || '',
        status: d.status || 'submitted',
        note: d.note || ''
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
    }
  } catch (err) {
    console.error(err)
    alert('Gagal mengambil data.')
  } finally {
    loadingInit.value = false
  }
})

async function submitForm() {
  submitting.value = true
  try {
    const validAuthors = authorsList.value.filter(a => a.name.trim())
    if (!validAuthors.length) {
      alert('Silakan masukkan minimal 1 nama penulis.')
      submitting.value = false
      return
    }

    form.value.author_name = validAuthors.map(a => a.name.trim()).join(', ')
    form.value.email = validAuthors[0].email || form.value.email
    form.value.phone = validAuthors[0].phone || form.value.phone

    const formData = new FormData()
    Object.entries(form.value).forEach(([key, val]) => {
      if (val !== null && val !== undefined) formData.append(key, val)
    })

    formData.append('author_names_json', JSON.stringify(validAuthors))
    validAuthors.forEach((item, idx) => {
      formData.append(`authors[${idx}][name]`, item.name.trim())
      if (item.author_id) formData.append(`authors[${idx}][author_id]`, item.author_id)
      if (item.email) formData.append(`authors[${idx}][email]`, item.email)
      if (item.phone) formData.append(`authors[${idx}][phone]`, item.phone)
      if (item.role) formData.append(`authors[${idx}][role]`, item.role)
    })

    if (fileNaskah.value) formData.append('manuscript_file', fileNaskah.value)
    if (coverImage.value) formData.append('cover_file', coverImage.value)

    if (isEdit.value) {
      formData.append('_method', 'PUT')
      await api.post(`/submissions/${route.params.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await api.post('/submissions', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    router.push('/administrator/submissions')
  } catch (err) {
    console.error(err)
    alert(err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.')
  } finally {
    submitting.value = false
  }
}
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
