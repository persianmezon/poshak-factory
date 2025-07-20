<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-white px-4">
    <!-- تصویر بالای فرم -->
    <img
      src="@/assets/images/login-banner.png"
      alt="Login Banner"
      class="w-3/4 max-w-xs mx-auto mb-6"
    />

    <!-- متن خوشامدگویی -->
    <h1 class="text-3xl font-semibold text-teal-700 mb-1">Welcome</h1>
    <p class="text-teal-600 mb-2">Login using your credentials</p>

    <!-- دکمه تغییر روش ورود -->
    <button
      @click="useFirebase = !useFirebase"
      class="mb-4 text-sm text-blue-700 underline"
    >
      Switch to {{ useFirebase ? 'Local Login' : 'Firebase Login' }}
    </button>

    <!-- فرم ورود -->
    <form @submit.prevent="handleLogin" class="w-full max-w-sm space-y-4">
      <div>
        <label class="text-sm text-blue-900 block mb-1">Email</label>
        <input
          v-model="email"
          placeholder="Enter your email"
          class="w-full border border-blue-800 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <div>
        <label class="text-sm text-blue-900 block mb-1">Password</label>
        <input
          v-model="password"
          type="password"
          placeholder="Enter your password"
          class="w-full border border-blue-800 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <button
        type="submit"
        class="w-full bg-blue-800 text-white py-2 rounded hover:bg-blue-900 transition"
      >
        {{ useFirebase ? 'Log In with Firebase' : 'Log In Locally' }}
      </button>
    </form>

    <p class="text-teal-600 text-sm mt-6">or use Social media</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { signInWithEmailAndPassword } from 'firebase/auth'
import { auth } from '@/firebase'
import { localUsers } from '@/utils/localUsers' // فایل کاربران زاپاس

const router = useRouter()
const email = ref('')
const password = ref('')
const useFirebase = ref(true)

const tryLocalLogin = (emailVal, passVal) => {
  const user = localUsers.find(
    (u) => u.email === emailVal && u.password === passVal
  )
  if (user) {
    localStorage.setItem('userRole', user.role)
    localStorage.setItem('userEmail', user.email)
    localStorage.setItem('isLocalLogin', 'true')
    return true
  }
  return false
}

const handleLogin = async () => {
  if (!email.value || !password.value) {
    alert('لطفاً ایمیل و رمز عبور را وارد کنید.')
    return
  }

  if (useFirebase.value) {
    try {
      await signInWithEmailAndPassword(auth, email.value, password.value)
      // اینجا می‌تونی نقش رو از Firestore بگیری، ولی فعلاً فرض می‌گیریم نقش پیش‌فرضه
      localStorage.setItem('userRole', 'admin') // یا مقدار واقعی از دیتابیس
      localStorage.setItem('userEmail', email.value)
      localStorage.setItem('isLocalLogin', 'false')
      router.push('/dashboard')
    } catch (error) {
      alert('❌ ورود با Firebase ناموفق بود.')
    }
  } else {
    const success = tryLocalLogin(email.value, password.value)
    if (success) {
      router.push('/dashboard')
    } else {
      alert('❌ ورود محلی ناموفق بود.')
    }
  }
}
</script>
