import { createApp } from 'vue'
import { createPinia } from 'pinia'
import './style.css'
import App from './App.vue'
import router from './router'

const app = createApp(App)
app.use(createPinia())
app.use(router)

function redirectToLogin() {
  if (router.currentRoute.value.name !== 'Login') {
    router.push({ name: 'Login' })
  }
}

window.addEventListener('auth:redirect-login', redirectToLogin)
window.addEventListener('auth:unauthorized', (event) => {
  if (event.detail?.redirect !== false) {
    redirectToLogin()
  }
})

app.mount('#app')
