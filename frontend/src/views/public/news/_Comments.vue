<template>
  <section class="comments-panel">
    <div class="comments-heading">
      <div>
        <p class="news-kicker compact">
          <span class="size-2.5 rounded-full bg-sky-600"></span>
          Diskusi Pembaca
        </p>
        <h2 class="comments-title">Kolom Komentar</h2>
      </div>
      <p class="comments-count">{{ comments.length }} komentar</p>
    </div>

    <form class="comment-form mt-8" @submit.prevent="submitComment">
      <div v-if="replyingTo" class="comment-replying">
        <span>Membalas <span class="font-black">{{ replyingTo }}</span></span>
        <button @click="cancelReply" class="text-slate-500 hover:text-slate-700 inline-flex items-center" type="button">
          <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
      </div>
      <div class="grid gap-4 md:grid-cols-[0.55fr_1fr]">
        <input v-model.trim="form.name" class="comment-input" placeholder="Nama kamu" maxlength="60" />
        <input v-model.trim="form.email" class="comment-input" placeholder="Email (opsional)" maxlength="90" />
      </div>
      <textarea v-model.trim="form.message" class="comment-input mt-4 min-h-32 resize-none py-4" placeholder="Tulis komentar yang sopan dan membangun..." maxlength="600"></textarea>
      <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <p class="comment-counter">{{ form.message.length }}/600 karakter</p>
        <button class="comment-submit disabled:opacity-70 disabled:cursor-not-allowed" type="submit" :disabled="sending">
          <template v-if="sending">
            <span class="size-5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
            Mengirim...
          </template>
          <template v-else>
            Kirim Komentar
            <span class="material-symbols-outlined text-[20px]">send</span>
          </template>
        </button>
      </div>
      <p v-if="error" class="mt-3 text-sm font-bold text-red-600">{{ error }}</p>
    </form>

    <div class="mt-8 grid gap-6">
      <div v-for="comment in comments" :key="comment.id" class="grid gap-4">
        <!-- Main Comment -->
        <article class="comment-card">
          <div class="comment-avatar">{{ initials(comment.name) }}</div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
              <div>
                <p class="font-black text-[#101418]">{{ comment.name }}</p>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ formatDate(comment.created_at, { hour: '2-digit', minute: '2-digit' }) }}</p>
              </div>
              <div class="flex items-center gap-3">
                <button @click="setReply(comment)" class="comment-reply-button" type="button">Balas</button>
                <button @click="likeComment(comment.id)" class="like-button" :class="{ '!text-red-600 !bg-red-50 !border-red-200': comment.is_liked }" type="button">
                  <span class="material-symbols-outlined text-[18px]">favorite</span>
                  {{ comment.likes }}
                </button>
              </div>
            </div>
            <p class="mt-3 leading-7 text-slate-600">{{ comment.message }}</p>
          </div>
        </article>

        <!-- Replies -->
        <div v-if="comment.replies && comment.replies.length" class="comment-replies">
          <article v-for="reply in comment.replies" :key="reply.id" class="comment-card comment-card--reply">
            <div class="comment-avatar comment-avatar--reply">{{ initials(reply.name) }}</div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <p class="font-black text-[#101418]">{{ reply.name }}</p>
                  <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-400">{{ formatDate(reply.created_at, { hour: '2-digit', minute: '2-digit' }) }}</p>
                </div>
                <button @click="likeComment(reply.id)" class="like-button" :class="{ '!text-red-600 !bg-red-50 !border-red-200': reply.is_liked }" type="button">
                  <span class="material-symbols-outlined text-[18px]">favorite</span>
                  {{ reply.likes }}
                </button>
              </div>
              <p class="mt-3 leading-7 text-slate-600">{{ reply.message }}</p>
            </div>
          </article>
        </div>
      </div>

      <div v-if="comments.length === 0" class="empty-comments">
        <span class="material-symbols-outlined text-[42px] text-sky-500">mode_comment</span>
        <p class="mt-3 font-black text-[#101418]">Belum ada komentar.</p>
        <p class="text-sm text-slate-500">Jadilah pembaca pertama yang membuka diskusi.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { useAuthStore } from '../../../stores/auth'
import api from '../../../axios'
import { formatDate, initials } from './utils'

const props = defineProps({
  newsId: {
    type: [Number, String],
    required: true,
  },
})

const comments = ref([])
const error = ref('')
const loading = ref(false)
const replyingTo = ref(null)
const sending = ref(false)
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  message: '',
  parent_id: null,
})

async function loadComments() {
  loading.value = true
  try {
    const { data } = await api.get(`/news/${props.newsId}/comments`)
    comments.value = data
  } catch (err) {
    console.error(err)
    error.value = 'Gagal memuat komentar.'
  } finally {
    loading.value = false
  }
}

async function submitComment() {
  error.value = ''

  if (!form.name || !form.message) {
    error.value = 'Nama dan komentar wajib diisi.'
    return
  }

  sending.value = true
  try {
    await api.post(`/news/${props.newsId}/comments`, {
      name: form.name,
      email: form.email,
      message: form.message,
      parent_id: form.parent_id,
    })

    // Reset form
    form.name = ''
    form.email = ''
    form.message = ''
    form.parent_id = null
    replyingTo.value = null

    // Reload comments
    await loadComments()
  } catch (err) {
    console.error(err)
    error.value = 'Gagal mengirim komentar.'
  } finally {
    sending.value = false
  }
}

async function likeComment(id) {
  if (!authStore.isAuthenticated) {
    alert('Silakan login terlebih dahulu untuk menyukai komentar.')
    return
  }
  try {
    const { data } = await api.post(`/comments/${id}/like`)
    
    // Update local state to avoid reload
    const updateLikes = (list) => {
      return list.map(c => {
        if (c.id === id) return { ...c, likes: data.likes, is_liked: true }
        if (c.replies) return { ...c, replies: updateLikes(c.replies) }
        return c
      })
    }
    comments.value = updateLikes(comments.value)
  } catch (err) {
    console.error(err)
    if (err.response && err.response.data && err.response.data.message) {
      alert(err.response.data.message)
    }
  }
}

function setReply(comment) {
  form.parent_id = comment.id
  replyingTo.value = comment.name
  // Scroll to form
  const formElement = document.querySelector('.comment-form')
  if (formElement) formElement.scrollIntoView({ behavior: 'smooth' })
}

function cancelReply() {
  form.parent_id = null
  replyingTo.value = null
}

onMounted(loadComments)
watch(() => props.newsId, loadComments)
</script>
