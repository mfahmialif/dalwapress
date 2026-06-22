<template>
  <div class="flex flex-col gap-6">
    <div class="flex items-center gap-4">
      <router-link to="/kepala-penulis/news" class="flex items-center gap-1 text-sm font-medium" style="color: var(--text-muted)"><span class="material-symbols-outlined text-[20px]">arrow_back</span>Kembali</router-link>
      <h2 class="text-lg font-bold" style="color: var(--text-heading)">{{ isEdit ? 'Edit Artikel Penulis' : 'Tambah Artikel Penulis' }}</h2>
    </div>
    <div class="form-card rounded-2xl p-6 flex flex-col gap-5">
      <div class="field">
        <label>Penulis *</label>
        <select v-model="form.author_id" class="filter-input">
          <option value="" disabled>Pilih penulis</option>
          <option v-for="writer in writers" :key="writer.id" :value="writer.id">{{ writer.name }}</option>
        </select>
      </div>
      <div class="field">
        <label>Judul *</label>
        <input v-model="form.title" class="filter-input" placeholder="Judul artikel" />
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <div class="field">
          <label>Kategori *</label>
          <VueMultiselect v-model="formCategoryOptions" :options="categoryOptions" :multiple="true" :close-on-select="false" :searchable="true" :allow-empty="false" :show-labels="false" label="name" track-by="id" placeholder="Pilih kategori" />
        </div>
        <div class="field">
          <label>Status</label>
          <select v-model="form.status" class="filter-input">
            <option value="Published">Published</option>
            <option value="Draft">Draft</option>
          </select>
        </div>
      </div>
      <div class="field">
        <label>Thumbnail</label>
        <input type="file" accept="image/*" class="filter-input" @change="imageFile = $event.target.files?.[0] || null" />
        <img v-if="imagePreview" :src="imagePreview" class="mt-3 h-36 w-56 rounded-lg object-cover" />
      </div>
      <div class="field">
        <label>Isi Konten</label>
        <textarea v-model="form.body" class="filter-input min-h-72 resize-y" placeholder="Tulis konten artikel. HTML sederhana diperbolehkan."></textarea>
      </div>
      <div v-if="error" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">{{ error }}</div>
      <div class="flex justify-end gap-3">
        <router-link to="/kepala-penulis/news" class="rounded-lg px-5 py-2.5 text-sm font-bold" style="border:1px solid var(--border); color: var(--text-body)">Batal</router-link>
        <button @click="submit" :disabled="saving" class="rounded-lg bg-accent px-5 py-2.5 text-sm font-bold disabled:opacity-60" style="color: var(--text-btn)">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import VueMultiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const route = useRoute()
const router = useRouter()
const isEdit = computed(() => !!route.params.id)
const categories = ref([])
const categoryOptions = computed(() => categories.value.map(item => ({ ...item, name: `${item.name} (${item.type})` })))
const writers = ref([])
const imageFile = ref(null)
const imagePreview = ref('')
const saving = ref(false)
const error = ref('')
const form = ref({ title: '', news_category_ids: [], author_id: '', status: 'Published', body: '' })
const formCategoryOptions = computed({
  get: () => categoryOptions.value.filter(item => form.value.news_category_ids.includes(item.id)),
  set: (value) => { form.value.news_category_ids = (value || []).map(item => item.id) }
})

async function loadOptions(){
  categories.value = (await api.get('/news-categories')).data || []
  writers.value = (await api.get('/kepala-penulis/writers/options')).data || []
  if (!form.value.news_category_ids.length && categories.value.length) form.value.news_category_ids = [categories.value[0].id]
  if (!form.value.author_id && writers.value.length) form.value.author_id = writers.value[0].id
}
async function loadItem(){
  const { data } = await api.get(`/kepala-penulis/news/${route.params.id}`)
  const ids = (data.categories?.length ? data.categories : [data.category].filter(Boolean)).map(item => item.id)
  form.value = { title: data.title || '', news_category_ids: ids.length ? ids : [], author_id: data.author_id || data.author?.id || '', status: data.status || 'Published', body: data.body || '' }
  if (data.image_path) imagePreview.value = storageUrl(data.image_path)
}
async function submit(){
  saving.value = true; error.value = ''
  try {
    const fd = new FormData()
    fd.append('title', form.value.title)
    fd.append('author_id', form.value.author_id)
    fd.append('status', form.value.status)
    if (form.value.body) fd.append('body', form.value.body)
    form.value.news_category_ids.forEach(id => fd.append('news_category_ids[]', id))
    if (imageFile.value) fd.append('image', imageFile.value)
    if (isEdit.value) {
      fd.append('_method', 'PUT')
      await api.post(`/kepala-penulis/news/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    } else {
      await api.post('/kepala-penulis/news', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    }
    router.push('/kepala-penulis/news')
  } catch (e) {
    const errors = e.response?.data?.errors
    error.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Gagal menyimpan artikel.')
  } finally { saving.value = false }
}
onMounted(async () => { await loadOptions(); if (isEdit.value) await loadItem() })
</script>

<style scoped>
.form-card{background:var(--bg-card);border:1px solid var(--border);box-shadow:var(--shadow-card)}.field{display:flex;flex-direction:column;gap:.4rem}.field label{font-size:.88rem;font-weight:800;color:var(--text-body)}.filter-input{width:100%;border-radius:.75rem;background:var(--bg-input);border:1px solid var(--border);color:var(--text-heading);padding:.7rem .9rem;outline:none}.filter-input:focus{border-color:var(--color-accent);box-shadow:0 0 12px rgba(37,99,235,.28)}
</style>
