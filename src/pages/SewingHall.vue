<!-- eslint-disable -->
<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold flex items-center gap-2">
        🧵 سالن دوخت
      </h1>
      <p class="text-gray-500 text-sm">ثبت عملکرد خیاط‌ها در بخش دوخت</p>
    </div>

    <!-- کارت مجموع کل قطعات -->
    <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between">
      <div class="text-sm text-gray-500">🔢 مجموع کل قطعات</div>
      <div class="text-xl font-bold text-blue-600">{{ totalCount }}</div>
    </div>

    <!-- فیلترها -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-white rounded-xl shadow p-4">
      <div>
        <label class="block text-sm font-medium mb-1">🔍 نام خیاط:</label>
        <input
          v-model="filterWorker"
          placeholder="مثلاً زهرا"
          class="border border-gray-300 p-2 rounded w-full"
        />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">از تاریخ:</label>
        <input type="date" v-model="startDate" class="border border-gray-300 p-2 rounded w-full" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">تا تاریخ:</label>
        <input type="date" v-model="endDate" class="border border-gray-300 p-2 rounded w-full" />
      </div>
    </div>

    <!-- جدول آمار -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
      <table class="min-w-full text-sm text-right">
        <thead class="bg-gray-100 text-gray-600">
          <tr>
            <th class="px-4 py-2">👩‍🧵 خیاط</th>
            <th class="px-4 py-2">🧩 قسمت</th>
            <th class="px-4 py-2">📦 کد مانتو</th>
            <th class="px-4 py-2">🔢 تعداد</th>
            <th class="px-4 py-2">🕒 تاریخ</th>
            <th class="px-4 py-2">⚙️ عملیات</th>
          </tr>
        </thead>
        <tbody>
<tr
  v-for="item in filteredItems"
  :key="item.id"
  class="hover:bg-gray-50 border-b"
>
  <td class="px-4 py-2">{{ item.workerId ? getWorkerName(item.workerId) : '---' }}</td>
  <td class="px-4 py-2">{{ item.part || '---' }}</td>
  <td class="px-4 py-2">{{ item.code }}</td>
  <td class="px-4 py-2">{{ item.count }}</td>
  <td class="px-4 py-2">{{ formatDate(item.createdAt) }}</td>
  <td class="px-4 py-2">
    <!-- دکمه‌ها فعلاً مخفی -->
  </td>
</tr>

          <tr v-if="filteredItems.length === 0">
            <td colspan="5" class="text-center text-gray-500 py-4">داده‌ای برای نمایش وجود ندارد.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'

export default {
  name: 'SewingHall',
  setup() {
    const items = ref([])
    const workers = ref([])
    const filterWorker = ref('')
    const startDate = ref('')
    const endDate = ref('')

    const getWorkerName = (id) => {
      const w = workers.value.find(w => w.uid === id)
      return w ? w.name : 'نامشخص'
    }

    const fetchItems = async () => {
      try {
        const res = await fetch('/api/get-scans.php?t=' + Date.now()) // جلوگیری از کش
        const json = await res.json()
        if (json.success) {
          items.value = json.records.map(r => {
            if (r.createdAt && typeof r.createdAt === 'number') {
              r.createdAt = new Date(r.createdAt * 1000)
            }
            return r
          })
        }
      } catch (err) {
        console.error('❌ خطا در دریافت رکوردها:', err)
      }
    }
const fetchWorkers = async () => {
  try {
    const res = await fetch('/api/get-workers.php?t=' + Date.now())
    const json = await res.json()
    if (json.success) {
      workers.value = json.workers.map(w => ({
        uid: w.uid,
        name: w.name
      }))
    }
  } catch (err) {
    console.error('❌ خطا در دریافت کارگرها:', err)
  }
}


    const formatDate = (date) => {
      if (!date) return '-'
      if (date instanceof Date) return date.toLocaleDateString('fa-IR')
      try {
        return new Date(date).toLocaleDateString('fa-IR')
      } catch {
        return '-'
      }
    }

const filteredItems = computed(() => {
  return items.value
    .filter(item => item && item.section === 'دوخت')
    .filter(item => {
      const name = getWorkerName(item.workerId || '')
      const nameMatch = name.includes(filterWorker.value.trim())
      const dateObj = item.createdAt ? new Date(item.createdAt) : null
      const from = startDate.value ? new Date(startDate.value) : null
      const to = endDate.value ? new Date(endDate.value + 'T23:59:59') : null

      if (from && dateObj && dateObj < from) return false
      if (to && dateObj && dateObj > to) return false

      return nameMatch
    })
})

    const totalCount = computed(() =>
      filteredItems.value.reduce((sum, item) => sum + (item.count || 0), 0)
    )

    onMounted(async () => {
      await fetchWorkers()
      await fetchItems()
    })

    return {
      items,
      workers,
      filterWorker,
      startDate,
      endDate,
      filteredItems,
      totalCount,
      formatDate,
      getWorkerName
    }
  }
}
</script>

