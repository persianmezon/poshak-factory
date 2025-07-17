<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <aside class="w-64 min-h-screen bg-white hidden md:block">
    <nav class="p-4 space-y-2 text-sm">
      <router-link
        v-for="item in menuItems"
        :key="item.path"
        :to="item.path"
        class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-blue-50"
        :class="{ 'bg-blue-100 text-blue-600 font-bold': route.path === item.path }"
      >
        <component :is="item.icon" class="w-4 h-4" />
        {{ item.label }}
      </router-link>
    </nav>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'
import {
  LayoutDashboardIcon,
  ShirtIcon,
  AlertTriangleIcon,
  BarChartIcon,
  WarehouseIcon,
  QrCodeIcon,
  CameraIcon
} from 'lucide-vue-next'

const route = useRoute()

const menuItems = [
  { path: '/dashboard', label: 'داشبورد کارخانه', icon: LayoutDashboardIcon },
  { path: '/sewing-hall', label: 'سالن دوخت', icon: ShirtIcon },
  { path: '/stats-history', label: 'تاریخچه هشدار ها', icon: AlertTriangleIcon },
  { path: '/workers-stats', label: 'آمار خیاط‌ها', icon: BarChartIcon },
  { path: '/warehouse', label: 'انبار', icon: WarehouseIcon },
  { path: '/generate-qrcode', label: 'ساخت QRCode', icon: QrCodeIcon },
  { path: '/qrcode-scanner', label: 'اسکن QRCode', icon: CameraIcon }
]
</script>
