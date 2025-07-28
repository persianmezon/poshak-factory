<!-- eslint-disable vue/multi-word-component-names -->
<template>
<aside
  class="fixed inset-y-0 left-0 w-64 bg-white z-50 transform transition-transform duration-300 ease-in-out
         md:relative md:translate-x-0"
  :class="{ '-translate-x-full': !isSidebarOpen, 'translate-x-0': isSidebarOpen }"
>
  <nav class="p-4 space-y-2 text-sm">
    <router-link
      v-for="item in filteredMenuItems"
      :key="item.path"
      :to="item.path"
      class="flex items-center gap-2 px-3 py-2 rounded transition hover:bg-blue-50"
      :class="{ 'bg-blue-100 text-blue-600 font-bold': route.path === item.path }"
      @click="isSidebarOpen = false"
    >
      <component :is="item.icon" class="w-4 h-4" />
      {{ item.label }}
    </router-link>
  </nav>
</aside>

</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { ref } from 'vue'
import {
  LayoutDashboardIcon,
  ShirtIcon,
  AlertTriangleIcon,
  BarChartIcon,
  WarehouseIcon,
  QrCodeIcon,
  CameraIcon,
  UsersIcon
} from 'lucide-vue-next'

const route = useRoute()
const userRole = localStorage.getItem('userRole') || 'guest'

const isSidebarOpen = ref(false)

const menuItems = [
  { path: '/dashboard', label: 'داشبورد کارخانه', icon: LayoutDashboardIcon },
  { path: '/sewing-hall', label: 'سالن دوخت', icon: ShirtIcon, roles: ['admin']  },
  { path: '/stats-history', label: 'تاریخچه هشدار ها', icon: AlertTriangleIcon, roles: ['admin']  },
  { path: '/workers-stats', label: 'آمار خیاط‌ها', icon: BarChartIcon, roles: ['admin']  },
  { path: '/warehouse', label: 'انبار', icon: WarehouseIcon, roles: ['admin']  },
  { path: '/generate-qrcode', label: 'ساخت QRCode', icon: QrCodeIcon, roles: ['admin'] },
  { path: '/cut-inventory', label: 'ثبت آمار برش', icon: WarehouseIcon, roles: ['admin'] },
  { path: '/qrcode-scanner', label: 'اسکن QRCode', icon: CameraIcon },
  { path: '/workers-management', label: 'مدیریت کارگرها', icon: UsersIcon, roles: ['admin'] }
]

const filteredMenuItems = computed(() =>
  menuItems.filter(item => !item.roles || item.roles.includes(userRole))
)
</script>
