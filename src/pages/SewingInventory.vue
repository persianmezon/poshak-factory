<!-- eslint-disable -->
<template>
  <div class="p-6 space-y-6">
    <!-- عنوان صفحه -->
    <h1 class="text-2xl font-bold flex items-center justify-center gap-2 text-gray-800">
      🧵 موجودی سالن دوخت
    </h1>

    <!-- دکمه اکسل -->
    <div class="flex justify-end">
      <button
        @click="downloadExcel"
        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow transition"
      >
        📥 دانلود اکسل
      </button>
    </div>

    <!-- انتخاب تاریخ -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
      <label class="font-medium text-gray-700">📅 نمایش آمار روز:</label>
      <input type="date" v-model="selectedDate" class="border rounded px-3 py-2 shadow-sm" />
    </div>

    <!-- فیلتر جستجو -->
    <input
      v-model="filterText"
      placeholder="🔍 جستجو در کد، قسمت یا کارگر..."
      class="border p-2 rounded w-full shadow-sm"
    />

    <!-- مجموع -->
    <p class="text-right text-sm text-gray-600">
      🔢 مجموع کل قطعات: <span class="font-semibold text-blue-700">{{ totalCount }}</span>
    </p>

    <!-- جدول -->
    <div class="overflow-x-auto rounded shadow-sm">
      <table class="min-w-full text-sm text-right border border-gray-300 rounded overflow-hidden">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="border px-4 py-2">🧩 قسمت</th>
            <th class="border px-4 py-2">🔖 کد</th>
            <th class="border px-4 py-2">📦 تعداد</th>
            <th class="border px-4 py-2">👷‍♀️ کارگر</th>
            <th class="border px-4 py-2">⏰ تاریخ</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in filteredRecordsByDate"
            :key="item.id"
            class="border odd:bg-white even:bg-gray-50 hover:bg-yellow-50 transition"
          >
            <td class="border px-4 py-2">{{ item.part || '-' }}</td>
            <td class="border px-4 py-2">{{ item.code }}</td>
            <td class="border px-4 py-2">{{ item.count }}</td>
            <td class="border px-4 py-2">{{ getWorkerName(item.workerId) }}</td>
            <td class="border px-4 py-2">
              {{ item.createdAt ? item.createdAt.toLocaleString('fa-IR') : '-' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'

export default {
  data() {
    return {
      records: [],
      workersList: [],
      selectedDate: new Date().toISOString().substr(0, 10),
      filterText: ''
    }
  },

  computed: {
    filteredRecords() {
      const keyword = this.filterText.toLowerCase()
      return this.records.filter(item =>
        (item.part || '').toLowerCase().includes(keyword) ||
        (item.code || '').toLowerCase().includes(keyword) ||
        this.getWorkerName(item.workerId).toLowerCase().includes(keyword)
      )
    },
    filteredRecordsByDate() {
      return this.filteredRecords.filter(item =>
        item.createdAt?.toISOString().substr(0, 10) === this.selectedDate
      )
    },
    totalCount() {
      return this.filteredRecordsByDate.reduce((sum, item) => sum + (item.count || 0), 0)
    }
  },

  methods: {
    getWorkerName(uid) {
      const w = this.workersList.find(w => w.uid === uid)
      return w ? w.name : 'نامشخص'
    },

    async fetchWorkers() {
      try {
        const res = await fetch('https://app.paryamezon.ir/api/get-workers.php?t=' + Date.now())
        const json = await res.json()
        if (json.success && Array.isArray(json.workers)) {
          this.workersList = json.workers
        }
      } catch (err) {
        console.error('❌ خطا در واکشی کارگران:', err)
      }
    },

    async fetchRecords() {
      try {
        const res = await fetch('https://app.paryamezon.ir/api/get-sewing.php?t=' + Date.now())
        const json = await res.json()
        if (json.success && Array.isArray(json.records)) {
          this.records = json.records.map(item => ({
            ...item,
            createdAt: item.createdAt ? new Date(item.createdAt * 1000) : null
          }))
        }
      } catch (err) {
        console.error('❌ خطا در دریافت اطلاعات سالن دوخت:', err)
      }
    },

    downloadExcel() {
      const exportData = this.filteredRecordsByDate.map(item => ({
        'قسمت': item.part || '',
        'کد مانتو': item.code || '',
        'تعداد': item.count || 0,
        'نام کارگر': this.getWorkerName(item.workerId),
        'تاریخ': item.createdAt
          ? item.createdAt.toLocaleDateString('fa-IR')
          : ''
      }))

      const worksheet = XLSX.utils.json_to_sheet(exportData)
      const workbook = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(workbook, worksheet, 'گزارش سالن دوخت')

      const excelBuffer = XLSX.write(workbook, {
        bookType: 'xlsx',
        type: 'array'
      })

      const blob = new Blob([excelBuffer], {
        type: 'application/octet-stream'
      })

      const today = new Date().toLocaleDateString('fa-IR').replace(/\//g, '-')
      saveAs(blob, `گزارش-سالن-دوخت-${today}.xlsx`)
    }
  },

  // ✅ درست قرار گرفتن mounted خارج از methods
  async mounted() {
    await this.fetchWorkers()
    await this.fetchRecords()
  }
}
</script>
