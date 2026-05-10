import { createRouter, createWebHistory } from 'vue-router'
import LandingPage from '../views/public/LandingPage.vue'
import NewsPage from '../views/public/InfoTerkini.vue'
import DetailPage from '../views/public/DetailPage.vue'
import Login from '../views/public/Login.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import AdminDashboard from '../views/admin/dashboard/Index.vue'

const appName = 'UII Dalwa Press'

const routes = [
  {
    path: '/',
    name: 'Landing',
    component: LandingPage,
    meta: { title: appName }
  },
  {
    path: '/news',
    name: 'News',
    component: NewsPage,
    meta: { title: `${appName} - News` }
  },
  {
    path: '/news/:id',
    name: 'DetailNews',
    component: DetailPage,
    meta: { title: `${appName} - Detail News`, detailType: 'news' }
  },
  {
    path: '/info-terkini',
    redirect: '/news'
  },
  {
    path: '/info-terkini/:id',
    redirect: to => `/news/${to.params.id}`
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { title: `${appName} - Login` }
  },
  {
    path: '/administrator',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/administrator/dashboard'
      },
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: AdminDashboard,
        meta: { title: `${appName} - Admin Dashboard`, pageTitle: 'Dashboard', requiresAuth: true }
      },
      {
        path: 'news',
        name: 'AdminNews',
        component: () => import('../views/admin/info/Index.vue'),
        meta: { title: `${appName} - News`, pageTitle: 'News', requiresAuth: true }
      },
      {
        path: 'news/create',
        name: 'AdminNewsCreate',
        component: () => import('../views/admin/info/Form.vue'),
        meta: { title: `${appName} - Tambah News`, pageTitle: 'Tambah News', requiresAuth: true }
      },
      {
        path: 'news/:id/edit',
        name: 'AdminNewsEdit',
        component: () => import('../views/admin/info/Form.vue'),
        meta: { title: `${appName} - Edit News`, pageTitle: 'Edit News', requiresAuth: true }
      },
      {
        path: 'info-terkini',
        redirect: '/administrator/news'
      },
      {
        path: 'info-terkini/create',
        redirect: '/administrator/news/create'
      },
      {
        path: 'info-terkini/:id/edit',
        redirect: to => `/administrator/news/${to.params.id}/edit`
      },
      {
        path: 'manajemen-user',
        name: 'AdminManajemenUser',
        component: () => import('../views/admin/user/User.vue'),
        meta: { title: `${appName} - Manajemen User`, pageTitle: 'Manajemen User', requiresAuth: true }
      },
      {
        path: 'manajemen-role',
        name: 'AdminManajemenRole',
        component: () => import('../views/admin/user/Role.vue'),
        meta: { title: `${appName} - Manajemen Role`, pageTitle: 'Manajemen Role', requiresAuth: true }
      },
      {
        path: 'profile',
        name: 'AdminProfile',
        component: () => import('../views/admin/profile/Index.vue'),
        meta: { title: `${appName} - Profile`, pageTitle: 'Profile', requiresAuth: true }
      },
      {
        path: 'pengaturan',
        name: 'AdminPengaturan',
        component: () => import('../views/admin/pengaturan/Index.vue'),
        meta: { title: `${appName} - Pengaturan`, pageTitle: 'Pengaturan', requiresAuth: true }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return savedPosition || { top: 0 }
  }
})

router.beforeEach((to) => {
  document.title = to.meta.title || appName

  const isAuthenticated = !!localStorage.getItem('auth_token')

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'Login' }
  }

  if (to.meta.requiresAuth && isAuthenticated) {
    try {
      const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}')
      const roleName = authUser?.role?.name
      if (!roleName || !['Admin', 'Operator'].includes(roleName)) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        return { name: 'Login' }
      }
    } catch {
      return { name: 'Login' }
    }
  }

  if (to.name === 'Login' && isAuthenticated) {
    return { name: 'AdminDashboard' }
  }
})

export default router
