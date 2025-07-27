import { createRouter, createWebHistory } from 'vue-router'
import { auth } from '@/firebase'

import Login from '@/pages/Login.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import { onAuthStateChanged } from 'firebase/auth'
import CutInventory from '@/pages/CutInventory.vue'


const WorkersStats = () => import('@/pages/WorkersStats.vue')
const SupervisorPanel = () => import('@/components/SupervisorPanel.vue')
const UsersManagement = () => import('@/pages/UsersManagement.vue')
const WorkersManagement = () => import('@/pages/WorkersManagement.vue')

const routes = [
  {
    path: '/login',
    component: Login,
    meta: { layout: false }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
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
      { path: '/pdf-archive', name: 'PdfArchive', component: () => import('@/pages/reports/PdfArchive.vue') },
      {path: '/workers-management',component: WorkersManagement,meta: { onlyAdmin: true }},
      {path: '/generate-qrcode',component: () => import('@/pages/GenerateQRCode.vue'),meta: { requiresRole: 'admin' }},
      {path: '/cut-inventory',name: 'CutInventory',component: CutInventory,meta: { requiresAuth: true }},
      {path: '/sewing-inventory',name: 'SewingInventory',component: () => import('@/pages/SewingInventory.vue'),meta: { requiresAuth: true, allowedRoles: ['admin', 'supervisor'] }},
      {path: '/final-inventory',name: 'FinalInventory',component: () => import('@/pages/FinalInventory.vue')},
      {path: '/cut-storage',name: 'CutStorage',component: () => import('@/pages/CutStorage.vue'),meta: { requiresAuth: true, roles: ['admin', 'supervisor'] }},
      {path: '/qrcode-scanner',component: () => import('@/pages/QRCodeScanner.vue')},
    ]
  }
]
const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const role = localStorage.getItem('userRole') || 'unknown'
  
  // صبر می‌کنیم تا Firebase کاربر رو شناسایی کنه
  const unsubscribe = onAuthStateChanged(auth, (user) => {
    unsubscribe() // فقط یک بار اجرا بشه

    if (to.meta.requiresAuth && !user) {
      return next('/login')
    }

    checkRoleAccess()
  })

  function checkRoleAccess() {
    if (to.meta.onlyAdmin && role !== 'admin') {
      alert('⛔ فقط ادمین دسترسی دارد.')
      return next('/dashboard')
    }

    if (to.meta.roles && !to.meta.roles.includes(role)) {
      alert('⛔ دسترسی شما به این بخش محدود شده است.')
      return next('/dashboard')
    }

    return next()
  }
})



export default router




