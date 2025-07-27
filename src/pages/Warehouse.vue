<!-- eslint-disable -->
<template>
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold text-center text-gray-800">🏬 انبار کلی</h1>

    <!-- دکمه اکسل -->
    <div class="flex justify-end">
      <button
        @click="downloadExcel"
        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow"
      >
        📥 دانلود اکسل
      </button>
    </div>


<!-- کارت‌های آماری -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <!-- مجموع کل -->
  <div class="bg-white shadow rounded p-4 text-center cursor-default">
    <p class="text-sm text-gray-600">🔢 مجموع کل</p>
    <p class="text-xl font-bold text-blue-600">
      {{ totalAllSections }}
    </p>
  </div>

  <!-- انبار برش -->
  <router-link
    to="/cut-storage"
    class="bg-white shadow rounded p-4 text-center hover:bg-gray-100 transition cursor-pointer"
  >
    <p class="text-sm text-gray-600">✂️ انبار برش</p>
    <p class="text-xl font-bold text-purple-600">{{ totalBySection.cut }}</p>
  </router-link>

  <!-- سالن دوخت -->
  <router-link
    to="/sewing-inventory"
    class="bg-white shadow rounded p-4 text-center hover:bg-gray-100 transition cursor-pointer"
  >
    <p class="text-sm text-gray-600">🧵 سالن دوخت</p>
    <p class="text-xl font-bold text-pink-600">{{ totalBySection.sewing }}</p>
  </router-link>

  <!-- انبار نهایی‌کار -->
  <router-link
    to="/final-inventory"
    class="bg-white shadow rounded p-4 text-center hover:bg-gray-100 transition cursor-pointer"
  >
    <p class="text-sm text-gray-600">🎯 نهایی‌کار</p>
    <p class="text-xl font-bold text-yellow-600">{{ totalBySection.final }}</p>
  </router-link>
</div>


    <!-- فیلتر جستجو -->
    <input
      v-model="filterText"
      placeholder="🔍 جستجو بر اساس کد مانتو..."
      class="border p-2 rounded w-full shadow"
    />

    <!-- جدول -->
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-right border border-gray-300 rounded overflow-hidden">
        <thead class="bg-gray-100 text-gray-700">
          <tr>
            <th class="border px-4 py-2">🔖 کد مانتو</th>
            <th class="border px-4 py-2">📥 انبار برش</th>
            <th class="border px-4 py-2">🧵 سالن دوخت</th>
            <th class="border px-4 py-2">🎯 نهایی‌کار</th>
            <th class="border px-4 py-2">📦 جمع کل</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in filteredInventory"
            :key="item.code"
            class="odd:bg-white even:bg-gray-50 hover:bg-yellow-50 transition"
          >
            <td class="border px-4 py-2 font-bold">{{ item.code }}</td>
            <td class="border px-4 py-2 text-purple-700">{{ item.cut }}</td>
            <td class="border px-4 py-2 text-pink-700">{{ item.sewing }}</td>
            <td class="border px-4 py-2 text-yellow-700">{{ item.final }}</td>
            <td class="border px-4 py-2 font-bold text-blue-700">{{ item.total }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { initializeApp } from 'firebase/app'
import { getDatabase, ref, get, child } from 'firebase/database'
import { firebaseConfig } from '@/firebase'
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'

export default {
  data() {
    return {
      cutInventory: [],
      cutToSewing: [],
      sewingToFinal: [],
      filterText: ''
    }
  },

  computed: {
    inventoryByCode() {
      const result = {}

      // 1. ورودی برش
      this.cutInventory.forEach(item => {
        const code = item.code
        if (!result[code]) result[code] = { code, cut: 0, sewing: 0, final: 0 }
        result[code].cut += item.count || 0
      })

      // 2. خروج از برش
      this.cutToSewing.forEach(item => {
        const code = item.code
        if (!result[code]) result[code] = { code, cut: 0, sewing: 0, final: 0 }
        result[code].cut -= item.count || 0
        result[code].sewing += item.count || 0
      })

      // 3. خروج از دوخت
      this.sewingToFinal.forEach(item => {
        const code = item.code
        if (!result[code]) result[code] = { code, cut: 0, sewing: 0, final: 0 }
        result[code].sewing -= item.count || 0
        result[code].final += item.count || 0
      })

      // 4. محاسبه مجموع
      for (const code in result) {
        result[code].total = result[code].cut + result[code].sewing + result[code].final
      }

      return Object.values(result)
    },
totalBySection() {
  const sum = { cut: 0, sewing: 0, final: 0 }
  this.inventoryByCode.forEach(item => {
    sum.cut += item.cut
    sum.sewing += item.sewing
    sum.final += item.final
  })
  return sum
},
totalAllSections() {
  return this.totalBySection.cut + this.totalBySection.sewing + this.totalBySection.final
}
,
    filteredInventory() {
      const keyword = this.filterText.toLowerCase()
      return this.inventoryByCode.filter(item => item.code.toLowerCase().includes(keyword))
    }
  },

  methods: {
    async fetchData(path) {
      const app = initializeApp(firebaseConfig)
      const db = getDatabase(app)
      try {
        const snapshot = await get(child(ref(db), path))
        const data = snapshot.val()
        return data
          ? Object.values(data).map(item => ({
              code: item.code || '',
              count: item.count || 0
            }))
          : []
      } catch (err) {
        console.error(`❌ خطا در واکشی ${path}:`, err)
        return []
      }
    },

    async loadAllData() {
      this.cutInventory = await this.fetchData('cut_inventory')
      this.cutToSewing = await this.fetchData('cut_to_sewing')
      this.sewingToFinal = await this.fetchData('sewing_to_final')
    },

    downloadExcel() {
      const exportData = this.filteredInventory.map(item => ({
        'کد مانتو': item.code,
        'موجودی انبار برش': item.cut,
        'موجودی سالن دوخت': item.sewing,
        'موجودی نهایی‌کار': item.final,
        'جمع کل': item.total
      }))

      const worksheet = XLSX.utils.json_to_sheet(exportData)
      const workbook = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(workbook, worksheet, 'موجودی کلی')

      const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' })
      const blob = new Blob([excelBuffer], { type: 'application/octet-stream' })
      const today = new Date().toISOString().slice(0, 10)
      saveAs(blob, `inventory-total-${today}.xlsx`)
    }
  },

  async mounted() {
    await this.loadAllData()
  }
}
</script>
