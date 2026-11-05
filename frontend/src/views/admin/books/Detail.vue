<template>
  <div class="flex flex-col gap-6">
    <div v-if="loading" class="flex justify-center py-20">
      <span class="material-symbols-outlined animate-spin text-4xl text-accent">progress_activity</span>
    </div>

    <template v-else-if="book">
      <!-- Top Action Bar -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
          <router-link to="/administrator/books" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-700 bg-slate-800 text-slate-200 transition hover:bg-slate-700">
            <span class="material-symbols-outlined">arrow_back</span>
          </router-link>
          <div>
            <h1 class="text-xl font-black" style="color: var(--text-heading)">{{ book.title }}</h1>
            <p class="text-xs" style="color: var(--text-muted)">
              Kategori: <strong class="text-accent">{{ book.category?.name || '-' }}</strong> · ISBN: <strong>{{ book.isbn || '-' }}</strong>
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <a :href="`/buku/${book.slug}`" target="_blank" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/10 px-4 text-xs font-extrabold text-sky-400 hover:bg-sky-500 hover:text-white transition">
            <span class="material-symbols-outlined text-[18px]">open_in_new</span>
            Halaman Publik
          </a>
          <router-link :to="{ name: 'AdminBookEdit', params: { id: book.id } }" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-accent px-4 text-xs font-black text-white hover:opacity-90 transition">
            <span class="material-symbols-outlined text-[18px]">edit</span>
            Edit Buku
          </router-link>
          <button @click="removeBook" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 text-xs font-extrabold text-red-400 hover:bg-red-500 hover:text-white transition">
            <span class="material-symbols-outlined text-[18px]">delete</span>
            Hapus
          </button>
        </div>
      </div>

      <!-- Main Overview Grid -->
      <div class="grid gap-6 lg:grid-cols-[300px_1fr]">
        <!-- Left Column: Cover & Quick Info -->
        <div class="flex flex-col gap-6">
          <div class="rounded-2xl p-5 text-center flex flex-col items-center gap-4" style="background: var(--bg-card); border: 1px solid var(--border)">
            <div class="relative w-48 aspect-[3/4] overflow-hidden rounded-xl bg-slate-800 shadow-xl border border-slate-700/50 flex items-center justify-center">
              <img v-if="book.cover" :src="storageUrl(book.cover)" :alt="book.title" class="h-full w-full object-cover" />
              <span v-else class="material-symbols-outlined text-6xl text-slate-600">menu_book</span>
            </div>

            <div class="flex flex-col items-center gap-1">
              <span :class="statusBadge(book.status)">{{ statusLabel(book.status) }}</span>
              <p class="mt-2 text-xs" style="color: var(--text-muted)">
                Diterbitkan pada {{ formatDate(book.published_at || book.created_at) }}
              </p>
            </div>

            <div class="grid w-full grid-cols-2 gap-2 border-t pt-4" style="border-color: var(--border)">
              <div class="rounded-xl p-3 text-center" style="background: var(--bg-input)">
                <p class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--text-muted)">Views</p>
                <p class="text-base font-black text-sky-400">{{ book.views || 0 }}</p>
              </div>
              <div class="rounded-xl p-3 text-center" style="background: var(--bg-input)">
                <p class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--text-muted)">Downloads</p>
                <p class="text-base font-black text-emerald-400">{{ book.downloads || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- File PDF Download Card -->
          <div class="rounded-2xl p-5" style="background: var(--bg-card); border: 1px solid var(--border)">
            <h3 class="text-xs font-bold uppercase tracking-wider mb-3" style="color: var(--text-muted)">Berkas Buku PDF</h3>
            <div v-if="book.full_file" class="flex flex-col gap-3">
              <div class="flex items-center gap-3 rounded-xl border p-3" style="border-color: var(--border); background: var(--bg-input)">
                <span class="material-symbols-outlined text-red-400 text-3xl">picture_as_pdf</span>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-black truncate" style="color: var(--text-heading)">File PDF Naskah Utuh</p>
                  <p class="text-[11px]" style="color: var(--text-muted)">Siap diunduh / dibaca</p>
                </div>
              </div>
              <a :href="`/api/books/${book.id}/download`" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 py-2.5 text-xs font-black text-white hover:bg-emerald-600 transition shadow-md">
                <span class="material-symbols-outlined text-[18px]">download</span>
                Download File PDF
              </a>
            </div>
            <p v-else class="text-xs" style="color: var(--text-muted)">Belum ada berkas PDF utuh yang diunggah.</p>
          </div>
        </div>

        <!-- Right Column: Metadata Details -->
        <div class="flex flex-col gap-6">
          <article class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
            <h2 class="text-lg font-black" style="color: var(--text-heading)">Informasi & Spesifikasi Buku</h2>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <Info label="Judul Buku" :value="book.title || '-'" />
              <div class="flex flex-col gap-1">
                <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--text-muted)">Penulis / Authors</span>
                <div v-if="book.authors && book.authors.length > 0" class="flex flex-wrap gap-1.5 mt-0.5">
                  <span v-for="auth in book.authors" :key="auth.id" class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <span class="material-symbols-outlined text-[13px]">person</span>
                    {{ auth.name }}
                    <span v-if="auth.pivot?.role" class="text-[10px] opacity-75">({{ auth.pivot.role }})</span>
                  </span>
                </div>
                <p v-else class="text-sm font-bold" style="color: var(--text-heading)">{{ book.author?.name || '-' }}</p>
              </div>
              <Info label="Kategori" :value="book.category?.name || '-'" />
              <Info label="ISBN" :value="book.isbn || '-'" />
              <Info label="Penerbit" :value="book.publisher || '-'" />
              <Info label="Tahun Terbit" :value="String(book.year || '-')" />
              <Info label="Jumlah Halaman" :value="book.pages || '-'" />
              <Info label="Ukuran (Dimensi)" :value="book.size || '-'" />
              <Info label="Bahasa" :value="book.language || '-'" />
            </div>

            <div v-if="book.tags" class="mt-6 border-t pt-4" style="border-color: var(--border)">
              <p class="text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--text-muted)">Tags / Kata Kunci</p>
              <div class="flex flex-wrap gap-1.5">
                <span v-for="tag in book.tags.split(',')" :key="tag" class="rounded-lg bg-accent/10 border border-accent/30 text-accent px-2.5 py-1 text-xs font-bold">
                  #{{ tag.trim() }}
                </span>
              </div>
            </div>
          </article>

          <!-- Synopsis / Description -->
          <article class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
            <h2 class="text-lg font-black" style="color: var(--text-heading)">Deskripsi / Sinopsis</h2>
            <div class="mt-4 whitespace-pre-line text-sm leading-relaxed" style="color: var(--text-body)">
              {{ book.description || 'Belum ada deskripsi untuk buku ini.' }}
            </div>
          </article>

          <!-- Table of Contents -->
          <article v-if="book.table_of_contents" class="rounded-2xl p-6" style="background: var(--bg-card); border: 1px solid var(--border)">
            <h2 class="text-lg font-black" style="color: var(--text-heading)">Daftar Isi</h2>
            <div class="mt-4 whitespace-pre-line text-sm leading-relaxed" style="color: var(--text-body)">
              {{ book.table_of_contents }}
            </div>
          </article>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { defineComponent, h, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../../axios'
import { storageUrl } from '../../../utils/asset'

const Info = defineComponent({
  props: { label: String, value: String },
  setup(props) {
    return () => h('div', { class: 'rounded-xl border p-4', style: 'border-color: var(--border); background: var(--bg-input)' }, [
      h('p', { class: 'text-xs font-bold uppercase tracking-wider', style: 'color: var(--text-muted)' }, props.label),
      h('p', { class: 'mt-1 text-sm font-black', style: 'color: var(--text-heading)' }, props.value),
    ])
  },
})

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const book = ref(null)

async function loadBook() {
  loading.value = true
  try {
    const { data } = await api.get(`/books/${route.params.id}`)
    book.value = data
  } catch (err) {
    console.error('Failed to fetch book detail', err)
  } finally {
    loading.value = false
  }
}

async function removeBook() {
  if (!book.value) return
  if (!confirm(`Apakah Anda yakin ingin menghapus buku "${book.value.title}"?`)) return
  try {
    await api.delete(`/books/${book.value.id}`)
    alert('Buku berhasil dihapus.')
    router.push('/administrator/books')
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menghapus buku.')
  }
}

function formatDate(date) {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

function statusLabel(val) {
  return { draft: 'Draft', review: 'Review', published: 'Published', archived: 'Archived' }[val] || val
}

function statusBadge(val) {
  const base = 'app-badge'
  if (val === 'published') return `${base} app-badge--success`
  if (val === 'archived') return `${base} app-badge--red`
  if (val === 'review') return `${base} app-badge--blue`
  return `${base} app-badge--sky`
}

onMounted(() => {
  loadBook()
})
</script>
