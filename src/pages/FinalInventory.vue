<!-- eslint-disable -->
<template>
  <div class="p-4 md:p-6 space-y-6">
<h1 class="text-2xl font-bold flex items-center justify-center gap-2 text-gray-800">
  📦 موجودی نهایی‌کار
</h1>

    <!-- اکسل و تاریخ -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div class="flex items-center gap-3">
        <label class="font-medium">📅 نمایش آمار روز:</label>
        <input type="date" v-model="selectedDate" class="border p-2 rounded-md" />
      </div>

      <button
        @click="downloadExcel"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md shadow flex items-center gap-2"
      >
        📥 دانلود اکسل
      </button>
    </div>

    <!-- فیلتر جستجو -->
    <div>
      <input
        v-model="filterText"
        placeholder="🔍 جستجو در کد یا کارگر..."
        class="border px-4 py-2 rounded-md w-full shadow-sm"
      />
    </div>

    <!-- خلاصه آماری -->
    <p class="text-right text-sm text-gray-600 font-medium">
      🔢 مجموع کل قطعات: <span class="text-green-700 font-bold">{{ totalCount }}</span>
    </p>

    <!-- جدول -->
    <div class="overflow-x-auto rounded-md shadow-sm border">
      <table class="w-full text-sm text-right">
        <thead class="bg-gray-100 text-gray-800 font-semibold">
          <tr>
            <th class="px-4 py-2 border">کد</th>
            <th class="px-4 py-2 border">تعداد</th>
            <th class="px-4 py-2 border">کارگر</th>
            <th class="px-4 py-2 border">تاریخ</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in filteredRecordsByDate"
            :key="item.id"
            class="odd:bg-white even:bg-gray-50 hover:bg-blue-50 transition"
          >
            <td class="px-4 py-2 border">{{ item.code }}</td>
            <td class="px-4 py-2 border">{{ item.count }}</td>
            <td class="px-4 py-2 border">{{ getWorkerName(item.workerId) }}</td>
            <td class="px-4 py-2 border">
              {{ item.createdAt ? item.createdAt.toLocaleString('fa-IR') : '-' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { initializeApp } from "firebase/app"
import { getDatabase, ref, get, child } from "firebase/database"
import { firebaseConfig } from "@/firebase"
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
    downloadExcel() {
      const exportData = this.filteredRecordsByDate.map(item => ({
        'کد مانتو': item.code || '',
        'تعداد': item.count || 0,
        'نام کارگر': this.getWorkerName(item.workerId),
        'تاریخ': item.createdAt
          ? item.createdAt.toLocaleDateString('fa-IR')
          : ''
      }))

      const worksheet = XLSX.utils.json_to_sheet(exportData)
      const workbook = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(workbook, worksheet, 'گزارش نهایی‌کار')

      const excelBuffer = XLSX.write(workbook, {
        bookType: 'xlsx',
        type: 'array'
      })

      const blob = new Blob([excelBuffer], {
        type: 'application/octet-stream'
      })

      const today = new Date().toLocaleDateString('fa-IR').replace(/\//g, '-')
      saveAs(blob, `گزارش-نهایی‌کار-${today}.xlsx`)
    },
    async fetchRecords() {
      const app = initializeApp(firebaseConfig)
      const db = getDatabase(app)
      try {
        const snapshot = await get(child(ref(db), 'sewing_to_final'))
        const data = snapshot.val()
        if (data) {
          this.records = Object.entries(data).map(([id, item]) => ({
            id,
            ...item,
            createdAt: item.createdAt ? new Date(item.createdAt * 1000) : null
          }))
        }
      } catch (err) {
        console.error('❌ خطا در واکشی آمار نهایی‌کار:', err)
      }
    }
  },
  async mounted() {
    await this.fetchWorkers()
    await this.fetchRecords()
  }
}
</script>
