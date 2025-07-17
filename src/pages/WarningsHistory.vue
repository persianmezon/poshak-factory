<!-- eslint-disable vue/multi-word-component-names -->
<template><AppLayout>
  <p class="text-sm text-gray-500 mb-2">
    ⚠️ هشدارهای با افت بالای {{ SEVERE_THRESHOLD }}٪ با رنگ قرمز پررنگ مشخص شده‌اند.
  </p>

  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">📉 تاریخچه هشدارهای افت عملکرد</h2>

    <div v-if="warnings.length === 0">هیچ هشداری ثبت نشده است.</div>

    <div v-for="(monthGroup, month) in groupedWarnings" :key="month" class="mb-4">
      <h3 class="font-bold text-lg text-blue-700 mb-1 flex items-center justify-between">
        <span>{{ getMonthLabel(month) }}</span>
        <button @click="deleteWarningsByMonth(month)" class="text-red-600 text-sm hover:underline">
          حذف همه هشدارهای این ماه ❌
        </button>
      </h3>
      <ul class="list-disc ml-5">
<li
  v-for="w in monthGroup"
  :key="w.workerName + w.timestamp"
  :class="[
    'px-2 py-1 rounded',
    w.dropPercent >= SEVERE_THRESHOLD
      ? 'bg-red-100 text-red-700 font-bold'
      : 'bg-gray-100 text-gray-800'
  ]"
>
  ⚠️ {{ w.workerName }} | افت {{ w.dropPercent }}٪
  <button @click="deleteWarning(w.id)" class="text-red-600 hover:underline ml-2">
    حذف ❌
  </button>
  <button
  @click="addTestWarning"
  class="mt-4 bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
>
  🔬 ثبت هشدار تستی
</button>

</li>

      </ul>
    </div>
  </div>

  <div class="bg-yellow-50 border border-yellow-300 rounded p-4 mb-4">
    <p>📦 مجموع هشدارها: <strong>{{ warningsSummary.total }}</strong></p>
    <p>📅 ماه با بیشترین هشدار: <strong>{{ warningsSummary.maxMonthLabel }}</strong></p>
    <p>🚨 تعداد هشدار در آن ماه: <strong>{{ warningsSummary.maxCount }}</strong></p>
  </div>

  <div class="bg-red-50 border border-red-300 rounded p-4 mb-4">
    <p>🚨 هشدارهای شدید (افت ≥ ۳۰٪): <strong>{{ severeStats.severe }}</strong></p>
    <p>📊 درصد نسبت به کل هشدارها: <strong>{{ severeStats.percent }}٪</strong></p>
  </div>

  <div class="mb-4">
    <input type="text" v-model="searchQuery" placeholder="🔍 جستجو بر اساس نام خیاط" class="border px-3 py-1 rounded w-64" />
  </div>

  <div class="flex items-center gap-4 mb-4">
    <label>از تاریخ: <input type="date" v-model="fromDate" class="border rounded px-2 py-1" /></label>
    <label>تا تاریخ: <input type="date" v-model="toDate" class="border rounded px-2 py-1" /></label>
    <button @click="fromDate = ''; toDate = ''" class="text-blue-600 hover:underline">پاک کردن فیلتر 🧹</button>
  </div>

  <button @click="downloadWarningsPDF" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 mb-4">📥 دانلود PDF هشدارها</button>
  <button @click="downloadWarningsExcel" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 ml-2">📥 خروجی Excel هشدارها</button>

  <button @click="printWarnings" class="bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700 ml-2">
  🖨 پرینت هشدارها
</button>

  <h2 class="text-xl font-bold mt-8 mb-2">🍩 نسبت هشدارهای شدید و معمولی</h2>
  <PieChart :chartData="pieData" style="max-width: 400px; height: 300px;" />

  <h2 class="text-xl font-bold mt-10 mb-2">📈 نمودار روند افت عملکرد خیاط‌ها</h2>
  <LineChart :chartData="dropTrendData" style="height: 300px;" />

  <h2 class="text-xl font-bold mt-10 mb-2">📉 میانگین افت عملکرد خیاط‌ها</h2>
  <ul class="list-disc ml-5">
    <li v-for="w in avgDropsPerWorker" :key="w.name">{{ w.name }} - میانگین افت: <strong>{{ w.average }}%</strong></li>
  </ul>

  <h2 class="text-xl font-bold mt-10 mb-2">👩‍🔧 خیاط‌های پرتکرار در هشدارها</h2>
  <ul class="list-disc ml-5">
    <li v-for="w in topWarnedWorkers" :key="w.name">{{ w.name }} - <strong>{{ w.count }}</strong> هشدار</li>
  </ul>

  <h2 class="text-xl font-bold mt-8 mb-2">📊 نمودار تعداد هشدارها در ماه‌ها</h2>
  <BarChart :chartData="warningsTrendData" style="height: 300px;" />
<h2 class="text-xl font-bold mt-10 mb-2">📛 نمودار تعداد هشدار برای هر خیاط</h2>
<BarChart :chartData="warningsPerWorkerChartData" style="height: 300px;" />
</AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { collection, getDocs, deleteDoc, doc, onSnapshot, addDoc} from 'firebase/firestore'
import { db } from '../firebase'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement, LineController } from 'chart.js'
import jsPDF from 'jspdf'
import AppLayout from '@/components/AppLayout.vue'
import autoTable from 'jspdf-autotable'
import * as XLSX from 'xlsx'
import '../utils/vazirmatn'


ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, LineElement, PointElement, LineController)

const warnings = ref([])
const fromDate = ref('')
const toDate = ref('')
const searchQuery = ref('')
const SEVERE_THRESHOLD = 30

const filteredWarnings = computed(() => {
  let results = warnings.value

  // فیلتر بازه زمانی
  if (fromDate.value || toDate.value) {
    results = results.filter(w => {
      const warnDate = w.timestamp?.toDate?.()
      if (!warnDate) return false

      const from = fromDate.value ? new Date(fromDate.value) : null
      const to = toDate.value ? new Date(toDate.value) : null

      return (!from || warnDate >= from) && (!to || warnDate <= to)
    })
  }

  // فیلتر نام
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.trim().toLowerCase()
    results = results.filter(w =>
      w.workerName?.toLowerCase().includes(query)
    )
  }

  // مرتب‌سازی نزولی بر اساس تاریخ
  results = results.slice().sort((a, b) => {
    const dateA = a.timestamp?.toDate?.()?.getTime?.() || 0
    const dateB = b.timestamp?.toDate?.()?.getTime?.() || 0
    return dateB - dateA
  })

  return results
})


const pieData = computed(() => {
  const severe = filteredWarnings.value.filter(w => w.dropPercent >= SEVERE_THRESHOLD).length
  const normal = filteredWarnings.value.length - severe
  return {
    labels: ['هشدارهای شدید (≥۳۰٪)', 'هشدارهای عادی (<۳۰٪)'],
    datasets: [{ data: [severe, normal], backgroundColor: ['#EF4444', '#60A5FA'] }]
  }
})

const avgDropsPerWorker = computed(() => {
  const sumMap = {}, countMap = {}
  filteredWarnings.value.forEach(w => {
    if (!sumMap[w.workerName]) { sumMap[w.workerName] = 0; countMap[w.workerName] = 0 }
    sumMap[w.workerName] += w.dropPercent
    countMap[w.workerName]++
  })
  return Object.keys(sumMap).map(name => ({ name, average: (sumMap[name] / countMap[name]).toFixed(1) }))
})

const topWarnedWorkers = computed(() => {
  const countMap = {}
  warnings.value.forEach(w => {
    if (!countMap[w.workerName]) countMap[w.workerName] = 0
    countMap[w.workerName]++
  })
  return Object.entries(countMap).sort((a,b)=>b[1]-a[1]).slice(0,5).map(([name,count])=>({name,count}))
})

const groupedWarnings = computed(() => {
  const grouped = {}
  filteredWarnings.value.forEach(w => {
    if (!grouped[w.month]) grouped[w.month] = []
    grouped[w.month].push(w)
  })
  return grouped
})

const severeStats = computed(() => {
  const total = filteredWarnings.value.length
  const severe = filteredWarnings.value.filter(w => w.dropPercent >= SEVERE_THRESHOLD).length
  const percent = total > 0 ? ((severe / total) * 100).toFixed(1) : 0
  return { total, severe, percent }
})

const warningsSummary = computed(() => {
  const total = warnings.value.length
  const monthCount = {}
  warnings.value.forEach(w => {
    if (!monthCount[w.month]) monthCount[w.month] = 0
    monthCount[w.month]++
  })
  const maxMonth = Object.entries(monthCount).sort((a,b)=>b[1]-a[1])[0] || []
  return {
    total,
    maxCount: maxMonth[1] || 0,
    maxMonthLabel: getMonthLabel(maxMonth[0]) || 'نامشخص'
  }
})

const warningsTrendData = computed(() => {
  const monthMap = {}
  warnings.value.forEach(w => {
    if (!monthMap[w.month]) monthMap[w.month] = 0
    monthMap[w.month]++
  })
  const sortedMonths = Object.keys(monthMap).sort()
  return {
    labels: sortedMonths.map(getMonthLabel),
    datasets: [{ label: 'تعداد هشدارها', backgroundColor: '#EF4444', data: sortedMonths.map(m => monthMap[m]) }]
  }
})

const dropTrendData = computed(() => {
  const monthLabels = [
    '2025-01','2025-02','2025-03','2025-04','2025-05','2025-06',
    '2025-07','2025-08','2025-09','2025-10','2025-11','2025-12'
  ]
  const map = {}
  filteredWarnings.value.forEach(w => {
    if (!map[w.workerName]) map[w.workerName] = {}
    if (!map[w.workerName][w.month]) map[w.workerName][w.month] = []
    map[w.workerName][w.month].push(w.dropPercent)
  })
  const datasets = Object.entries(map).map(([name, data]) => {
    const avg = monthLabels.map(m => {
      const arr = data[m] || []
      return arr.length === 0 ? 0 : (arr.reduce((a,b)=>a+b,0) / arr.length).toFixed(1)
    })
    return { label: name, data: avg, fill: false, borderWidth: 2, tension: 0.3 }
  })
  return { labels: monthLabels.map(getMonthLabel), datasets }
})

const getMonthLabel = (val) => {
  const map = {
    '2025-01': 'دی ۱۴۰۳','2025-02': 'بهمن ۱۴۰۳','2025-03': 'اسفند ۱۴۰۳',
    '2025-04': 'فروردین ۱۴۰۴','2025-05': 'اردیبهشت ۱۴۰۴','2025-06': 'خرداد ۱۴۰۴',
    '2025-07': 'تیر ۱۴۰۴','2025-08': 'مرداد ۱۴۰۴','2025-09': 'شهریور ۱۴۰۴',
    '2025-10': 'مهر ۱۴۰۴','2025-11': 'آبان ۱۴۰۴','2025-12': 'آذر ۱۴۰۴'
  }
  return map[val] || val
}

onMounted(async () => {
  const snapshot = await getDocs(collection(db, 'performanceWarnings'))
  warnings.value = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }))
})

const deleteWarning = async (id) => {
  if (confirm('آیا مطمئن هستید که می‌خواهید این هشدار را حذف کنید؟')) {
    try {
      await deleteDoc(doc(db, 'performanceWarnings', id))
      warnings.value = warnings.value.filter(w => w.id !== id)
      alert('✅ هشدار با موفقیت حذف شد.')
    } catch (err) {
      console.error('❌ خطا در حذف هشدار:', err)
      alert('حذف هشدار با خطا مواجه شد.')
    }
  }
}

const deleteWarningsByMonth = async (month) => {
  if (!confirm(`آیا مطمئن هستید که می‌خواهید تمام هشدارهای ماه ${getMonthLabel(month)} را حذف کنید؟`)) return
  const monthWarnings = warnings.value.filter(w => w.month === month)
  try {
    for (const w of monthWarnings) await deleteDoc(doc(db, 'performanceWarnings', w.id))
    warnings.value = warnings.value.filter(w => w.month !== month)
    alert(`✅ تمام هشدارهای ماه ${getMonthLabel(month)} حذف شدند.`)
  } catch (err) {
    console.error('خطا در حذف هشدارهای ماه:', err)
    alert('❌ حذف هشدارهای ماه با خطا مواجه شد.')
  }
}

const warningsPerWorkerChartData = computed(() => {
  const map = {}

  filteredWarnings.value.forEach(w => {
    if (!map[w.workerName]) map[w.workerName] = 0
    map[w.workerName]++
  })

  const names = Object.keys(map)
  const counts = Object.values(map)

  return {
    labels: names,
    datasets: [
      {
        label: 'تعداد هشدارها',
        backgroundColor: '#F59E0B',
        data: counts
      }
    ]
  }
})

const downloadWarningsExcel = () => {
  const rows = warnings.value.map(w => ({
    'نام خیاط': w.workerName,
    'درصد افت': `${w.dropPercent}%`,
    'ماه': getMonthLabel(w.month),
    'تاریخ': w.timestamp?.toDate?.().toLocaleDateString('fa-IR') || '---'
  }))
  const worksheet = XLSX.utils.json_to_sheet(rows)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'هشدارها')
  XLSX.writeFile(workbook, 'هشدارهای-عملکرد.xlsx')
}

const downloadWarningsPDF = () => {
  const doc = new jsPDF()
  doc.setFont('Vazirmatn', 'normal')
  doc.text('📉 هشدارهای افت عملکرد خیاط‌ها', 10, 10)
  const rows = warnings.value.map(w => [w.workerName, `${w.dropPercent}%`, getMonthLabel(w.month), w.timestamp?.toDate?.().toLocaleDateString('fa-IR') || '---'])
  autoTable(doc, { head: [['نام خیاط', 'درصد افت', 'ماه', 'تاریخ']], body: rows, startY: 20 })
  doc.save('هشدارهای-عملکرد.pdf')
}

const printWarnings = () => {
  const printContent = document.querySelector('.p-4') // قسمت اصلی هشدارها

  if (printContent) {
    const newWin = window.open('', '', 'width=900,height=600')
    newWin.document.write(`
      <html>
        <head>
          <title>پرینت هشدارهای عملکرد</title>
          <style>
            body { font-family: Vazirmatn, sans-serif; direction: rtl; padding: 20px; }
            h2, h3 { color: #1d4ed8; }
            li { margin-bottom: 6px; }
            .text-red-700 { color: #b91c1c; font-weight: bold; }
            .text-gray-800 { color: #1f2937; }
          </style>
        </head>
        <body>${printContent.innerHTML}</body>
      </html>
    `)
    newWin.document.close()
    newWin.focus()
    newWin.print()
    newWin.close()
  } else {
    alert('محتوای قابل پرینت پیدا نشد.')
  }
}


onMounted(() => {
  // eslint-disable-next-line no-unused-vars
  const unsubscribe = onSnapshot(collection(db, 'performanceWarnings'), snapshot => {
    warnings.value = snapshot.docs.map(doc => ({
      id: doc.id,
      ...doc.data()
    }))
  })

  // اگر خواستی در آینده پاک‌سازی انجام بدی:
  // onUnmounted(() => unsubscribe())
})

const addTestWarning = async () => {
  const now = new Date()
  const month = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`

  try {
    await addDoc(collection(db, 'performanceWarnings'), {
      workerName: 'خیاط تستی',
      dropPercent: 45, // درصد بالای آستانه
      timestamp: now,
      month
    })
    alert('⚠️ هشدار تستی با موفقیت ثبت شد.')
  } catch (err) {
    console.error('❌ خطا در ثبت هشدار تستی:', err)
    alert('ثبت هشدار تستی با خطا مواجه شد.')
  }
}

</script>
