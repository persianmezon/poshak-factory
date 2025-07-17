<!-- eslint-disable -->
<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold flex items-center gap-2">
        🧵 سالن دوخت
      </h1>
      <p class="text-gray-500 text-sm">ثبت عملکرد خیاط‌ها در دسته‌های دوخت</p>
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
            <th class="px-4 py-2">📦 کد مانتو</th>
            <th class="px-4 py-2">🔢 تعداد</th>
            <th class="px-4 py-2">🕒 تاریخ</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="batch in filteredBatches"
            :key="batch.id"
            class="hover:bg-gray-50 border-b"
          >
            <td class="px-4 py-2">{{ batch.workerName }}</td>
            <td class="px-4 py-2">{{ batch.code }}</td>
            <td class="px-4 py-2">{{ batch.count }}</td>
            <td class="px-4 py-2">{{ formatDate(batch.createdAt) }}</td>
          </tr>
          <tr v-if="filteredBatches.length === 0">
            <td colspan="4" class="text-center text-gray-500 py-4">داده‌ای برای نمایش وجود ندارد.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { getFirestore, collection, onSnapshot } from 'firebase/firestore'
import '@/firebase'

export default {
  name: 'SewingHall',
  setup() {
    const batches = ref([])
    const filterWorker = ref('')
    const startDate = ref('')
    const endDate = ref('')

    const formatDate = (timestamp) => {
      if (!timestamp?.seconds) return '---'
      return new Date(timestamp.seconds * 1000).toLocaleDateString('fa-IR')
    }

    const filteredBatches = computed(() => {
      return batches.value.filter(batch => {
        const matchWorker = batch.workerName?.includes(filterWorker.value.trim())
        const createdAt = batch.createdAt?.seconds ? new Date(batch.createdAt.seconds * 1000) : null

        const matchStart = startDate.value ? createdAt >= new Date(startDate.value) : true
        const matchEnd = endDate.value ? createdAt <= new Date(endDate.value + 'T23:59:59') : true

        return matchWorker && matchStart && matchEnd
      })
    })

    const totalCount = computed(() =>
      filteredBatches.value.reduce((sum, item) => sum + (item.count || 0), 0)
    )

    onMounted(() => {
      const db = getFirestore()
      const colRef = collection(db, 'batches')
      onSnapshot(colRef, (snapshot) => {
        batches.value = snapshot.docs.map(doc => ({
          id: doc.id,
          ...doc.data()
        }))
      })
    })

    return {
      batches,
      filterWorker,
      startDate,
      endDate,
      formatDate,
      filteredBatches,
      totalCount
    }
  }
}
</script>
