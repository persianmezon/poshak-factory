import { createRouter, createWebHistory } from 'vue-router'
import { auth } from '@/firebase'

import Login from '@/pages/Login.vue'
import MainLayout from '@/layouts/MainLayout.vue'

import WorkersStats from '@/pages/WorkersStats.vue'
import SupervisorPanel from '@/components/SupervisorPanel.vue'
import UsersManagement from '@/pages/UsersManagement.vue'

const routes = [
  {
    path: '/login',
    component: Login,
    meta: { layout: false }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true }, // کل layout نیاز به ورود دارد
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'Dashboard', component: () => import('@/pages/Dashboard.vue') },
      { path: '/warehouse', component: () => import('@/pages/Warehouse.vue'), meta: { roles: ['supervisor', 'admin'] } },
      { path: '/sewing-hall', component: () => import('@/pages/SewingHall.vue') },
      { path: '/stats-history', component: () => import('@/pages/StatsHistory.vue') },
      { path: '/workers-stats', component: WorkersStats },
      { path: '/users', component: UsersManagement, meta: { onlyAdmin: true } },
      { path: '/supervisor', component: SupervisorPanel, meta: { onlyAdmin: true } },
      { path: '/products', component: () => import('@/pages/ProductGallery.vue') },
      { path: '/deleted-batches', name: 'DeletedBatches', component: () => import('@/pages/reports/DeletedBatches.vue') },
      { path: '/settings', name: 'FactorySettings', component: () => import('@/pages/FactorySettings.vue') },
      { path: '/workers-management', component: () => import('@/pages/WorkersManagement.vue') },
      { path: '/pdf-archive', name: 'PdfArchive', component: () => import('@/pages/reports/PdfArchive.vue') },
      {path: '/generate-qrcode',component: () => import('@/pages/GenerateQRCode.vue'),meta: { requiresRole: 'admin' }},
      {path: '/qrcode-scanner',component: () => import('@/pages/QRCodeScanner.vue')},
    ]
  }
]
const router = createRouter({
  history: createWebHistory(),
  routes
})

// ✅ فقط یکبار beforeEach بنویس
router.beforeEach((to, from, next) => {
  const currentUser = auth.currentUser
  const role = localStorage.getItem('userRole') || 'unknown'

  // اگر ورود لازم است اما کاربر لاگین نکرده
  if (to.meta.requiresAuth && !currentUser) {
    return next('/login')
  }

  // فقط ادمین مجاز است
  if (to.meta.onlyAdmin && role !== 'admin') {
    alert('⛔ فقط ادمین دسترسی دارد.')
    return next('/dashboard')
  }

  // فقط سرپرست و ادمین مجازند
  if (to.meta.roles && !to.meta.roles.includes(role)) {
    alert('⛔ دسترسی شما به این بخش محدود شده است.')
    return next('/dashboard')
  }

  next()
})

export default router




