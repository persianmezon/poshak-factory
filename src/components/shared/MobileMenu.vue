<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <transition name="fade">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40 bg-black bg-opacity-40 md:hidden"
      @click.self="closeMenu"
    >
      <div class="bg-white w-64 h-full p-4 overflow-y-auto shadow-lg">
        <h2 class="text-lg font-bold mb-4">📁 منوی اصلی</h2>
        <ul class="space-y-2">
          <li v-for="item in filteredMenuItems" :key="item.path">
            <router-link
              :to="item.path"
              class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 transition"
              @click="closeMenu"
            >
              <component :is="item.icon" class="w-5 h-5 text-gray-600" />
              <span class="text-sm text-gray-800">{{ item.name }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { defineProps, defineEmits, computed, ref } from 'vue'
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

// دریافت پراپرتی‌ها و ایونت‌ها
defineProps({ isOpen: Boolean })
const emit = defineEmits(['close'])

// نقش کاربر از localStorage
const userRole = ref(localStorage.getItem('userRole') || 'guest')

// لیست منوها
const menuItems = [
  { name: 'داشبورد', path: '/dashboard', icon: LayoutDashboardIcon },
  { name: 'سالن دوخت', path: '/sewing-hall', icon: ShirtIcon, roles: ['admin'] },
  { name: 'تاریخچه هشدار ها', path: '/stats-history', icon: AlertTriangleIcon, roles: ['admin'] },
  { name: 'آمار خیاط‌ها', path: '/workers-stats', icon: BarChartIcon, roles: ['admin'] },
  { name: 'انبار', path: '/warehouse', icon: WarehouseIcon, roles: ['admin'] },
  { name: 'ساخت QRCode', path: '/generate-qrcode', icon: QrCodeIcon, roles: ['admin'] },
  { name: 'اسکن QRCode', path: '/qrcode-scanner', icon: CameraIcon },
  { name: 'مدیریت کارگرها', path: '/workers-management', icon: UsersIcon, roles: ['admin'] }
]

// فیلتر منو بر اساس نقش کاربر
const filteredMenuItems = computed(() =>
  menuItems.filter(item => !item.roles || item.roles.includes(userRole.value))
)

// بستن منو
const closeMenu = () => {
  emit('close')
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
