<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- هدر با قابلیت کنترل سایدبار -->
    <AppHeader v-if="route.path !== '/login'" @toggle-sidebar="isSidebarOpen = true" />

    <div class="flex">
      <!-- سایدبار دسکتاپ -->
      <Sidebar v-if="route.path !== '/login'" class="hidden sm:block" />

      <!-- سایدبار موبایل (شناور) -->
      <transition name="fade">
        <div
          v-if="isSidebarOpen && route.path !== '/login'"
          class="fixed inset-0 z-50 bg-white w-64 shadow-lg p-4 sm:hidden"
        >
          <button class="mb-4 text-gray-500 text-xl" @click="isSidebarOpen = false">✖️</button>
          <Sidebar />
        </div>
      </transition>

      <!-- محتوای اصلی -->
      <main class="flex-1 p-4">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/shared/AppHeader.vue'
import Sidebar from '@/components/shared/Sidebar.vue'

export default {
  components: {
    AppHeader,
    Sidebar
  },
  setup() {
    const route = useRoute()
    const isSidebarOpen = ref(false)

    return {
      route,
      isSidebarOpen
    }
  }
}
</script>

<style scoped>
/* انیمیشن نمایش موبایل سایدبار */
.fade-enter-active,
.fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}
.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  transform: translateX(0);
}
</style>
