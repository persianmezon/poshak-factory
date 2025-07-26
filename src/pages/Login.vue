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
        {{'Log In' }}
      </button>
    </form>

    <p class="text-teal-600 text-sm mt-6">or use Social media</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { signInWithCustomToken } from 'firebase/auth'
import { auth } from '@/firebase'


const router = useRouter()
const email = ref('')
const password = ref('')


const handleLogin = async () => {
  if (!email.value || !password.value) {
    alert('لطفاً ایمیل و رمز عبور را وارد کنید.')
    return
  }
  // ورود با Firebase از طریق PHP
  try {
    const response = await fetch('https://app.paryamezon.ir/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: email.value,
        password: password.value
      })
    })

    const result = await response.json()
    console.log('✅ result from PHP:', result)

    if (result.success) {
      localStorage.setItem('userRole', result.role)
      localStorage.setItem('userEmail', result.email)
      localStorage.setItem('uid', result.uid)
      localStorage.setItem('isLocalLogin', 'false')

      await signInWithCustomToken(auth, result.token)

      router.push('/dashboard')
    } else {
      alert(result.message || '❌ ورود Firebase ناموفق بود.')
    }
  } catch (error) {
    alert('❌ خطا در ارتباط با سرور')
    console.error(error)
  }
}
</script>

