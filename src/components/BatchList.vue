<template><AppLayout>
<router-link
  to="/dashboard"
  class="inline-block mb-4 bg-gray-200 hover:bg-gray-300 text-black px-4 py-2 rounded"
>
  🔙 بازگشت به داشبورد
</router-link>
<router-link
  to="/create-batch"
  class="inline-block mb-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
>
  ➕ ثبت دسته جدید
</router-link>

  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">📋 لیست دسته‌ها</h2>

    <!-- فیلتر بر اساس کارگر -->
    <div class="mb-4">
      <label class="block mb-1">🔍 فیلتر براساس کارگر:</label>
      <select v-model="selectedWorker" class="border p-2 rounded w-full">
        <option value="">نمایش همه</option>
        <option v-for="worker in uniqueWorkers" :key="worker" :value="worker">{{ worker }}</option>
      </select>

      <button
        v-if="selectedWorker"
        @click="selectedWorker = ''"
        class="ml-4 px-3 py-1 bg-gray-300 rounded text-sm"
      >
        🔁 نمایش همه
      </button>
    </div>

    <div class="mb-4">
  <label class="block mb-1">🧵 فیلتر بر اساس نوع پارچه:</label>
  <select v-model="selectedFabricType" class="border p-2 rounded w-full">
    <option value="">نمایش همه</option>
    <option v-for="type in uniqueFabricTypes" :key="type" :value="type">{{ type }}</option>
  </select>
</div>

<p v-if="noTodayResults" class="text-center text-red-600 font-bold my-4">
  ⚠️ هیچ دسته‌ای برای امروز ثبت نشده است.
</p>

    <div class="mb-4">
  <label class="block mb-1">🔎 جستجو در نام کارگر یا قطعه:</label>
  <input
    v-model="searchQuery"
    type="text"
    placeholder="مثلاً زهرا یا آستین"
    class="border p-2 rounded w-full"
  />
</div>

    <div class="mb-4">
<label class="flex items-center gap-2 mt-2 text-sm">
  <input type="checkbox" v-model="onlyWarnings" />
  فقط دسته‌های دارای هشدار (⚠️) نمایش داده شود
</label>

<div class="mb-4">
  <label class="block mb-1">🧑‍💼 فیلتر بر اساس ثبت‌کننده:</label>
  <select v-model="selectedCreator" class="border p-2 rounded w-full">
    <option value="">همه</option>
    <option v-for="creator in uniqueCreators" :key="creator" :value="creator">{{ creator }}</option>
  </select>
</div>


<!-- ✅ نمودار افت عملکرد -->
<div class="mt-6">
  <h3 class="font-bold text-lg mb-2">📉 نمودار افت عملکرد خیاط‌ها</h3>
  <DropChart :batches="filteredBatches" />
</div>

</div>

    <!-- ✅ فیلتر بر اساس تاریخ -->
<div class="mb-4 flex flex-wrap gap-4 items-end">
  <div>
    <label class="block mb-1">📅 از تاریخ:</label>
    <date-picker
      v-model="startDate"
      format="YYYY/MM/DD"
      display-format="jYYYY/jMM/jDD"
      placeholder="از تاریخ"
      class="border rounded px-3 py-2 w-full"
    />
  </div>

  <div>
    <label class="block mb-1">📅 تا تاریخ:</label>
    <date-picker
      v-model="endDate"
      format="YYYY/MM/DD"
      display-format="jYYYY/jMM/jDD"
      placeholder="تا تاریخ"
      class="border rounded px-3 py-2 w-full"
    />
  </div>
      <button
        v-if="startDate || endDate"
        @click="startDate = endDate = ''"
        class="px-3 py-1 bg-gray-300 rounded text-sm"
      >
        🔁 حذف فیلتر تاریخ
      </button>
    </div>

    <div class="mb-4 text-right">
      <button
        @click="goToStats"
        class="bg-blue-500 text-white px-4 py-2 rounded mr-2"
      >
        📊 نمایش آمار عملکرد
      </button>
    </div>

    <!-- دکمه چاپ -->
    <div class="mb-4 text-right">
      <button @click="printTable" class="bg-green-500 text-white px-4 py-2 rounded">
        🖨 چاپ لیست
      </button>
    </div>

    <button @click="downloadDashboardSummary" class="bg-purple-600 text-white px-4 py-2 rounded mt-6">
  📄 دانلود گزارش کلی داشبورد
</button>


    <button
  @click="exportFilteredBatchesAsPDF"
  class="bg-purple-600 text-white px-4 py-2 rounded shadow text-sm"
>
  📄 دریافت PDF از لیست فیلترشده
</button>

<button @click="goToWorkerStats" class="bg-teal-600 text-white px-4 py-2 rounded">
  📊 ارسال آمار خیاط‌ها به گزارش
</button>


    <!-- دکمه چاپ فقط هشدارها -->
<div class="mb-4 text-right">
  <button @click="printWarningsOnly" class="bg-red-500 text-white px-4 py-2 rounded">
    ⚠️ چاپ فقط دسته‌های دارای افت شدید
  </button>
</div>

<label class="flex items-center gap-2 text-sm">
  <input type="checkbox" v-model="onlyToday" />
  فقط دسته‌های امروز ({{ todayCount }})
</label>

<!-- بخش مرتب‌سازی -->
<div class="mb-4 flex items-center gap-4">
  <label class="text-sm">🔽 مرتب‌سازی بر اساس:</label>
  <select v-model="sortBy" class="border p-1 rounded text-sm">
    <option value="date">⏰ تاریخ (جدیدتر اول)</option>
    <option value="quantity">📦 تعداد (بیشتر اول)</option>
    <option value="drop">⚠️ افت شدید (بیشتر اول)</option>
  </select>
</div>

<span
  v-if="estimateDropPercent(batch.quantity) > 0"
  class="text-red-600 font-bold text-sm ml-1"
>
  ({{ estimateDropPercent(batch.quantity) }}٪ افت)
</span>

<button class="bg-red-100 text-red-800 px-4 py-2 rounded shadow text-sm font-semibold cursor-default">
  ⚠️ میانگین افت: {{ averageDropPercent }}٪
</button>

<p v-if="selectedWorker || selectedFabricType" class="text-blue-700 mb-4">
  🔍 نمایش دسته‌ها
  <span v-if="selectedWorker">برای کارگر <strong>{{ selectedWorker }}</strong></span>
  <span v-if="selectedWorker && selectedFabricType"> و </span>
  <span v-if="selectedFabricType">با پارچه <strong>{{ selectedFabricType }}</strong></span>
</p>

<!-- فیلتر بر اساس نام قطعه -->
<div class="mb-4">
  <label class="block mb-1">🧩 فیلتر براساس نام قطعه:</label>
  <select v-model="selectedPartName" class="border p-2 rounded w-full">
    <option value="">نمایش همه</option>
    <option v-for="name in uniquePartNames" :key="name" :value="name">{{ name }}</option>
  </select>

  <button
    v-if="selectedPartName"
    @click="selectedPartName = ''"
    class="ml-4 px-3 py-1 bg-gray-300 rounded text-sm"
  >
    🔁 نمایش همه
  </button>
</div>

<button class="bg-gray-100 px-4 py-2 rounded shadow text-sm font-semibold cursor-default">
  📏 مجموع متراژ مصرف‌شده: {{ totalMetersUsed }} متر
</button>


    <!-- ✅ دکمه‌های آماری -->
    <div class="mb-4 flex gap-4 flex-wrap">
      <button class="bg-gray-100 px-4 py-2 rounded shadow text-sm font-semibold cursor-default">
        📦 تعداد دسته‌ها: {{ totalBatches }}
      </button>
      <button class="bg-gray-100 px-4 py-2 rounded shadow text-sm font-semibold cursor-default">
        🧵 مجموع قطعات: {{ totalQuantity }}
      </button>
      <button class="bg-gray-100 px-4 py-2 rounded shadow text-sm font-semibold cursor-default">
        👷‍♀️ خیاط‌های یکتا: {{ uniqueWorkersCount }}
      </button>
      <button
  class="bg-red-100 text-red-800 px-4 py-2 rounded shadow text-sm font-semibold cursor-default"
  v-if="warningCount > 0"
>
  ⚠️ دسته‌های با افت شدید: {{ warningCount }}
</button>
    </div>

<table class="min-w-full bg-white rounded shadow mt-10">
  <thead class="bg-gray-100 text-right">
    <tr>
<th class="p-2">👷‍♀️ کارگر</th>
<th class="p-2">🧩 قطعه</th>
<th class="p-2">📦 تعداد</th>
<th class="p-2">🧵 نوع پارچه</th>
<th class="p-2">🧑‍💼 ثبت‌کننده</th>
<th class="p-2">📆 تاریخ ثبت</th>
<th class="p-2">⏰ زمان</th>
<th class="p-2">عملیات</th>
    </tr>
  </thead>
  <tbody>
    <tr v-for="(data, name) in workerStats" :key="name" class="border-t">
      <td class="p-2">{{ name }}</td>
      <td class="p-2">{{ data.count }}</td>
      <td class="p-2">{{ data.quantity }}</td>
    </tr>
  </tbody>
</table>

<button @click="exportWorkerStatsPDF" class="bg-cyan-600 text-white px-4 py-2 rounded mt-4">
  📄 خروجی PDF آمار خیاط‌ها
</button>


    <p v-if="selectedWorker" class="mb-2 text-right text-blue-700">
      🔍 نمایش دسته‌های مربوط به کارگر: <strong>{{ selectedWorker }}</strong>
    </p>

         <p v-if="hasAnyWarning" class="text-red-600 font-bold mb-4">
  ⚠️ برخی دسته‌ها افت شدید دارند.
</p>

<Bar :data="dailyBatchStats" :options="{ responsive: true }" class="my-6" />


<button
  @click="exportFilteredSummaryPDF"
  class="bg-indigo-600 text-white px-4 py-2 rounded shadow text-sm"
>
  📄 خروجی خلاصه فیلترشده
</button>

    <table class="min-w-full bg-white rounded shadow">
      <thead>
        <tr class="bg-gray-200 text-right">
          <th class="p-2">نام کارگر</th>
          <th class="p-2">نام قطعه</th>
          <th class="p-2">تعداد</th>
          <th class="p-2">⏰ تاریخ ثبت</th>
          <th class="p-2">عملیات</th>
          <th class="p-2">🧵 نوع پارچه</th>
          <th class="p-2">🧑‍💼 ثبت‌کننده</th>
          <th class="p-2">📆 ایجاد</th>
        </tr>
      </thead>
      <tbody>
<tr
  v-for="(batch, index) in filteredBatches"
  :key="index"
  class="border-t"
  :class="{ 'bg-red-50': batch.hasWarning }"
>
  <td
    class="p-2 border-r-4"
    :class="{ 'border-red-500': batch.hasWarning, 'border-transparent': !batch.hasWarning }"
  >
    {{ batch.workerName }}
  </td>
  <td class="p-2">{{ batch.partName }}</td>
  <td class="p-2">
  {{ batch.storageUsage?.fabricType || '—' }}
</td>
  <td class="p-2">
    {{ batch.quantity }}
    <span v-if="batch.hasWarning" class="text-red-600 font-bold">⚠️</span>
  </td>

          <td class="p-2">{{ batch.createdBy || '—' }}</td>
<td class="p-2">{{ formatDate(batch.createdAt) }}</td>

          <td class="p-2">{{ formatDate(batch.timestamp) }}</td>
          <td class="p-2 flex gap-2">
            <button @click="editBatch(batch)" class="px-2 py-1 bg-yellow-400 rounded text-sm">✏️ ویرایش</button>
          </td>
          <td class="p-2">
  {{ batch.quantity }}
  <span v-if="estimateDropPercent(batch.quantity) >= 30" class="text-red-600 font-bold ml-2">⚠️</span>
</td>

        </tr>
        <tr v-if="filteredBatches.length === 0">
          <td colspan="5" class="text-center p-4 text-gray-500">موردی برای نمایش وجود ندارد.</td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="bg-gray-100 font-bold">
          <td class="p-2 text-right" :colspan="5">مجموع تعداد:</td>
          <td class="p-2 text-right" colspan="3">مجموع تعداد:</td>
          <td class="p-2">{{ totalQuantity }}</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
  </AppLayout>
</template>




<script>
// eslint-disable-next-line no-unused-vars
//import { collection, onSnapshot, deleteDoc, doc, query, orderBy, addDoc } from 'firebase/firestore'
import { getStorage, ref as storageRef, uploadBytes } from 'firebase/storage'
//import { db } from '../firebase'
import AppLayout from '@/components/AppLayout.vue'
import DropChart from '@/components/DropChart.vue'
import html2pdf from 'html2pdf.js'
import jsPDF from 'jspdf'
import axios from 'axios'
import { vazirmatnFontBase64 } from '@/utils/vazirmatn'
// eslint-disable-next-line no-unused-vars
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title, Tooltip, Legend,
  BarElement, CategoryScale, LinearScale
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  components: { AppLayout, DropChart },
  name: 'BatchList',
data() {
  return {
    batches: [],
    selectedWorker: '',
    startDate: '',
    endDate: '',
    onlyWarnings: false,
    selectedPartName: '',
    selectedFabricType: '',
    searchQuery: '',
    loading: false,
    error: null,
    selectedCreator: '',
    sortBy: 'date'
  }
}
,
mounted() {
  this.fetchBatchesFromPHP()
},
async deleteBatch(id) {
  if (!confirm('آیا از حذف این دسته مطمئن هستید؟')) return

  try {
    const res = await fetch('https://app.paryamezon.ir/api/delete-batch.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })

    const result = await res.json()
    if (result.success) {
      alert('✅ دسته با موفقیت حذف شد.')
      this.fetchBatches()
    } else {
      alert('❌ خطا: ' + result.message)
    }
  } catch (err) {
    alert('⚠️ خطا در ارتباط با سرور')
    console.error(err)
  }
}
,

computed: {
  workerStats() {
  const stats = {}
  this.filteredBatches.forEach(b => {
    if (!stats[b.workerName]) {
      stats[b.workerName] = { quantity: 0, count: 0 }
    }
    stats[b.workerName].quantity += b.quantity || 0
    stats[b.workerName].count += 1
  })
  return stats
}
,
  uniqueCreators() {
  const list = this.batches.map(b => b.createdBy).filter(Boolean)
  return [...new Set(list)]
}
,
  averageDropPercent() {
  const relevant = this.filteredBatches.filter(b => b.quantity)
  if (!relevant.length) return 0

  const sum = relevant.reduce((total, b) => total + this.estimateDropPercent(b.quantity), 0)
  return Math.round(sum / relevant.length)
}
,
  uniqueFabricTypes() {
    const types = this.batches.map(b => b.storageUsage?.fabricType || '')
    return [...new Set(types.filter(t => t))]
  },
  totalMetersUsed() {
    return this.filteredBatches.reduce((sum, batch) => {
      return sum + (batch.storageUsage?.metersUsed || 0)
    }, 0)
  },
  filteredBatches() {
    let result = this.batches.filter(b => {
      const matchesWorker = !this.selectedWorker || b.workerName === this.selectedWorker

      if (!b.timestamp?.seconds) return false
      const batchDate = new Date(b.timestamp.seconds * 1000)

      const matchesStartDate = !this.startDate || new Date(this.startDate) <= batchDate
      const matchesEndDate = !this.endDate || new Date(this.endDate) >= batchDate
const matchesCreator = !this.selectedCreator || b.createdBy === this.selectedCreator;
const matchesWarning = !this.onlyWarnings || b.hasWarning;
      const matchesPart = !this.selectedPartName || b.partName === this.selectedPartName
      const matchesSearch = !this.searchQuery ||
        b.workerName.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        b.partName.toLowerCase().includes(this.searchQuery.toLowerCase())
      const matchesFabric = !this.selectedFabricType || b.storageUsage?.fabricType === this.selectedFabricType

      return matchesWorker && matchesStartDate && matchesEndDate && matchesWarning && matchesPart && matchesSearch && matchesFabric && matchesCreator
    })

    // ✨ اعمال مرتب‌سازی
    if (this.sortBy === 'quantity') {
      result.sort((a, b) => b.quantity - a.quantity)
    } else if (this.sortBy === 'drop') {
      result.sort((a, b) => b.dropPercent - a.dropPercent)
    } else {
      result.sort((a, b) => b.timestamp.seconds - a.timestamp.seconds)
    }

    return result
  },
  noTodayResults() {
    const today = new Date()
    today.setHours(0, 0, 0, 0)

    return this.onlyToday && this.filteredBatches.every(b => {
      const batchDate = new Date(b.timestamp.seconds * 1000)
      return batchDate < today
    })
  },
  todayCount() {
    const today = new Date()
    today.setHours(0, 0, 0, 0)

    return this.batches.filter(b => {
      const d = new Date(b.timestamp?.seconds * 1000 || 0)
      return d >= today
    }).length
  },
dailyBatchStats() {
  const stats = {}

  for (let i = 6; i >= 0; i--) {
    const d = new Date()
    d.setDate(d.getDate() - i)
    const key = d.toLocaleDateString('fa-IR')
    stats[key] = 0
  }

  this.batches.forEach(b => {
    const date = new Date(b.timestamp?.seconds * 1000 || 0)
    const key = date.toLocaleDateString('fa-IR')
    if (stats[key] !== undefined) stats[key]++
  })

  return {
    labels: Object.keys(stats),
    datasets: [{
      label: 'تعداد دسته‌ها',
      data: Object.values(stats),
      backgroundColor: '#60a5fa'
    }]
  }
},

  uniquePartNames() {
    const names = this.batches.map(b => b.partName)
    return [...new Set(names)]
  },
  warningCount() {
    return this.filteredBatches.filter(b => b.hasWarning).length
  },
  hasAnyWarning() {
    return this.filteredBatches.some(batch => batch.hasWarning)
  },
  uniqueWorkers() {
    const names = this.batches.map(b => b.workerName)
    return [...new Set(names)]
  },
  totalQuantity() {
    return this.filteredBatches.reduce((sum, batch) => sum + batch.quantity, 0)
  },
  uniqueWorkersCount() {
    const names = this.filteredBatches.map(b => b.workerName)
    return new Set(names).size
  },
  totalBatches() {
    return this.filteredBatches.length
  }
},


  exportWorkerStatsPDF() {
  const doc = new jsPDF()
  doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
  doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
  doc.setFont('Vazirmatn')
  doc.setFontSize(14)

  doc.text('📊 آمار خیاط‌ها', 10, 20)

  let y = 35
  for (const [name, data] of Object.entries(this.workerStats)) {
    doc.text(`${name}: ${data.count} دسته، ${data.quantity} قطعه`, 10, y)
    y += 10
    if (y > 280) {
      doc.addPage()
      y = 20
    }
  }

  doc.save('worker-stats.pdf')
}
,
editBatch(batch) {
  localStorage.setItem('editBatch', JSON.stringify(batch))
  this.$router.push('/create-batch')
}
,
  estimateDropPercent(quantity) {
    if (quantity >= 100) return 0
    if (quantity >= 80) return 20
    if (quantity >= 60) return 30
    return 50
  },
exportFilteredSummaryPDF() {
  const doc = new jsPDF()

  doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
  doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
  doc.setFont('Vazirmatn')
  doc.setFontSize(14)

  doc.text('📄 خلاصه دسته‌های فیلترشده', 10, 20)

  const total = this.filteredBatches.length
  const meters = this.filteredBatches.reduce(
    (sum, b) => sum + (b.storageUsage?.metersUsed || 0),
    0
  )

  doc.setFontSize(12)
  doc.text(`📦 تعداد دسته‌ها: ${total}`, 10, 30)
  doc.text(`📏 مجموع متراژ مصرفی: ${meters.toFixed(2)} متر`, 10, 40)

  let y = 55
  this.filteredBatches.forEach((batch, index) => {
    const line = `${index + 1}. ${batch.workerName} | ${batch.partName} | ${batch.storageUsage?.fabricType || '—'} | ${batch.quantity} عدد`
    doc.text(line, 10, y)
    y += 10

    // جلوگیری از خروج از صفحه PDF
    if (y > 280) {
      doc.addPage()
      y = 20
    }
  })

  doc.save('filtered-summary.pdf')
}
,
  exportFilteredBatchesAsPDF() {
  const table = this.$el.querySelector('table')
  const opt = {
    margin:       0.5,
    filename:     `filtered-batches-${new Date().toISOString().slice(0,10)}.pdf`,
    image:        { type: 'jpeg', quality: 0.98 },
    html2canvas:  { scale: 2 },
    jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
  }
  html2pdf().set(opt).from(table).save()
}
,
  formatDate(timestamp) {
    if (!timestamp) return ''
    const date = new Date(timestamp.seconds * 1000)
    return date.toLocaleDateString('fa-IR') + ' - ' + date.toLocaleTimeString('fa-IR')
  },
  printTable() {
    const printContents = document.querySelector('table').outerHTML
    const printWindow = window.open('', '', 'width=800,height=600')
    printWindow.document.write(`
      <html dir="rtl">
        <head>
          <title>چاپ لیست دسته‌ها</title>
          <style>
            body { font-family: sans-serif; direction: rtl; padding: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
            th { background-color: #f0f0f0; }
          </style>
        </head>
        <body>
          ${printContents}
        </body>
      </html>
    `)
    printWindow.document.close()
    printWindow.print()
  },
  printWarningsOnly() {
  const warnings = this.filteredBatches.filter(b => b.hasWarning)

  if (warnings.length === 0) {
    alert('هیچ دسته دارای هشدار وجود ندارد.')
    return
  }

  let tableHTML = `
    <table>
      <thead>
        <tr>
          <th>نام کارگر</th>
          <th>نام قطعه</th>
          <th>تعداد</th>
          <th>⏰ تاریخ ثبت</th>
        </tr>
      </thead>
      <tbody>
        ${warnings.map(batch => `
          <tr>
            <td>${batch.workerName}</td>
            <td>${batch.partName}</td>
            <td>${batch.quantity} ⚠️</td>
            <td>${this.formatDate(batch.timestamp)}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `

  const printWindow = window.open('', '', 'width=800,height=600')
  printWindow.document.write(`
    <html dir="rtl">
      <head>
        <title>چاپ هشدارها</title>
        <style>
          body { font-family: sans-serif; direction: rtl; padding: 20px; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #ccc; padding: 8px; text-align: right; }
          th { background-color: #fdd; }
        </style>
      </head>
      <body>
        <h2>⚠️ لیست دسته‌های دارای افت شدید</h2>
        ${tableHTML}
      </body>
    </html>
  `)
  printWindow.document.close()
  printWindow.print()
}
,
async mounted() {
  await this.fetchBatches()
},
methods: {
  goToWorkerStats() {
  localStorage.setItem('workerStatsData', JSON.stringify(this.workerStats))
  this.$router.push('/workers-stats')
}
,
  async fetchBatches() {
    try {
      const res = await fetch('https://app.paryamezon.ir/api/get-batches.php')
      const result = await res.json()
      if (result.success) {
        this.batches = result.batches
      } else {
        alert('❌ خطا در دریافت دسته‌ها: ' + result.message)
      }
    } catch (e) {
      alert('⚠️ ارتباط با سرور ممکن نیست')
    }
  },
async downloadDashboardSummary() {
  const doc = new jsPDF()
  doc.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
  doc.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
  doc.setFont('Vazirmatn')
  doc.setFontSize(14)

  doc.text('📊 گزارش کلی کارخانه', 10, 20)
  doc.setFontSize(12)
  doc.text(`📦 تعداد دسته‌ها: ${this.totalBatches}`, 10, 35)
  doc.text(`🧵 مجموع قطعات: ${this.totalQuantity}`, 10, 45)
  doc.text(`👷‍♀️ خیاط‌های یکتا: ${this.uniqueWorkersCount}`, 10, 55)
  doc.text(`📏 متراژ مصرفی: ${this.totalMetersUsed.toFixed(2)} متر`, 10, 65)
  doc.text(`⚠️ هشدارها: ${this.warningCount}`, 10, 75)
  doc.text(`📉 میانگین افت: ${this.averageDropPercent}%`, 10, 85)

  doc.save('dashboard-summary.pdf')

  // ⬇️ آپلود در Firebase Storage
  const pdfBlob = doc.output('blob')
  const storage = getStorage()
  const filename = `pdf-archive/${Date.now()}.pdf`
  const fileRef = storageRef(storage, filename)

  try {
    await uploadBytes(fileRef, pdfBlob)
    console.log('✅ فایل PDF در Firebase Storage ذخیره شد.')
  } catch (error) {
    console.error('❌ خطا در آپلود فایل PDF:', error)
  }
}
,

  goToStats() {
    localStorage.setItem('filteredStats', JSON.stringify(this.filteredBatches))
    this.$router.push('/stats')
  }
}

}


</script>

<style scoped>
.stat-btn {
  background: #f0f0f0;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  font-weight: bold;
  cursor: default;
}
</style>






