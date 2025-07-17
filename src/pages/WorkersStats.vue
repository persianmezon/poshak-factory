<template><AppLayout>
  <h1 class="text-xl font-bold mb-4">📊 آمار خیاط‌ها</h1>

<table class="min-w-full bg-white rounded shadow">
  <thead class="bg-gray-100 text-right">
    <tr>
      <th class="p-2">👷‍♀️ خیاط</th>
      <th class="p-2">📦 تعداد دسته</th>
      <th class="p-2">🧵 مجموع قطعات</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(data, name) in workerStatsData" :key="name" class="border-t">
      <td class="p-2">{{ name }}</td>
      <td class="p-2">{{ data.count }}</td>
      <td class="p-2">{{ data.quantity }}</td>
    </tr>
  </tbody>
</table>

  <div class="flex gap-4 mb-4">
  <div>
    <label>ماه اول:</label>
    <select v-model="selectedMonth1" class="border rounded p-1">
      <option disabled value="">انتخاب کنید</option>
      <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
    </select>
  </div>

  <Bar
  :chart-data="{
    labels: comparisonData.labels,
    datasets: [
      {
        label: 'ماه اول',
        backgroundColor: '#4F46E5',
        data: comparisonData.dataset1
      },
      {
        label: 'ماه دوم',
        backgroundColor: '#F59E0B',
        data: comparisonData.dataset2
      }
    ]
  }"
  :options="{ responsive: true, maintainAspectRatio: false }"
  style="height: 300px"
/>

<div class="flex gap-2 mt-2">
  <button @click="selectAllWorkers" class="bg-blue-500 text-white px-3 py-1 rounded">
    انتخاب همه 👥
  </button>
  <button @click="clearWorkerSelection" class="bg-gray-400 text-white px-3 py-1 rounded">
    پاک‌کردن انتخاب ❌
  </button>
</div>

  <div>
    <label>ماه دوم:</label>
    <select v-model="selectedMonth2" class="border rounded p-1">
      <option disabled value="">انتخاب کنید</option>
      <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
    </select>
  </div>
</div>

  <!-- 🗓 انتخاب ماه گزارش -->
<div class="mb-4">
  <label class="block mb-1">🗓 انتخاب ماه برای گزارش:</label>
  <select v-model="selectedMonth" class="border p-2 rounded w-full">
    <option disabled value="">لطفاً یک ماه انتخاب کنید</option>
    <option v-for="month in months" :key="month.value" :value="month.value">
      {{ month.label }}
    </option>
  </select>
</div>

<router-link
  to="/sewing-hall"
  class="inline-block mb-4 bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded"
>
  🔙 بازگشت به لیست دسته‌ها
</router-link>


<div class="bg-gray-100 rounded p-4 mb-4 shadow text-right">
  <p>📦 مجموع قطعات: <strong>{{ summaryStats.total }}</strong></p>
  <p>👷‍♀️ تعداد خیاط‌ها: <strong>{{ summaryStats.workersCount }}</strong></p>
  <p>📊 میانگین سهم: <strong>{{ summaryStats.avgShare }}٪</strong></p>
</div>

  <div class="mb-4 text-right">
    <!-- دکمه PDF -->
    <button @click="downloadPDF" class="bg-red-600 text-white px-4 py-2 rounded ml-2">
      📥 ذخیره PDF
    </button>

    <button @click="saveReport" class="bg-blue-600 text-white px-4 py-2 rounded ml-2">
  📦 ذخیره گزارش
</button>

    <!-- ✅ دکمه Excel -->
    <button @click="downloadExcel" class="bg-green-600 text-white px-4 py-2 rounded">
      📤 خروجی Excel
    </button>

    <button @click="clearWorkerStats" class="bg-red-600 text-white px-4 py-2 rounded ml-4">
  🗑 پاک‌سازی آمار ذخیره‌شده
</button>

    <!-- ✅ دکمه رفتن به تاریخچه -->
<button @click="goToHistory" class="bg-gray-600 text-white px-4 py-2 rounded ml-2">
  🕓 تاریخچه گزارش‌ها
</button>
<button @click="downloadChangeReportPDF" class="bg-purple-600 text-white px-4 py-2 rounded mt-4">
  دانلود PDF تغییرات درصدی 📄
</button>
<button @click="downloadChangeReportExcel" class="bg-green-600 text-white px-4 py-2 rounded mt-2">
  دانلود Excel تغییرات درصدی 📊
</button>
<button
  v-if="role === 'supervisor'"
  @click="savePerformanceWarnings"
  class="bg-red-600 text-white px-4 py-2 rounded mt-4"
>
  ذخیره هشدارهای افت عملکرد در Firestore 📥
</button>

<button @click="downloadWorkerStatsPDF" class="bg-purple-600 text-white px-4 py-2 rounded mt-4">
  📄 دانلود PDF آمار خیاط‌ها
</button>


  </div>

<!-- ✅ فیلتر بر اساس خیاط -->
<div class="mb-4">
  <label class="block mb-1">🔍 فیلتر بر اساس خیاط:</label>
  <select v-model="selectedWorker" class="border p-2 rounded w-full">
    <option value="">نمایش همه</option>
    <option v-for="(total, name) in workerStats" :key="name" :value="name">{{ name }}</option>
  </select>

  <button
    v-if="selectedWorker"
    @click="selectedWorker = ''"
    class="mt-2 px-3 py-1 bg-gray-300 rounded text-sm"
  >
    🔁 نمایش همه خیاط‌ها
  </button>
</div>

<!-- ✅ فیلتر بر اساس ماه -->
<div class="mb-4">
  <label class="block mb-1">📅 فیلتر بر اساس ماه:</label>
  <select v-model="selectedMonth" class="border p-2 rounded w-full">
    <option value="">نمایش همه ماه‌ها</option>
    <option v-for="month in months" :key="month.value" :value="month.value">
      {{ month.label }}
    </option>
  </select>

  <button
    v-if="selectedMonth"
    @click="selectedMonth = ''"
    class="mt-2 px-3 py-1 bg-gray-300 rounded text-sm"
  >
    🔁 نمایش همه ماه‌ها
  </button>
</div>


  <div class="p-6 bg-white rounded shadow mt-6">
    <h2 class="text-xl font-bold mb-4">📊 آمار عملکرد خیاط‌ها</h2>
    <p v-if="selectedMonth" class="text-sm text-gray-700 mb-2">
  🗓 نمایش آمار مربوط به ماه: <strong>{{ getMonthLabel(selectedMonth) }}</strong>
</p>


    <div class="stats-container">
      <div v-if="stats.length === 0" class="text-gray-500">
        هیچ داده‌ای برای نمایش وجود ندارد.
      </div>

      <!-- جدول آمار -->
      <table v-else class="min-w-full bg-white rounded shadow mb-6">
        <thead class="bg-gray-200 text-right">
          <tr>
            <th class="p-2">👷‍♀️ نام کارگر</th>
            <th class="p-2">🧵 مجموع قطعات</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(total, name) in filteredWorkerStats" :key="name">
            <td class="p-2">{{ name }}</td>
            <td class="p-2">{{ total }}</td>
          </tr>
        </tbody>
      </table>

      <h3 class="text-lg font-semibold mt-6 mb-2">🎯 سهم نسبی خیاط‌ها</h3>
<div style="height: 300px;">
  <PieChart :chart-data="pieChartData" />
</div>

<div class="mb-4">
  <label class="font-bold">انتخاب خیاط‌ها برای نمایش در نمودار:</label>
  <select v-model="selectedWorkers" multiple class="border p-2 rounded w-full mt-1">
    <option v-for="name in allWorkerNames" :key="name" :value="name">{{ name }}</option>
  </select>
</div>

<table class="w-full border text-right mt-6">
  <thead>
    <tr class="bg-gray-200">
      <th class="p-2">نام خیاط</th>
      <th class="p-2">تعداد قطعات</th>
      <th class="p-2">تغییر نسبت به ماه قبل</th>
    </tr>
  </thead>
  <tbody>
    <tr
      v-for="(value, name) in workerStats"
      :key="name"
      class="border-t"
    >
      <td class="p-2">{{ name }}</td>
      <td class="p-2">{{ value }}</td>
      <td class="p-2">
        <span class="text-red-600 font-bold" v-if="performanceWarnings[name]">
  ⚠️ افت {{ performanceWarnings[name] }}٪ نسبت به ماه قبل
</span>
        <span v-if="workerChangeFromLastMonth[name]">
          <span
            v-if="workerChangeFromLastMonth[name] >= 0"
            class="text-green-600"
          >📈 +{{ workerChangeFromLastMonth[name] }}٪</span>
          <span
            v-else
            class="text-red-600"
          >📉 {{ workerChangeFromLastMonth[name] }}٪</span>
        </span>
        <span v-else class="text-gray-400">نامشخص</span>
      </td>
    </tr>
  </tbody>
</table>

      <!-- ✅ نمودار -->
      <BarChart
  v-if="filteredChartData && filteredChartData.labels && filteredChartData.labels.length"
  :chart-data="filteredChartData"
/>

      <!-- ✅ نمودار دایره‌ای (Pie) -->
<h3 class="text-lg font-semibold mt-6 mb-2">🎯 سهم نسبی خیاط‌ها</h3>
<div style="height: 300px;">
  <PieChart :chart-data="pieChartData" />
</div>
<!-- 🔢 نمایش درصد سهم هر خیاط -->
<ul class="mt-4 text-sm space-y-1">
  <li v-for="(percent, name) in workerShares" :key="name">
    {{ name }}: {{ percent }}٪ از کل
  </li>
</ul>

    </div>
  </div>

  <h3 class="text-lg font-bold my-4">📈 روند تغییرات ماهانه هر خیاط</h3>
<LineChart :chart-data="trendChartData" style="height: 400px;" />
</AppLayout>
</template>




<script>
import { Bar, Line, Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'
import AppLayout from '@/components/AppLayout.vue'
import * as XLSX from 'xlsx'
import { collection, addDoc, serverTimestamp } from 'firebase/firestore'
import { db } from '../firebase'
import autoTable from 'jspdf-autotable'
// eslint-disable-next-line no-unused-vars
import { useRouter } from 'vue-router'
// eslint-disable-next-line no-unused-vars
import { ref, onMounted } from 'vue'
// eslint-disable-next-line no-unused-vars
import { vazirmatnFontBase64 } from '@/utils/vazirmatn' 


ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'WorkersStats',

components: {
  AppLayout,
  BarChart: Bar,
  LineChart: Line,
  PieChart: Pie
}
,

  data() {
    return {
      stats: [],
      selectedWorker: '',
      selectedMonth: '',
      selectedMonth1: '',
      selectedMonth2: '',
      selectedWorkers: [],
      role: '',
      workerStatsData: {},
      months: [
        { value: '2025-01', label: 'دی ۱۴۰۳' },
        { value: '2025-02', label: 'بهمن ۱۴۰۳' },
        { value: '2025-03', label: 'اسفند ۱۴۰۳' },
        { value: '2025-04', label: 'فروردین ۱۴۰۴' },
        { value: '2025-05', label: 'اردیبهشت ۱۴۰۴' },
        { value: '2025-06', label: 'خرداد ۱۴۰۴' },
        { value: '2025-07', label: 'تیر ۱۴۰۴' },
        { value: '2025-08', label: 'مرداد ۱۴۰۴' },
        { value: '2025-09', label: 'شهریور ۱۴۰۴' },
        { value: '2025-10', label: 'مهر ۱۴۰۴' },
        { value: '2025-11', label: 'آبان ۱۴۰۴' },
        { value: '2025-12', label: 'آذر ۱۴۰۴' }
      ]
    }
  },

  mounted() {
    const raw = localStorage.getItem('filteredStats')
    this.stats = raw ? JSON.parse(raw) : []

    const rawStatsData = localStorage.getItem('workerStatsData')
    if (rawStatsData) {
      this.workerStatsData = JSON.parse(rawStatsData)
    } else {
      alert('⚠️ هیچ داده‌ای برای نمایش وجود ندارد.')
      this.$router.push('/sewing-hall')
    }

    const savedMonth = localStorage.getItem('selectedMonth')
    if (savedMonth) this.selectedMonth = savedMonth

    const savedWorkers = localStorage.getItem('selectedWorkers')
    if (savedWorkers) this.selectedWorkers = JSON.parse(savedWorkers)

    this.role = localStorage.getItem('userRole')
  },


  clearWorkerStats() {
  localStorage.removeItem('workerStatsData')
  this.workerStatsData = {}
}
,
  
  computed: {
    performanceWarnings() {
  const warnings = {}

  const currentMonth = this.selectedMonth
  if (!currentMonth) return warnings

  const monthIndex = this.months.findIndex(m => m.value === currentMonth)
  if (monthIndex <= 0) return warnings

  const prevMonth = this.months[monthIndex - 1].value

  const current = {}
  const previous = {}

  this.stats.forEach(item => {
    if (item.month === currentMonth) {
      current[item.workerName] = (current[item.workerName] || 0) + item.quantity
    }
    if (item.month === prevMonth) {
      previous[item.workerName] = (previous[item.workerName] || 0) + item.quantity
    }
  })

  Object.keys(current).forEach(name => {
    const curr = current[name]
    const prev = previous[name] || 0
    const dropPercent = prev === 0 ? 0 : ((prev - curr) / prev) * 100
    if (dropPercent >= 30) {
      warnings[name] = dropPercent.toFixed(1)
    }
  })

  return warnings
}
,
    trendChangeData() {
  const workerChangeMap = {}
  const monthValues = this.months.map(m => m.value)

  // آماده‌سازی داده: ساخت map ماهانه برای هر خیاط
  this.stats.forEach(item => {
    if (!workerChangeMap[item.workerName]) {
      workerChangeMap[item.workerName] = {}
    }
    if (!workerChangeMap[item.workerName][item.month]) {
      workerChangeMap[item.workerName][item.month] = 0
    }
    workerChangeMap[item.workerName][item.month] += item.quantity
  })

  // محاسبه درصد تغییرات ماهانه برای هر خیاط
  const datasets = Object.entries(workerChangeMap).map(([workerName, monthData]) => {
    const data = []

    for (let i = 0; i < monthValues.length; i++) {
      const month = monthValues[i]
      const prevMonth = monthValues[i - 1]

      const current = monthData[month] || 0
      const prev = i === 0 ? 0 : (monthData[prevMonth] || 0)

      const change = prev === 0 ? 0 : ((current - prev) / prev) * 100
      data.push(parseFloat(change.toFixed(1)))
    }

    return {
      label: workerName,
      data,
      fill: false,
      tension: 0.3,
      borderWidth: 2
    }
  })

  return {
    labels: monthValues,
    datasets
  }
}
,
    workerChangeFromLastMonth() {
  if (!this.selectedMonth) return {}

  // پیدا کردن ماه قبل
  const monthIndex = this.months.findIndex(m => m.value === this.selectedMonth)
  if (monthIndex <= 0) return {}

  const prevMonth = this.months[monthIndex - 1].value

  // محاسبه مجموع قطعات هر خیاط در ماه فعلی و قبلی
  const current = {}
  const previous = {}

  this.stats.forEach(item => {
    if (item.month === this.selectedMonth) {
      current[item.workerName] = (current[item.workerName] || 0) + item.quantity
    }
    if (item.month === prevMonth) {
      previous[item.workerName] = (previous[item.workerName] || 0) + item.quantity
    }
  })

  // محاسبه درصد تغییر
  const changes = {}
  Object.keys(current).forEach(name => {
    const curr = current[name]
    const prev = previous[name] || 0
    const percent = prev === 0 ? 100 : ((curr - prev) / prev) * 100
    changes[name] = percent.toFixed(1)
  })

  return changes
}
,
    allWorkerNames() {
  return Array.from(new Set(this.stats.map(item => item.workerName)))
}
,
    workerShares() {
  const total = Object.values(this.workerStats).reduce((a, b) => a + b, 0)
  const shares = {}
  for (const [name, qty] of Object.entries(this.workerStats)) {
    shares[name] = total > 0 ? ((qty / total) * 100).toFixed(1) : 0
  }
  return shares
}
,
trendChartData() {
  const monthValues = this.months.map(m => m.value)
  const workerMap = {}

  this.stats.forEach(item => {
    if (!workerMap[item.workerName]) {
      workerMap[item.workerName] = {}
    }
    if (!workerMap[item.workerName][item.month]) {
      workerMap[item.workerName][item.month] = 0
    }
    workerMap[item.workerName][item.month] += item.quantity
  })

  // 👉 فیلتر کردن فقط خیاط‌های انتخاب‌شده
  const filteredNames = this.selectedWorkers.length > 0
    ? Object.keys(workerMap).filter(name => this.selectedWorkers.includes(name))
    : Object.keys(workerMap)

  const datasets = filteredNames.map(name => ({
    label: name,
    data: monthValues.map(month => workerMap[name][month] || 0),
    fill: false,
    tension: 0.3,
    borderWidth: 2
  }))

  return {
    labels: monthValues,
    datasets
  }
}
,
comparisonData() {
  const getSummary = (month) => {
    const summary = {}
    this.stats.forEach(item => {
      if (item.month === month) {
        if (!summary[item.workerName]) summary[item.workerName] = 0
        summary[item.workerName] += item.quantity
      }
    })
    return summary
  }

  const m1 = getSummary(this.selectedMonth1)
  const m2 = getSummary(this.selectedMonth2)

  const allWorkers = Array.from(new Set([...Object.keys(m1), ...Object.keys(m2)]))

  return {
    labels: allWorkers,
    dataset1: allWorkers.map(name => m1[name] || 0),
    dataset2: allWorkers.map(name => m2[name] || 0)
  }
}
,

filteredWorkerStats() {
  const filtered = this.selectedMonth
    ? this.stats.filter(item => item.month === this.selectedMonth)
    : this.stats

  const summary = {}
  filtered.forEach(item => {
    if (!summary[item.workerName]) summary[item.workerName] = 0
    summary[item.workerName] += item.quantity
  })
  return summary
}
,

    filteredStats() {
  if (!this.selectedMonth) return this.stats

  return this.stats.filter(item => {
    if (!item.timestamp || !item.timestamp.seconds) return false
    const date = new Date(item.timestamp.seconds * 1000)
    const yearMonth = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
    return yearMonth === this.selectedMonth
  })
},
    filteredChartData() {
      return {
        labels: Object.keys(this.filteredWorkerStats),
        datasets: [
          {
            label: 'مجموع قطعات',
            backgroundColor: '#4F46E5',
            data: Object.values(this.filteredWorkerStats)
          }
        ]
      }
    },
    pieChartData() {
      return {
        labels: Object.keys(this.workerStats),
        datasets: [
          {
            label: 'سهم از تولید',
            backgroundColor: ['#4F46E5', '#22C55E', '#F59E0B', '#EF4444', '#14B8A6'],
            data: Object.values(this.workerStats)
          }
        ]
      }
    },
workerStats() {
  const summary = {}
  this.stats.forEach(item => {
    const itemMonth = item.month
    if (this.selectedMonth && itemMonth !== this.selectedMonth) return
    if (!summary[item.workerName]) summary[item.workerName] = 0
    summary[item.workerName] += item.quantity
  })
  return summary
},
summaryStats() {
  const total = Object.values(this.workerStats).reduce((a, b) => a + b, 0)
  const workersCount = Object.keys(this.workerStats).length
  const avgShare = workersCount > 0 ? (100 / workersCount).toFixed(1) : 0

  return {
    total,
    workersCount,
    avgShare
  }
}
,
    chartData() {
      return {
        labels: Object.keys(this.workerStats),
        datasets: [
          {
            label: 'مجموع قطعات',
            backgroundColor: '#4F46E5',
            data: Object.values(this.workerStats)
          }
        ]
      }
    }
  },

watch: {
  downloadWorkerStatsPDF() {
  const doc = new jsPDF()
  doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
  doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
  doc.setFont('Vazirmatn')
  doc.setFontSize(14)

  doc.text('📊 آمار خیاط‌ها', 10, 20)
  doc.setFontSize(12)

  let y = 35
  for (const [name, data] of Object.entries(this.workerStatsData)) {
    const count = data.count || '-'
    const quantity = data.quantity || '-'
    doc.text(`${name}: ${count} دسته، ${quantity} قطعه`, 10, y)
    y += 10
  }

  doc.save('workers-stats.pdf')
}
,
  selectedMonth(val) {
    localStorage.setItem('selectedMonth', val)
  },
  selectedWorkers(val) {
    localStorage.setItem('selectedWorkers', JSON.stringify(val))
  }
}
,
  methods: {
    async savePerformanceWarnings() {
  if (!this.selectedMonth) {
    alert('لطفاً یک ماه انتخاب کنید')
    return
  }

  const warnings = this.performanceWarnings
  const warningList = Object.entries(warnings).map(([name, drop]) => ({
    workerName: name,
    dropPercent: parseFloat(drop),
    month: this.selectedMonth,
    timestamp: new Date()
  }))

  try {
    for (const w of warningList) {
      await addDoc(collection(db, 'performanceWarnings'), w)
    }
    alert('⚠️ هشدارها در Firestore ذخیره شدند.')
  } catch (error) {
    console.error('خطا در ذخیره هشدار:', error)
    alert('❌ ذخیره هشدار ناموفق بود.')
  }
}
,
goToWorkerStats() {
  localStorage.setItem('workerStatsData', JSON.stringify(this.workerStats))
  this.$router.push('/workers-stats')
}
,
    downloadChangeReportExcel() {
  const XLSX = require('xlsx')

  const rows = this.trendChangeData.datasets.map(dataset => {
    const row = {
      'نام خیاط': dataset.label
    }

    this.months.forEach((m, i) => {
      const val = dataset.data[i]
      row[m.label] = (val >= 0 ? `+${val}%` : `${val}%`)
    })

    return row
  })

  const worksheet = XLSX.utils.json_to_sheet(rows)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'تغییرات درصدی')

  XLSX.writeFile(workbook, 'تغییرات-درصدی-خیاط‌ها.xlsx')
}
,
    downloadChangeReportPDF() {
  const doc = new jsPDF()
  doc.setFont('Vazirmatn', 'normal') // اگر فونت فارسی تعریف کرده باشی

  const headers = ['نام خیاط', ...this.months.map(m => m.label)]

  const rows = Object.entries(this.trendChangeData.datasets).map(([ dataset]) => {
    return [
      dataset.label,
      ...dataset.data.map(val => (val >= 0 ? `+${val}%` : `${val}%`))
    ]
  })

  doc.text('گزارش تغییرات درصدی عملکرد خیاط‌ها', 10, 10)
  autoTable(doc, {
    head: [headers],
    body: rows,
    startY: 20
  })

  doc.save('تغییرات-درصدی-خیاط‌ها.pdf')
}
,
    selectAllWorkers() {
  this.selectedWorkers = [...this.allWorkerNames]
},
clearWorkerSelection() {
  this.selectedWorkers = []
}
,
    downloadPDF() {
      const target = document.querySelector('.stats-container')
      html2canvas(target).then(canvas => {
        const imgData = canvas.toDataURL('image/png')
        const pdf = new jsPDF('p', 'mm', 'a4')
        const width = pdf.internal.pageSize.getWidth()
        const height = (canvas.height * width) / canvas.width
        pdf.addImage(imgData, 'PNG', 0, 0, width, height)
        pdf.save('آمار-خیاط‌ها.pdf')
      })
    },
downloadExcel() {
  const filteredStats = Object.entries(this.workerStats)
    .filter(([name]) => this.selectedWorkers.length === 0 || this.selectedWorkers.includes(name))

  const rows = filteredStats.map(([name, quantity]) => ({
    'نام کارگر': name,
    'مجموع قطعات': quantity
  }))

  const worksheet = XLSX.utils.json_to_sheet(rows)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'آمار خیاط‌ها')
  XLSX.writeFile(workbook, 'آمار-خیاط‌ها.xlsx')
}
,
async saveReport() {
  if (!this.selectedMonth) {
  alert('⚠️ لطفاً ابتدا یک ماه را انتخاب کنید.')
  return
}
  try {
    await addDoc(collection(db, 'statsReports'), {
      timestamp: serverTimestamp(),
      reportData: this.workerStats,
      totalQuantity: Object.values(this.workerStats).reduce((a, b) => a + b, 0),
      totalWorkers: Object.keys(this.workerStats).length,
      month: this.selectedMonth // ✅ اضافه شد
    })
    alert('📦 گزارش با موفقیت ذخیره شد.')
  } catch (error) {
    console.error('خطا در ذخیره گزارش:', error)
    alert('❌ ذخیره گزارش با مشکل مواجه شد.')
  }
},
    goToHistory() {
      this.$router.push('/stats-history')
    }
  }
}

</script>





