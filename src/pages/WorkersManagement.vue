<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div v-if="isAdmin" class="p-6 space-y-6">
    <h1 class="text-2xl font-bold flex items-center gap-2">👷 مدیریت کارگرها</h1>

    <!-- افزودن کارگر -->
    <div class="flex flex-col sm:flex-row gap-4 items-center">
      <input
        v-model="newWorkerName"
        placeholder="نام کارگر جدید"
        class="border border-gray-300 rounded-xl px-4 py-2 w-full sm:w-1/2"
      />
      <button
        @click="addWorker"
        class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700"
        :disabled="!newWorkerName.trim()"
      >
        ➕ افزودن
      </button>
    </div>

    <!-- فیلد جستجو -->
    <input
      v-model="searchQuery"
      placeholder="🔍 جستجو بر اساس نام"
      class="border border-gray-300 rounded-xl px-4 py-2 w-full"
    />

    <!-- پیام موفقیت -->
    <div v-if="successMessage" class="text-green-600 font-bold">
      ✅ {{ successMessage }}
    </div>

    <!-- جدول -->
    <div class="overflow-x-auto bg-white rounded-xl shadow p-4">
      <table class="min-w-full text-sm text-right">
        <thead class="bg-blue-100 text-blue-800">
          <tr>
            <th class="px-4 py-2">👤 نام</th>
            <th class="px-4 py-2">🆔 UID</th>
            <th class="px-4 py-2">🛠 عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="worker in filteredWorkers"
            :key="worker.uid"
            class="hover:bg-blue-50 border-b"
          >
            <td class="px-4 py-2">{{ worker.name }}</td>
            <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ worker.uid }}</td>
            <td class="px-4 py-2">
              <button
                @click="deleteWorker(worker.uid)"
                class="text-red-600 hover:underline"
              >
                🗑 حذف
              </button>
            </td>
          </tr>
          <tr v-if="filteredWorkers.length === 0">
            <td colspan="3" class="text-center text-gray-500 py-4">
              لیستی برای نمایش وجود ندارد.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div v-else class="p-6 bg-red-100 text-red-600 rounded-xl">
    فقط مدیر به این بخش دسترسی دارد.
  </div>
</template>

<script>
export default {
  name: 'WorkersManagement',
  data() {
    return {
      workers: [],
      newWorkerName: '',
      searchQuery: '',
      successMessage: ''
    }
  },
  computed: {
    isAdmin() {
      return localStorage.getItem('userRole') === 'admin'
    },
    filteredWorkers() {
      return this.workers.filter(w =>
        w.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      )
    }
  },
  mounted() {
    this.fetchWorkers()
  },
  methods: {
async fetchWorkers() {
  try {
    const res = await fetch(`/api/get-workers.php?t=${Date.now()}`) // جلوگیری از کش
    const json = await res.json()
    if (json.success) {
      // تریگر تغییر اجباری با تغییر ری‌اکتیو
      this.workers = []
      this.$nextTick(() => {
        this.workers = json.workers
      })
    }
  } catch (err) {
    console.error('❌ خطا در دریافت لیست کارگران:', err)
  }
}
,
async addWorker() {
  const name = this.newWorkerName.trim()
  if (!name) return

  const uid = 'worker_' + Math.floor(Date.now() / 1000)

  try {
    const res = await fetch('/api/add-worker.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ uid, name })
    })
    const json = await res.json()
    if (json.success) {
      this.successMessage = '✅ کارگر جدید ثبت شد.'
      this.newWorkerName = ''
      
      // مهم: منتظر واکشی مجدد لیست باشیم
      await this.fetchWorkers()

      setTimeout(() => (this.successMessage = ''), 3000)
    } else {
      alert('❌ خطا: ' + json.message)
    }
  } catch (err) {
    console.error('❌ خطا در افزودن کارگر:', err)
  }
},
async deleteWorker(uid) {
  if (!confirm('آیا از حذف این کارگر مطمئن هستید؟')) return

  try {
    const res = await fetch('/api/delete-worker.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ uid })
    })
    const json = await res.json()
    if (json.success) {
      // مهم: بعد از حذف، دوباره لیست واکشی شود
      await this.fetchWorkers()
    } else {
      alert('❌ خطا در حذف کارگر: ' + json.message)
    }
  } catch (err) {
    console.error('❌ خطا در حذف کارگر:', err)
  }
}
  }
}
</script>

