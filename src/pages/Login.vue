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
    <p class="text-teal-600 mb-6">Log & Create account</p>

    <!-- فرم ورود -->
    <form @submit.prevent="handleLogin" class="w-full max-w-sm space-y-4">
      <div>
        <label class="text-sm text-blue-900 block mb-1">Username</label>
        <input
          v-model="username"
          placeholder="Enter your username"
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
        Log In
      </button>
    </form>

    <p class="text-teal-600 text-sm mt-6">or use Social media</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { signInWithEmailAndPassword } from 'firebase/auth'
import { auth } from '@/firebase'
import { useRouter } from 'vue-router'

const router = useRouter()
const username = ref('')
const password = ref('')

const handleLogin = async () => {
  const email = username.value
  const pass = password.value

  // 👇 ورود تستی برای نقش admin
  if (email === 'admin@factory.com' && pass === '123456') {
    localStorage.setItem('userRole', 'admin')
    localStorage.setItem('userEmail', email)
    router.push('/dashboard')
    return
  }

  // 🔐 ورود واقعی با Firebase Auth
  try {
    await signInWithEmailAndPassword(auth, email, pass)
    // می‌تونی بعداً role رو از Firestore بگیری، اما فعلاً برای ورود معمولی فقط انتقال بده
    router.push('/dashboard')
  } catch (error) {
    alert('❌ ورود ناموفق بود. لطفاً اطلاعات را بررسی کنید.')
  }
}

</script>
