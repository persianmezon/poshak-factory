<template>
  <router-link
    to="/dashboard"
    class="inline-block mb-4 bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded"
  >
    🔙 بازگشت به داشبورد
  </router-link>

  <!-- 🔍 فیلتر بر اساس نام خیاط -->
  <div class="mb-4 relative">
    <label class="block mb-1">🔍 جست‌وجو در گزارش‌ها (بر اساس نام خیاط):</label>
    <input
      v-model="searchQuery"
      @keyup.enter="$refs.searchInput.blur()"
      ref="searchInput"
      type="text"
      placeholder="مثلاً نرگس"
      class="border p-2 rounded w-full pr-10"
    />

    <label class="inline-flex items-center gap-2 mb-4">
  <input type="checkbox" v-model="showOnlyWarnings" />
  فقط گزارش‌های دارای افت بالا (⚠️)
</label>

    <button
      @click="showForm = true"
      class="mb-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
    >
      ➕ افزودن گزارش جدید
    </button>

    <button
      @click="showManualForm = true"
      class="bg-green-500 text-white px-4 py-2 rounded mb-4"
    >
      ➕ افزودن دستی گزارش خیاط‌ها
    </button>

    <button @click="downloadMonthlySummary" class="button">
      📄 دریافت PDF ماهانه
    </button>

    <button
      v-if="searchQuery"
      @click="searchQuery = ''"
      class="absolute top-8 left-3 text-gray-500 hover:text-black"
    >
      ✖️
    </button>
  </div>

  <p v-if="successMessage" class="text-green-600 font-medium mb-2">
  {{ successMessage }}
</p>

  <!-- فرم پاپ‌آپ افزودن دستی -->
  <div v-if="showManualForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl shadow w-96">
      <h2 class="text-lg font-bold mb-4">📝 افزودن گزارش خیاط</h2>
      <label class="block mb-2">ماه:</label>
      <select v-model="manualReportForm.month" class="w-full border rounded px-3 py-1 mb-3">
        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
      </select>

      <label class="block mb-2">نام و تعداد (مثلاً نرگس:45):</label>
      <textarea v-model="manualReportForm.rawInput" rows="4" class="w-full border rounded px-2 py-1"></textarea>

      <div class="flex justify-end gap-2 mt-4">
        <button @click="showManualForm = false" class="bg-gray-300 px-4 py-2 rounded">لغو</button>
        <button @click="submitManualReport" class="bg-blue-600 text-white px-4 py-2 rounded">ثبت</button>
      </div>
    </div>
  </div>

  <div class="p-6 bg-white rounded shadow mt-6">
    <h2 class="text-xl font-bold mb-4">🕓 تاریخچه گزارش‌ها</h2>

    <!-- 🔍 خلاصه آماری و مرتب‌سازی -->
    <div class="mb-4 flex flex-wrap gap-4" v-if="activeTab === 'stats'">
      <div class="bg-gray-100 px-4 py-2 rounded shadow text-sm font-semibold">
        🧵 مجموع کل قطعات: {{ totalQuantity }}
      </div>
      <div class="bg-gray-100 px-4 py-2 rounded shadow text-sm font-semibold">
        👷‍♀️ مجموع خیاط‌ها: {{ totalWorkers }}
      </div>

      <Bar :data="chartDropData" :options="{ responsive: true }" class="mt-6" />

      <select v-model="selectedMonth" class="border px-3 py-2 rounded mb-4">
        <option value="">همه ماه‌ها</option>
        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
      </select>

      <select v-model="dropFilter" class="border px-3 py-2 rounded mb-4">
  <option value="">همه سطوح افت</option>
  <option value="20">افت ≥ 20٪</option>
  <option value="30">افت ≥ 30٪</option>
  <option value="50">افت ≥ 50٪</option>
</select>

      <select v-model="sortOrder" class="ml-auto px-3 py-1 border rounded text-sm">
        <option value="desc">📅 جدیدترین اول</option>
        <option value="asc">📅 قدیمی‌ترین اول</option>
      </select>
    </div>

    <div v-if="activeTab === 'stats'" class="mb-4 text-sm font-medium text-gray-700 flex gap-4">
  <div>📄 تعداد گزارش‌ها: {{ overallStats.totalReports }}</div>
  <div>🧵 مجموع قطعات: {{ overallStats.totalQty }}</div>
  <div>⚠️ میانگین افت: {{ overallStats.avgDrop }}٪</div>
</div>


    <!-- ✅ جدول گزارش خیاط‌ها -->
    <table
      v-if="activeTab === 'stats'"
      class="min-w-full bg-white rounded shadow"
    >
      <thead class="bg-gray-200 text-right">
        <tr>
          <th class="p-2">📅 تاریخ</th>
          <th class="p-2">📆 ماه</th>
          <th class="p-2">🧵 قطعات</th>
          <th class="p-2">👷‍♀️ خیاط‌ها</th>
          <th class="p-2">عملیات</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="report in filteredReports" :key="report.id">
          <tr class="border-t">
            <td class="p-2">{{ formatDate(report.timestamp) }}</td>
            <td class="p-2">{{ getMonthLabel(report.month) }}</td>
            <td class="p-2">{{ report.totalQuantity }}</td>
            <td class="p-2">{{ report.totalWorkers }}</td>
            <td class="p-2">
              <button
                @click="downloadReportAsPDF(report)"
                class="px-2 py-1 bg-blue-600 text-white rounded text-sm ml-2"
              >📥 PDF</button>
              <button
                @click="deleteReport(report.id)"
                class="px-2 py-1 bg-red-600 text-white rounded text-sm"
              >🗑 حذف</button>
            </td>
          </tr>

          <td class="p-2">
  {{ report.totalWorkers }}
  <span v-if="report.totalQuantity < 60" class="text-red-600 font-bold">⚠️</span>
</td>

          <!-- 📊 ردیف دوم: نمودار -->
          <tr>
            <td colspan="5">
              <ReportBarChart :reportData="report.reportData" class="my-4" />
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <!-- ✅ لیست خلاصه گزارش‌های ماهانه -->
    <div v-else-if="activeTab === 'monthly'" class="grid gap-4">
      <div
        v-for="report in monthlyReports"
        :key="report.id"
        class="border p-4 rounded shadow"
      ><button
  @click="printMonthlyReport(report)"
  class="text-blue-600 text-sm hover:underline ml-2"
>
  🖨 چاپ
</button>

      <p class="text-sm text-gray-700 mb-2">
  📄 تعداد گزارش‌های ثبت‌شده: {{ monthlyReportCount }}
</p>
<button
  @click="startEdit(report)"
  class="text-yellow-600 text-sm hover:underline ml-4"
>
  ✏️ ویرایش
</button>
<button
  @click="deleteMonthlyReport(report.id)"
  class="text-red-600 text-sm hover:underline ml-2"
>
  🗑 حذف
</button>

        <h3 class="text-lg font-semibold mb-1">{{ report.month }}</h3>
        <p>📦 مجموع دسته‌ها: {{ report.totalBatches }}</p>
        <p v-if="report.updatedAt" class="text-sm text-gray-500 mt-1">
  ⏰ آخرین ویرایش: {{ formatDate(report.updatedAt) }}
</p>
<p>🧵 مجموع قطعات: {{ report.totalPieces }}</p>

<p v-if="report.description" class="text-sm text-gray-600 mt-1">
  📝 {{ report.description }}
</p>

<div v-if="report.dropRatePercent > 20" class="text-red-600 font-semibold">
  <p v-if="loading" class="text-center text-gray-500 mb-2">
    ⏳ در حال بارگذاری...
  </p>
  ⚠️ افت زیاد: {{ report.dropRatePercent }}٪
</div>

        <p
  v-if="report.dropRatePercent >= 30"
  class="text-red-600 font-semibold mt-2"
>
  ⚠️ افت شدید: {{ report.dropRatePercent }}٪
</p>
<label class="block mb-2">📝 توضیحات (اختیاری):</label>
<textarea v-model="form.description" rows="2" class="w-full border rounded px-2 py-1 mb-4"></textarea>

      </div>
    </div>
  </div>
</template>




<script>
import { vazirmatnFontBase64 } from '../utils/vazirmatn'
import { collection, onSnapshot, deleteDoc, doc, query, orderBy, addDoc, getDocs, serverTimestamp,  updateDoc } from 'firebase/firestore'
import { db } from '../firebase'
import jsPDF from 'jspdf'
import ReportBarChart from '@/components/ReportBarChart.vue'
import { calculateTotalQuantity, calculateUniqueWorkers } from '@/utils/statsUtils'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'StatsHistory',
  data() {
    return {
      reports: [],
      monthlyReports: [],
      form: {
        month: '',
        totalBatches: 0,
        description: '',
        totalPieces: 0,
        activeTab: 'stats',
        editingMonthlyId: null,
        successMessage: '',
        dropRatePercent: 0
      },
      manualReportForm: {
  month: '',
  dropFilter: '',
  reportData: {}
},
showManualForm: false,
showOnlyWarnings: false,
      loading: false,
      showForm: false,
      searchQuery: '',
      sortOrder: 'desc',
      selectedMonth: '',
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
  components: {
  ReportBarChart,
  Bar
}
,
  computed: {
    monthlyReportCount() {
  return this.monthlyReports?.length || 0
}
,
    chartDropData() {
  return {
    labels: this.filteredReports.map(r => this.formatDate(r.timestamp)),
    datasets: [{
      label: 'درصد افت',
      data: this.filteredReports.map(r => r.dropRatePercent || 0),
      backgroundColor: '#f87171'
    }]
  }
}
,
    overallStats() {
  const totalReports = this.filteredReports.length
  const totalQty = this.totalQuantity
  const avgDrop = totalReports
    ? Math.round(this.filteredReports.reduce((sum, r) => sum + (r.dropRatePercent || 0), 0) / totalReports)
    : 0
  return { totalReports, totalQty, avgDrop }
}
,
    sortedReports() {
      const sorted = [...this.reports]
      sorted.sort((a, b) => {
        if (!a.timestamp || !b.timestamp) return 0
        const t1 = a.timestamp.seconds
        const t2 = b.timestamp.seconds
        return this.sortOrder === 'asc' ? t1 - t2 : t2 - t1
      })
      return sorted
    },
filteredReports() {
  const q = this.searchQuery.toLowerCase()
  return this.sortedReports.filter(report => {
    const names = Object.keys(report.reportData || {}).join(' ').toLowerCase()
    const isNameMatch = names.includes(q)

    const isMonthMatch =
      !this.selectedMonth || (report.month && report.month === this.selectedMonth)

    const isDropMatch =
      !this.dropFilter || (report.dropRatePercent >= parseInt(this.dropFilter))

    return isNameMatch && isMonthMatch && isDropMatch
  })
}
,
    totalQuantity() {
      return calculateTotalQuantity(this.reports)
    },
    totalWorkers() {
      return calculateUniqueWorkers(this.reports)
    },
    selectedMonthLabel() {
      const found = this.months.find(m => m.value === this.selectedMonth)
      return found ? found.label : '—'
    },
    filteredData() {
      const summary = {}
      this.filteredReports.forEach(report => {
        for (const [name, qty] of Object.entries(report.reportData || {})) {
          if (!summary[name]) summary[name] = 0
          summary[name] += qty
        }
      })
      return summary
    }
  },
  created() {
    const role = localStorage.getItem('userRole')
    if (role !== 'supervisor' && role !== 'admin') {
      alert('⛔ دسترسی غیرمجاز')
      this.$router.push('/login')
    }
    document.title = '📊 تاریخچه آمار ماهانه | پوشاک فکتوری'
  },
  mounted: async function () {
    await this.loadMonthlyReports()
    await this.loadReports()
    const q = query(collection(db, 'statsReports'), orderBy('timestamp', 'desc'))
    onSnapshot(q, snapshot => {
      this.reports = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }))
    })
  },
  methods: {
    async deleteMonthlyReport(id) {
  if (!confirm('آیا از حذف این گزارش مطمئن هستید؟')) return
  try {
    await deleteDoc(doc(db, 'monthlyReports', id))
    await this.loadReports()
  } catch (error) {
    alert('❌ حذف با خطا مواجه شد.')
    console.error('خطا در حذف گزارش ماهانه:', error)
  }
}
,
printMonthlyReport(report) {
  const win = window.open('', '', 'width=800,height=600')
  win.document.write(`
    <html dir="rtl">
      <head>
        <title>چاپ گزارش ماهانه</title>
        <style>
          body { font-family: sans-serif; padding: 20px; direction: rtl; }
          h2 { color: #1e40af; }
          p { margin: 6px 0; }
        </style>
      </head>
      <body>
        <h2>📆 گزارش ماه: ${report.month}</h2>
        <p>📦 تعداد دسته‌ها: ${report.totalBatches}</p>
        <p>🧵 مجموع قطعات: ${report.totalPieces}</p>
        <p>⚠️ درصد افت: ${report.dropRatePercent}%</p>
        ${report.description ? `<p>📝 توضیحات: ${report.description}</p>` : ''}
      </body>
    </html>
  `)
  win.document.close()
  win.print()
}
,
    startEdit(report) {
  this.form = {
    month: report.month,
    totalBatches: report.totalBatches,
    totalPieces: report.totalPieces,
    dropRatePercent: report.dropRatePercent
  }
  this.editingMonthlyId = report.id
  this.showForm = true
}
,
async loadMonthlyReports() {
  this.loading = true
  const snap = await getDocs(collection(db, 'monthlyReports'))
  this.monthlyReports = snap.docs.map(doc => ({
    id: doc.id,
    ...doc.data()
  }))
  this.loading = false
}
,
async loadReports() {
  this.loading = true
  const snapshot = await getDocs(query(collection(db, 'monthlyReports'), orderBy('createdAt', 'desc')))
  this.reports = snapshot.docs.map(doc => ({
    id: doc.id,
    ...doc.data()
  }))
  this.loading = false
}
,
    async submitManualReport() {
  const lines = (this.manualReportForm.rawInput || '').split('\n')
  const reportData = {}

  lines.forEach(line => {
    const [name, qty] = line.split(':')
    if (name && qty) reportData[name.trim()] = parseInt(qty.trim())
  })

  const totalQuantity = Object.values(reportData).reduce((sum, n) => sum + n, 0)
  const totalWorkers = Object.keys(reportData).length
  const now = new Date()

  let dropRatePercent = 0
if (totalQuantity < 60) dropRatePercent = 50
else if (totalQuantity < 80) dropRatePercent = 30
else if (totalQuantity < 100) dropRatePercent = 20

  await addDoc(collection(db, 'statsReports'), {
    timestamp: now,
    month: this.manualReportForm.month,
    reportData,
    totalQuantity,
    totalWorkers,
    dropRatePercent
  })

  this.manualReportForm = { month: '', rawInput: '' }
  this.showManualForm = false
}
,
async submitReport() {
  if (!this.form.month) return alert('ماه را وارد کنید!')    
  serverTimestamp()

  const exists = this.reports.some(r => r.month === this.form.month && r.id !== this.editingMonthlyId)
if (exists) {
  alert('⚠️ برای این ماه قبلاً گزارشی ثبت شده است.')
  return
}


  // ⬇️ محاسبه خودکار درصد افت اگر خالی باشد
  if (!this.form.dropRatePercent) {
    const q = this.form.totalPieces
    if (q >= 100) this.form.dropRatePercent = 0
    else if (q >= 80) this.form.dropRatePercent = 20
    else if (q >= 60) this.form.dropRatePercent = 30
    else this.form.dropRatePercent = 50
  }

  const wasEdit = !!this.editingMonthlyId

  if (this.editingMonthlyId) {
    const ref = doc(db, 'monthlyReports', this.editingMonthlyId)
    await updateDoc(ref, {
      ...this.form,
      updatedAt: serverTimestamp()
    })
  } else {
    await addDoc(collection(db, 'monthlyReports'), {
      ...this.form,
      createdAt: serverTimestamp()
    })
  }

  this.showForm = false
  this.editingMonthlyId = null
  this.form = {
    month: '',
    totalBatches: 0,
    totalPieces: 0,
    dropRatePercent: 0
  }

  await this.loadReports()

  this.successMessage = wasEdit ? '✅ ویرایش با موفقیت انجام شد.' : '✅ گزارش جدید ثبت شد.'
  setTimeout(() => {
    this.successMessage = ''
  }, 3000)


await addDoc(... { ...this.form })
await updateDoc(... { ...this.form })




}

,
    getMonthLabel(value) {
      const found = this.months.find(m => m.value === value)
      return found ? found.label : '—'
    },
    async deleteReport(id) {
      if (!confirm('آیا از حذف این گزارش مطمئن هستید؟')) return
      try {
        await deleteDoc(doc(db, 'statsReports', id))
        alert('🗑 گزارش با موفقیت حذف شد.')
      } catch (error) {
        console.error('خطا در حذف گزارش:', error)
        alert('❌ حذف گزارش با مشکل مواجه شد.')
      }
    },
    formatDate(timestamp) {
      if (!timestamp) return ''
      const date = new Date(timestamp.seconds * 1000)
      return date.toLocaleDateString('fa-IR') + ' - ' + date.toLocaleTimeString('fa-IR')
    },
    downloadReportAsPDF(report) {
      const doc = new jsPDF()
      doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
      doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
      doc.setFont('Vazirmatn')
      doc.setFontSize(14)
      doc.text('گزارش عملکرد خیاط‌ها', 10, 20)
      doc.setFontSize(12)
      doc.text(`تاریخ: ${this.formatDate(report.timestamp)}`, 10, 35)
      doc.text(`تعداد کل قطعات: ${report.totalQuantity}`, 10, 45)
      doc.text(`تعداد خیاط‌ها: ${report.totalWorkers}`, 10, 55)
      doc.text('جزئیات:', 10, 70)
      let y = 80
      for (const [name, quantity] of Object.entries(report.reportData)) {
        doc.text(`${name}: ${quantity} قطعه`, 15, y)
        y += 10
      }
      doc.save(`report-${this.formatDate(report.timestamp)}.pdf`)
    },
    downloadMonthlyPDF(report) {
      const doc = new jsPDF()
      doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
      doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
      doc.setFont('Vazirmatn')
      doc.setFontSize(14)
      doc.text(`📆 گزارش ماهانه: ${report.month}`, 10, 20)
      doc.setFontSize(12)
      doc.text(`📦 مجموع دسته‌ها: ${report.totalBatches}`, 10, 35)
      doc.text(`🧵 مجموع قطعات: ${report.totalPieces}`, 10, 45)
      doc.text(`⚠️ درصد افت: ${report.dropRatePercent}%`, 10, 55)
      doc.save(`monthly-report-${report.month}.pdf`)
    },
    downloadMonthlySummary() {
      const doc = new jsPDF()
      doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
      doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
      doc.setFont('Vazirmatn')
      doc.setFontSize(14)
      doc.text(`📆 گزارش ماهانه (${this.selectedMonthLabel})`, 10, 20)
      doc.text(`🧵 مجموع قطعات: ${this.totalQuantity}`, 10, 35)
      doc.text(`👷‍♀️ تعداد خیاط‌ها: ${this.totalWorkers}`, 10, 45)
      let y = 60
      for (const [name, quantity] of Object.entries(this.filteredData)) {
        doc.text(`${name}: ${quantity} قطعه`, 15, y)
        y += 10
      }
      doc.save(`گزارش-${this.selectedMonthLabel}.pdf`)
    }
  }
}
</script>



