import { reactive } from 'vue'
import api from '../axios'
import { assetUrl } from '../utils/asset'

const faviconCacheKey = 'dalwapress-public-favicon'
const systemNameCacheKey = 'dalwapress-system-name'
const cachedFavicon = typeof window !== 'undefined'
  ? localStorage.getItem(faviconCacheKey) || ''
  : ''
const cachedSystemName = typeof window !== 'undefined'
  ? localStorage.getItem(systemNameCacheKey) || ''
  : ''

const defaults = {
  systemName: cachedSystemName || 'UII Dalwa Press',
  faviconUrl: cachedFavicon,
  landingHeroImageUrl: '/img/hero-bg.jpg',
  loginBackgroundImageUrl: '/img/hero-bg.jpg',
  navbarBrandMode: 'text',
  navbarBrandIcon: 'auto_stories',
  navbarBrandTitle: 'UII Dalwa',
  navbarBrandSubtitle: 'Press Publisher',
  navbarBrandLogoUrl: '',
  footerContactEmail: 'press@uiidalwa.ac.id',
  footerContactPhone: '+62 812 0000 0000',
  footerContactLocation: 'Pasuruan, Jawa Timur',
  footerContactMapsUrl: 'https://maps.app.goo.gl/pNNU4dnCtuu7y4Qk6',
}

const settings = reactive({ ...defaults })
let settingsLoaded = false
let settingsRequest = null
const preloadedImages = new Set()

function normalizeSettings(data = {}) {
  return {
    systemName: data.system_name || defaults.systemName,
    faviconUrl: assetUrl(data.favicon_url) || defaults.faviconUrl,
    landingHeroImageUrl: assetUrl(data.landing_hero_image_url) || defaults.landingHeroImageUrl,
    loginBackgroundImageUrl: assetUrl(data.login_background_image_url) || defaults.loginBackgroundImageUrl,
    navbarBrandMode: data.navbar_brand_mode === 'logo' ? 'logo' : defaults.navbarBrandMode,
    navbarBrandIcon: data.navbar_brand_icon || defaults.navbarBrandIcon,
    navbarBrandTitle: data.navbar_brand_title || defaults.navbarBrandTitle,
    navbarBrandSubtitle: data.navbar_brand_subtitle ?? defaults.navbarBrandSubtitle,
    navbarBrandLogoUrl: assetUrl(data.navbar_brand_logo_url),
    footerContactEmail: data.footer_contact_email || defaults.footerContactEmail,
    footerContactPhone: data.footer_contact_phone || defaults.footerContactPhone,
    footerContactLocation: data.footer_contact_location || defaults.footerContactLocation,
    footerContactMapsUrl: data.footer_contact_maps_url || defaults.footerContactMapsUrl,
  }
}

function applyFavicon(url) {
  if (!url || typeof document === 'undefined') return

  let favicon = document.querySelector("link[rel='icon']")

  if (!favicon) {
    favicon = document.createElement('link')
    favicon.rel = 'icon'
    document.head.appendChild(favicon)
  }

  if (favicon.href !== url) {
    favicon.href = url
  }
}

function cacheAndApplyFavicon(url) {
  if (!url) return

  if (typeof window !== 'undefined') {
    localStorage.setItem(faviconCacheKey, url)
  }

  applyFavicon(url)
}

function cacheSystemName(value) {
  if (!value || typeof window === 'undefined') return

  localStorage.setItem(systemNameCacheKey, value)
}

applyFavicon(defaults.faviconUrl)

function preloadImage(url) {
  if (!url || preloadedImages.has(url)) {
    return Promise.resolve(true)
  }

  return new Promise((resolve) => {
    const image = new Image()
    image.decoding = 'async'
    image.onload = async () => {
      try {
        await image.decode?.()
      } catch {
        // The resource is already loaded; decoding support varies by browser.
      }
      preloadedImages.add(url)
      resolve(true)
    }
    image.onerror = () => resolve(false)
    image.src = url
  })
}

async function loadSettings() {
  if (settingsLoaded) return settings

  if (!settingsRequest) {
    settingsRequest = api.get('/settings/public', { skipAuthRedirect: true })
      .then(({ data }) => {
        Object.assign(settings, normalizeSettings(data))
        cacheAndApplyFavicon(settings.faviconUrl)
        cacheSystemName(settings.systemName)
        settingsLoaded = true
        return settings
      })
      .catch(() => {
        Object.assign(settings, defaults)
        settingsLoaded = true
        return settings
      })
      .finally(() => {
        settingsRequest = null
      })
  }

  return settingsRequest
}

export function getPublicSettings() {
  return settings
}

export function syncPublicSettings(data) {
  Object.assign(settings, normalizeSettings(data))
  cacheAndApplyFavicon(settings.faviconUrl)
  cacheSystemName(settings.systemName)
  settingsLoaded = true
  return settings
}

export async function ensurePublicSettings(target) {
  await loadSettings()

  if (target === 'landing' || target === 'login') {
    const key = target === 'login' ? 'loginBackgroundImageUrl' : 'landingHeroImageUrl'
    const fallback = target === 'login' ? defaults.loginBackgroundImageUrl : defaults.landingHeroImageUrl
    const loaded = await preloadImage(settings[key])

    if (!loaded && settings[key] !== fallback) {
      settings[key] = fallback
      await preloadImage(fallback)
    }
  }

  if (settings.navbarBrandMode === 'logo') {
    const logoLoaded = await preloadImage(settings.navbarBrandLogoUrl)

    if (!logoLoaded) {
      settings.navbarBrandMode = 'text'
      settings.navbarBrandLogoUrl = ''
    }
  }

  return settings
}
