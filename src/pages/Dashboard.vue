<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="flex min-h-screen bg-gray-100 text-gray-800">
    <div class="flex-1 p-6 space-y-10">

      <!-- ✅ داشبورد کارت‌ها و نمودار -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">📊 خلاصه داشبورد</h2>
        <!-- اینجا کارت‌ها و نمودار قرار می‌گیرند -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-blue-100 p-4 rounded text-center">کارت ۱</div>
          <div class="bg-green-100 p-4 rounded text-center">کارت ۲</div>
          <div class="bg-yellow-100 p-4 rounded text-center">کارت ۳</div>
        </div>
      </div>

      <!-- ✅ جدول کارگرها -->

   <div class="overflow-x-auto bg-white rounded-xl shadow">
      <table class="min-w-full text-sm text-right">
        <thead class="bg-blue-50 text-blue-700">
          <tr>
            <th class="py-3 px-4 text-right">👩‍🔧 نام کارگر</th>
            <th class="py-3 px-4 text-right">🔢 تعداد کار انجام‌شده</th>
            <th class="py-3 px-4 text-right">🏭 بخش</th>
            <th class="py-3 px-4 text-right">🕒 آخرین فعالیت</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="worker in topWorkers"
            :key="worker.uid"
            class="border-b hover:bg-gray-50 transition"
          >
            <td class="py-3 px-4 flex items-center gap-3">
              <span class="text-lg">👤</span>
              <span class="font-medium">{{ worker.name }}</span>
            </td>
            <td class="py-3 px-4 font-bold text-blue-600">{{ worker.total }}</td>
            <td class="py-3 px-4 text-gray-700">✂️ دوخت</td>
            <td class="py-3 px-4 text-gray-500">{{ formatDate(worker.lastTime) }}</td>
          </tr>

          <tr v-if="topWorkers.length === 0">
            <td colspan="4" class="text-center text-gray-400 py-6">هیچ داده‌ای وجود ندارد 😕</td>
          </tr>
          </tbody>
        </table>
        </div>

      <!-- ✅ نمودار پایین -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold mb-4">📈 نمودار عملکرد</h2>
        <div class="h-48 bg-gray-100 rounded flex items-center justify-center">
          (نمودار آزمایشی)
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  /* eslint-disable vue/multi-word-component-names */
  name: 'Dashboard',
  setup() {
    const scans = ref([])
    const workers = ref([])

    const fetchScans = async () => {
      try {
        const res = await fetch('/api/get-scans.php?t=' + Date.now())
        const json = await res.json()
        if (json.success) {
          scans.value = json.records.filter(r => r.section === 'دوخت')
        }
      } catch (e) {
        console.error('خطا در دریافت اسکن‌ها:', e)
      }
    }

    const fetchWorkers = async () => {
      try {
        const res = await fetch('/api/get-workers.php?t=' + Date.now())
        const json = await res.json()
        if (json.success) {
          workers.value = json.workers
        }
      } catch (e) {
        console.error('خطا در دریافت کارگرها:', e)
      }
    }

    const formatDate = (timestamp) => {
      if (!timestamp) return '-'
      const date = new Date(timestamp * 1000)
      return date.toLocaleString('fa-IR')
    }

    const topWorkers = computed(() => {
      const map = {}

      for (const scan of scans.value) {
        const id = scan.workerId
        if (!map[id]) {
          map[id] = {
            uid: id,
            total: 0,
            lastTime: 0
          }
        }
        map[id].total += scan.count || 0
        if (scan.createdAt > map[id].lastTime) {
          map[id].lastTime = scan.createdAt
        }
      }

      const result = Object.values(map)
        .map(item => ({
          ...item,
          name: (workers.value.find(w => w.uid === item.uid) || {}).name || 'نامشخص'
        }))
        .sort((a, b) => b.total - a.total)
        .slice(0, 8)

      return result
    })

    onMounted(() => {
      fetchScans()
      fetchWorkers()
    })

    return {
      topWorkers,
      formatDate
    }
  }
}
</script>
