<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="p-6 space-y-6">
    <h1 class="text-xl font-bold text-center">📦 آمار انبار برش</h1>

    <div class="mb-4">
  <label class="block mb-1 font-medium">فیلتر بر اساس کد مانتو:</label>
  <input
    v-model="filterCode"
    type="text"
    placeholder="مثلاً 564"
    class="w-full border px-3 py-2 rounded"
  />
</div>

    <!-- فیلتر تاریخ -->
    <div class="flex items-center justify-center gap-4">
      <label>📅 فیلتر بر اساس تاریخ:</label>
      <input type="date" v-model="selectedDate" class="border px-2 py-1 rounded" />
      <button @click="filterByDate" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">اعمال فیلتر</button>
    </div>


<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
  <div class="bg-white shadow rounded-xl p-6 flex flex-col items-center justify-center text-center">
    <div class="text-xl font-bold text-blue-600">{{ totalCutCount }}</div>
    <div class="text-sm text-gray-500 mt-1">✂️ مجموع برش‌ها</div>
  </div>
  <div class="bg-white shadow rounded-xl p-6 flex flex-col items-center justify-center text-center">
    <div class="text-xl font-bold text-green-600">{{ totalMainFabric }}</div>
    <div class="text-sm text-gray-500 mt-1">🧵 پارچه اصلی</div>
  </div>
  <div class="bg-white shadow rounded-xl p-6 flex flex-col items-center justify-center text-center">
    <div class="text-xl font-bold text-pink-600">{{ totalLiningFabric }}</div>
    <div class="text-sm text-gray-500 mt-1">🪡 آستر</div>
  </div>
</div>

    <!-- جدول داده‌ها -->
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border mt-4">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-4 py-2">نوع پارچه</th>
            <th class="border px-4 py-2">کد مانتو</th>
            <th class="border px-4 py-2">تعداد</th>
            <th class="border px-4 py-2">تاریخ</th>
          </tr>
        </thead>
        <tbody>
     <tr v-for="(item, index) in filteredData" :key="index">
            <td class="border px-4 py-2">{{ item.part }}</td>
            <td class="border px-4 py-2">{{ item.code }}</td>
            <td class="border px-4 py-2">{{ item.count }}</td>
            <td class="border px-4 py-2">{{ convertToShamsi(item.createdAt) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- مجموع -->
    <div class="text-center font-bold text-green-700 mt-4">
      ✅ مجموع کل: {{ totalCount }} عدد
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import moment from 'moment-jalaali'

export default {
  data() {
    return {
      allItems: [],
      filteredItems: [],
      records: [],
      filterCode: '',
      selectedDate: ''
    }
  },

  computed: {
    // 🔢 مجموع کل برش‌ها
    totalCutCount() {
      return this.records.reduce((sum, item) => sum + Number(item.count || 0), 0)
    },

    // 🧵 مجموع پارچه اصلی
    totalMainFabric() {
      return this.records
        .filter(item => item.part === 'پارچه اصلی')
        .reduce((sum, item) => sum + Number(item.count || 0), 0)
    },

    // 🪡 مجموع آستر
    totalLiningFabric() {
      return this.records
        .filter(item => item.part === 'آستر')
        .reduce((sum, item) => sum + Number(item.count || 0), 0)
    },

    // 🔍 فیلتر بر اساس کد مانتو
    filteredData() {
      if (!this.filterCode.trim()) {
        return this.records
      }
      return this.records.filter(item =>
        item.code.toString().includes(this.filterCode.trim())
      )
    }
  },

  methods: {
    // 📥 واکشی داده‌ها از سرور
    async fetchData() {
      try {
        const res = await axios.get('/api/get-cut-inventory.php?t=' + Date.now())
        if (res.data.success) {
          this.allItems = res.data.records
          this.records = res.data.records
          this.filteredItems = res.data.records
        }
      } catch (err) {
        console.error('❌ خطا در دریافت داده‌ها:', err)
      }
    },

    // 🗓 فیلتر بر اساس تاریخ
    filterByDate() {
      if (!this.selectedDate) {
        this.filteredItems = this.allItems
        return
      }

      const selected = new Date(this.selectedDate)
      this.filteredItems = this.allItems.filter(item => {
        const itemDate = new Date(item.createdAt * 1000)
        return itemDate.toDateString() === selected.toDateString()
      })

      // همچنین مقدار records را هم به‌روز کن تا در computedها استفاده شود
      this.records = this.filteredItems
    },

    // 📆 تبدیل زمان به تاریخ شمسی
    convertToShamsi(timestamp) {
      return moment.unix(timestamp).format('jYYYY/jMM/jDD')
    }
  },

  mounted() {
    this.fetchData()
  }
}
</script>

