<template>
  <div class="min-h-screen bg-background text-gray-900">
    <!-- 🔹 هدر با رنگ و نام کارخانه از store -->
    <div :style="{ backgroundColor: factorySettings.themeColor || '#f3f4f6' }" class="py-2 px-4 shadow-sm">
      <h1 class="text-xl font-bold text-white">
        {{ factorySettings.name || 'ProdMaster' }}
      </h1>
    </div>
    <!-- 🔹 نوار منو -->
    <nav class="flex flex-wrap items-center justify-between bg-white shadow-md p-4 mb-6">
      <div class="flex gap-4 flex-wrap">
        <router-link
          v-if="role === 'supervisor'"
          to="/warehouse"
          class="text-gray-700 font-semibold hover:text-primary transition"
        >
          انبار
        </router-link>

        <router-link
  v-if="role === 'supervisor'"
  to="/deleted-batches"
  class="text-gray-700 font-semibold hover:text-primary transition"
>
  حذف‌شده‌ها
</router-link>

<router-link
  v-if="role === 'supervisor'"
  to="/pdf-archive"
  class="text-gray-700 font-semibold hover:text-primary transition"
>
  📁 آرشیو PDF
</router-link>

        <router-link
          v-if="role === 'supervisor'"
          to="/users"
          class="text-gray-700 font-semibold hover:text-primary transition"
        >
          مدیریت کاربران
        </router-link>

        <router-link to="/sewing-hall" class="text-gray-700 font-semibold hover:text-primary transition">
          سالن دوخت
        </router-link>
        <router-link to="/stats-history" class="text-gray-700 font-semibold hover:text-primary transition">
          تاریخچه هشدار
        </router-link>
        <router-link to="/workers-stats" class="text-gray-700 font-semibold hover:text-primary transition">
          آمار خیاط‌ها
        </router-link>
        <router-link to="/dashboard" class="text-gray-700 font-bold hover:text-primary transition">
          📊 داشبورد کارخانه
        </router-link>
      </div>

      <div class="flex items-center gap-3">
        <div class="text-sm text-gray-600" v-if="role">
          نقش شما: {{ role === 'supervisor' ? 'سرپرست' : 'کارگر' }}
        </div>
        <button
          v-if="role"
          @click="logout"
          class="px-4 py-2 bg-red-500 text-white rounded shadow"
        >
          خروج
        </button>
      </div>
    </nav>

    <!-- 🔹 رندر محتوای صفحات -->
    <main class="px-4 sm:px-8">
      <slot />
    </main>
    <router-view />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { signOut } from 'firebase/auth'
import { auth } from '@/firebase'
import { useFactorySettings } from '@/stores/factoryStore'

export default {
  name: 'AppLayout',
  setup() {
    const role = ref('')
    const { factorySettings } = useFactorySettings()


onMounted(() => {
  role.value = localStorage.getItem('userRole') || 'unknown'
})


    const logout = async () => {
      await signOut(auth)
      localStorage.removeItem('userRole')
      window.location.href = '/login'
    }

    return { role, logout, factorySettings }
  }
}
</script>
