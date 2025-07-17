<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <header class="bg-white shadow p-4 flex justify-between items-center">
    <!-- پروفایل و خروج + دکمه سایدبار موبایل -->
    <div class="order-1 flex items-center gap-4">
      <!-- دکمه منو برای موبایل -->
      <button class="sm:hidden text-gray-600" @click="$emit('toggle-sidebar')">
        ☰
      </button>

      <button @click="logout" class="text-red-500 hover:text-red-700">
        <PowerIcon class="w-6 h-6" />
      </button>
      <img :src="userPhoto" alt="avatar" class="w-8 h-8 rounded-full" />
      <span class="text-sm text-gray-600">{{ userEmail }}</span>
    </div>

    <!-- لوگو وسط (فقط در دسکتاپ نمایش داده می‌شه) -->
    <div class="order-2 flex justify-center flex-1">
      <img src="/logo.png" alt="prodmaster" class="h-8 w-auto hidden sm:block" />
    </div>

    <!-- جستجو سمت چپ -->
    <div class="order-3 flex items-center justify-end gap-2 w-full max-w-sm">
      <!-- آیکن جستجو در موبایل -->
      <button class="sm:hidden" @click="toggleMobileSearch">
        <SearchIcon class="w-6 h-6 text-gray-500" />
      </button>

      <!-- اینپوت جستجو در دسکتاپ -->
      <div class="relative w-full hidden sm:block">
        <input
          type="text"
          v-model="searchQuery"
          @input="handleSearch"
          placeholder="جستجو ..."
          class="w-full pl-10 pr-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
        />
        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
          <SearchIcon class="w-5 h-5" />
        </span>
      </div>
    </div>
  </header>

  <!-- نوار جستجوی کشویی در موبایل -->
  <transition name="slide-down">
    <div
      v-if="isMobileSearchOpen"
      class="fixed top-0 left-0 right-0 bg-white shadow-md p-4 z-50 flex items-center gap-2"
    >
      <input
        type="text"
        v-model="searchQuery"
        @input="handleSearch"
        placeholder="جستجو ..."
        class="flex-1 pl-4 pr-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"
      />
      <button @click="isMobileSearchOpen = false" class="text-gray-500 text-xl">
        ✖️
      </button>
    </div>
  </transition>
</template>

<script>
import { ref } from 'vue'
import { PowerIcon, SearchIcon } from 'lucide-vue-next'

export default {
  name: 'AppHeader',
  components: {
    PowerIcon,
    SearchIcon
  },
  setup() {
    const searchQuery = ref('')
    const userEmail = localStorage.getItem('userEmail') || 'کاربر'
    const userPhoto = localStorage.getItem('avatarUrl') || 'https://i.pravatar.cc/100'
    const isMobileSearchOpen = ref(false)

    const handleSearch = () => {
      console.log('در حال جستجو برای:', searchQuery.value)
    }

    const logout = () => {
      localStorage.clear()
      window.location.href = '/login'
    }

    const toggleMobileSearch = () => {
      isMobileSearchOpen.value = true
    }

    return {
      searchQuery,
      handleSearch,
      userEmail,
      userPhoto,
      logout,
      toggleMobileSearch,
      isMobileSearchOpen
    }
  }
}
</script>

<style scoped>
/* انیمیشن کشویی جستجو */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}
.slide-down-enter-from {
  transform: translateY(-100%);
  opacity: 0;
}
.slide-down-enter-to {
  transform: translateY(0);
  opacity: 1;
}
.slide-down-leave-from {
  transform: translateY(0);
  opacity: 1;
}
.slide-down-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}
</style>



