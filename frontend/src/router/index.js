import { createRouter, createWebHistory } from 'vue-router'
import LandingIndex from '../views/public/landing/Index.vue'
import BooksPage from '../views/public/books/Index.vue'
import BookDetailPage from '../views/public/books/Detail.vue'
import NewsPage from '../views/public/news/Index.vue'
import DetailPage from '../views/public/news/Detail.vue'
import ContactPage from '../views/public/contact/Index.vue'
import SubmissionPage from '../views/public/submission/Index.vue'
import Login from '../views/public/login/Index.vue'
import PublicLayout from '../layouts/PublicLayout.vue'
import AdminLayout from '../layouts/AdminLayout.vue'
import AuthorLayout from '../layouts/AuthorLayout.vue'
import EditorLayout from '../layouts/EditorLayout.vue'
import AdminDashboard from '../views/admin/dashboard/Index.vue'

const appName = 'UII Dalwa Press'

const routes = [
  {
    path: '/',
    component: PublicLayout,
    children: [
      {
        path: '',
        name: 'Landing',
        component: LandingIndex,
        meta: { title: appName, publicActive: 'home' }
      },
      {
        path: 'books',
        name: 'Books',
        component: BooksPage,
        meta: { title: `${appName} - Books`, publicActive: 'books' }
      },
      {
        path: 'books/:id',
        name: 'BookDetail',
        component: BookDetailPage,
        meta: { title: `${appName} - Detail Buku`, publicActive: 'books' }
      },
      {
        path: 'news',
        name: 'News',
        component: NewsPage,
        meta: { title: `${appName} - News`, publicActive: 'news' }
      },
      {
        path: 'news/:id',
        name: 'DetailNews',
        component: DetailPage,
        meta: { title: `${appName} - Detail News`, detailType: 'news', publicActive: 'news' }
      },
      {
        path: 'submissions',
        redirect: '/author/submissions/create'
      },
      {
        path: 'contact',
        name: 'Contact',
        component: ContactPage,
        meta: { title: `${appName} - Contact`, publicActive: 'contact' }
      },
      {
        path: 'info-terkini',
        redirect: '/news'
      },
      {
        path: 'info-terkini/:id',
        redirect: to => `/news/${to.params.id}`
      },
    ]
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
        component: () => import('../views/admin/news/Index.vue'),
        meta: { title: `${appName} - News`, pageTitle: 'News', requiresAuth: true }
      },
      {
        path: 'news/create',
        name: 'AdminNewsCreate',
        component: () => import('../views/admin/news/Form.vue'),
        meta: { title: `${appName} - Tambah News`, pageTitle: 'Tambah News', requiresAuth: true }
      },
      {
        path: 'news/:id/edit',
        name: 'AdminNewsEdit',
        component: () => import('../views/admin/news/Form.vue'),
        meta: { title: `${appName} - Edit News`, pageTitle: 'Edit News', requiresAuth: true }
      },
      {
        path: 'book-categories',
        name: 'AdminBookCategories',
        component: () => import('../views/admin/bookCategories/Index.vue'),
        meta: { title: `${appName} - Kategori Buku`, pageTitle: 'Kategori Buku', requiresAuth: true }
      },
      {
        path: 'authors',
        name: 'AdminAuthors',
        component: () => import('../views/admin/authors/Index.vue'),
        meta: { title: `${appName} - Authors`, pageTitle: 'Authors', requiresAuth: true }
      },
      {
        path: 'books',
        name: 'AdminBooks',
        component: () => import('../views/admin/books/Index.vue'),
        meta: { title: `${appName} - Books`, pageTitle: 'Books', requiresAuth: true }
      },
      {
        path: 'books/create',
        name: 'AdminBookCreate',
        component: () => import('../views/admin/books/Form.vue'),
        meta: { title: `${appName} - Tambah Buku`, pageTitle: 'Tambah Buku', requiresAuth: true }
      },
      {
        path: 'books/:id/edit',
        name: 'AdminBookEdit',
        component: () => import('../views/admin/books/Form.vue'),
        meta: { title: `${appName} - Edit Buku`, pageTitle: 'Edit Buku', requiresAuth: true }
      },
      {
        path: 'submissions',
        name: 'AdminSubmissions',
        component: () => import('../views/admin/submissions/Index.vue'),
        meta: { title: `${appName} - Submissions`, pageTitle: 'Submissions', requiresAuth: true }
      },
      {
        path: 'submissions/:id',
        name: 'AdminSubmissionDetail',
        component: () => import('../views/admin/submissions/Detail.vue'),
        meta: { title: `${appName} - Detail Submission`, pageTitle: 'Detail Submission', requiresAuth: true }
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
  ,
  {
    path: '/author',
    component: AuthorLayout,
    meta: { requiresAuth: true, roles: ['Author'] },
    children: [
      { path: '', redirect: '/author/dashboard' },
      {
        path: 'dashboard',
        name: 'AuthorDashboard',
        component: () => import('../views/author/dashboard/Index.vue'),
        meta: { title: `${appName} - Author Dashboard`, pageTitle: 'Dashboard Author', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'submissions',
        name: 'AuthorSubmissions',
        component: () => import('../views/author/submissions/Index.vue'),
        meta: { title: `${appName} - My Submissions`, pageTitle: 'My Submissions', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'submissions/create',
        name: 'AuthorSubmissionCreate',
        component: () => import('../views/author/submissions/Form.vue'),
        meta: { title: `${appName} - Create Submission`, pageTitle: 'Create Submission', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'submissions/:id',
        name: 'AuthorSubmissionDetail',
        component: () => import('../views/author/submissions/Detail.vue'),
        meta: { title: `${appName} - Submission Detail`, pageTitle: 'Submission Detail', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'submissions/:id/edit',
        name: 'AuthorSubmissionEdit',
        component: () => import('../views/author/submissions/Form.vue'),
        meta: { title: `${appName} - Edit Submission`, pageTitle: 'Edit Submission', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'submissions/:id/revision',
        name: 'AuthorRevisionUpload',
        component: () => import('../views/author/submissions/Revision.vue'),
        meta: { title: `${appName} - Upload Revisi`, pageTitle: 'Upload Revisi', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'books',
        name: 'AuthorBooks',
        component: () => import('../views/author/books/Index.vue'),
        meta: { title: `${appName} - My Published Books`, pageTitle: 'My Published Books', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'profile',
        name: 'AuthorProfile',
        component: () => import('../views/author/profile/Index.vue'),
        meta: { title: `${appName} - Profile Author`, pageTitle: 'Profile Author', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'notifications',
        name: 'AuthorNotifications',
        component: () => import('../views/author/notifications/Index.vue'),
        meta: { title: `${appName} - Notifications`, pageTitle: 'Notifications', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'activity',
        name: 'AuthorActivity',
        component: () => import('../views/author/activity/Index.vue'),
        meta: { title: `${appName} - Activity Log`, pageTitle: 'Activity Log', requiresAuth: true, roles: ['Author'] }
      },
      {
        path: 'bookmarks',
        name: 'AuthorBookmarks',
        component: () => import('../views/author/bookmarks/Index.vue'),
        meta: { title: `${appName} - Bookmark Buku`, pageTitle: 'Bookmark Buku', requiresAuth: true, roles: ['Author'] }
      }
    ]
  },
  {
    path: '/editor',
    component: EditorLayout,
    meta: { requiresAuth: true, roles: ['Editor'] },
    children: [
      { path: '', redirect: '/editor/dashboard' },
      {
        path: 'dashboard',
        name: 'EditorDashboard',
        component: () => import('../views/editor/dashboard/Index.vue'),
        meta: { title: `${appName} - Editor Dashboard`, pageTitle: 'Dashboard Editor', requiresAuth: true, roles: ['Editor'] }
      },
      {
        path: 'submissions',
        name: 'EditorSubmissions',
        component: () => import('../views/editor/submissions/Index.vue'),
        meta: { title: `${appName} - Assigned Submissions`, pageTitle: 'Assigned Submissions', requiresAuth: true, roles: ['Editor'] }
      },
      {
        path: 'submissions/:id',
        name: 'EditorSubmissionDetail',
        component: () => import('../views/editor/submissions/Detail.vue'),
        meta: { title: `${appName} - Review Submission`, pageTitle: 'Review Submission', requiresAuth: true, roles: ['Editor'] }
      },
      {
        path: 'profile',
        name: 'EditorProfile',
        component: () => import('../views/editor/profile/Index.vue'),
        meta: { title: `${appName} - Profile Editor`, pageTitle: 'Profile Editor', requiresAuth: true, roles: ['Editor'] }
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
      const allowedRoles = to.meta.roles || ['Admin', 'Operator']
      if (!roleName || !allowedRoles.includes(roleName)) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        return { name: 'Login' }
      }
    } catch {
      return { name: 'Login' }
    }
  }

  if (to.name === 'Login' && isAuthenticated) {
    const authUser = JSON.parse(localStorage.getItem('auth_user') || '{}')
    if (authUser?.role?.name === 'Author') return { name: 'AuthorDashboard' }
    if (authUser?.role?.name === 'Editor') return { name: 'EditorDashboard' }
    return { name: 'AdminDashboard' }
  }
})

export default router
