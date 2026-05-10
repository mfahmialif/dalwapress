import { fixHtmlAssetUrls, storageUrl } from '../../../utils/asset'

export const filterTabs = [
  { value: 'all', label: 'Semua', icon: 'dashboard' },
  { value: 'Artikel', label: 'Artikel', icon: 'article' },
  { value: 'Gambar', label: 'Gambar', icon: 'image' },
  { value: 'Video', label: 'Video', icon: 'play_circle' },
]

export const sortOptions = [
  { value: 'created_at', label: 'Terbaru' },
  { value: 'title', label: 'Judul' },
  { value: 'news_category_id', label: 'Kategori' },
]

export function getNewsImage(item) {
  if (item?.image_path) return storageUrl(item.image_path)
  return '/img/news/news1.jpg'
}

export function getNewsVideo(item) {
  if (item?.video_path) return storageUrl(item.video_path)
  return ''
}

export function plainText(html = '') {
  return String(html)
    .replace(/<[^>]*>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()
}

export function excerpt(item, length = 150) {
  const text = plainText(item?.body || '')
  if (!text) return 'Baca informasi terbaru dari UII Dalwa Press.'
  return text.length > length ? `${text.slice(0, length).trim()}...` : text
}

export function normalizeNews(raw) {
  const category = raw?.category || null
  const categoryName = category?.name || 'Artikel'
  const categoryType = category?.type || 'Artikel'

  return {
    ...raw,
    category,
    categoryName,
    categoryType,
    image: getNewsImage(raw),
    video: getNewsVideo(raw),
    bodyHtml: fixHtmlAssetUrls(raw?.body || ''),
    excerpt: excerpt(raw),
  }
}

export function formatDate(dateStr, options = {}) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  if (Number.isNaN(date.getTime())) return dateStr

  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    ...options,
  }).format(date)
}

export function formatRelative(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const diff = Math.floor((Date.now() - date.getTime()) / 1000)

  if (Number.isNaN(diff)) return formatDate(dateStr)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`
  if (diff < 172800) return 'Kemarin'
  return formatDate(dateStr)
}

export function categoryClass(category) {
  if (category === 'Video') return 'bg-red-50 text-red-700 ring-red-100'
  if (category === 'Gambar') return 'bg-sky-50 text-sky-700 ring-sky-100'
  return 'bg-blue-50 text-blue-700 ring-blue-100'
}

export function initials(name = '') {
  return name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('') || 'UD'
}
