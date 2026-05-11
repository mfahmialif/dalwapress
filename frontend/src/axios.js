import axios from 'axios'

const apiBaseURL = import.meta.env.VITE_API_URL || '/api'

function getCsrfCookieURL() {
  const baseURL = apiBaseURL.replace(/\/+$/, '')

  if (baseURL.endsWith('/api')) {
    return `${baseURL.slice(0, -4)}/sanctum/csrf-cookie`
  }

  return `${baseURL}/sanctum/csrf-cookie`
}

let csrfCookieRequest = null

export function ensureCsrfCookie() {
  if (!csrfCookieRequest) {
    csrfCookieRequest = axios.get(getCsrfCookieURL(), {
      withCredentials: true,
      withXSRFToken: true,
      headers: {
        Accept: 'application/json',
      },
    }).finally(() => {
      csrfCookieRequest = null
    })
  }

  return csrfCookieRequest
}

const api = axios.create({
  baseURL: apiBaseURL,
  withCredentials: true,
  withXSRFToken: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Request interceptor - make sure Laravel has issued the CSRF cookie first.
api.interceptors.request.use(async (config) => {
  const method = (config.method || 'get').toLowerCase()

  if (['post', 'put', 'patch', 'delete'].includes(method)) {
    await ensureCsrfCookie()
  }

  return config
})

// Response interceptor — handle 401
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 419 && error.config && !error.config._csrfRetry) {
      error.config._csrfRetry = true
      await ensureCsrfCookie()
      return api(error.config)
    }

    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent('auth:unauthorized', {
          detail: { redirect: !error.config?.skipAuthRedirect },
        }))
      }
    }

    return Promise.reject(error)
  }
)

export default api
