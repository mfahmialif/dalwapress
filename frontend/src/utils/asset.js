/**
 * Asset URL utilities
 *
 * In development, VITE_ASSET_URL is 'http://localhost:8000' but Vite proxy
 * handles /storage/* so we can use relative paths.
 * In production, VITE_ASSET_URL points to the backend server where files live.
 */

const ASSET_BASE = import.meta.env.VITE_ASSET_URL || ''

/**
 * Build a full URL for any asset path.
 * @param {string} path — e.g. '/storage/news/img.jpg' or '/img/default.png'
 * @returns {string} Full URL in production, relative in dev
 */
export function assetUrl(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const normalizedPath = path.startsWith('/') ? path : `/${path}`
  return `${ASSET_BASE}${normalizedPath}`
}

/**
 * Shortcut: build URL for a file stored in /storage/.
 * @param {string} path — e.g. 'news/img.jpg' (without /storage/ prefix)
 * @returns {string}
 */
export function storageUrl(path) {
  if (!path) return ''
  return assetUrl(`/storage/${path}`)
}

/**
 * Fix asset URLs inside HTML content (from Quill rich-text editor).
 * Replaces relative /storage/ paths with full backend URLs.
 * @param {string} html — raw HTML string
 * @returns {string} HTML with fixed URLs
 */
export function fixHtmlAssetUrls(html) {
  if (!html || !ASSET_BASE) return html
  return html.replace(/(src|poster)="\/storage\//g, `$1="${ASSET_BASE}/storage/`)
}
