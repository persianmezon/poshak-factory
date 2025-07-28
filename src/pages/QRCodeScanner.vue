<!-- eslint-disable -->
<template>
  <div class="p-6">
    <h1 class="text-center font-bold mb-4">اسکن QRCode</h1>

    <div id="qr-reader" style="width: 300px"></div>

    <p v-if="cameraError" class="text-red-500 mt-4 text-center">
      دوربین در دسترس نیست. لطفاً با دستگاهی که دوربین دارد وارد شوید.
    </p>

<!-- انتخاب کارگر یا پیام راهنما -->
<div v-if="scannedText && parsedQR" class="mt-4">
  <div v-if="parsedQR.section === 'سالن دوخت'">
    <label class="block mb-1">👷‍♀️ انتخاب کارگر:</label>
    <select v-model="selectedWorker" class="border p-2 rounded w-full">
      <option value="">-- انتخاب کنید --</option>
      <option
        v-for="worker in filteredWorkers"
        :key="worker.uid"
        :value="worker.uid"
      >
        {{ worker.name }}
      </option>
    </select>
    <button
      @click="submitScan"
      :disabled="!selectedWorker"
      class="mt-2 bg-green-600 text-white px-4 py-2 rounded"
    >
      ✅ ثبت نهایی آمار
    </button>
  </div>

  <div v-else-if="parsedQR.section === 'خروج از برش'" class="text-blue-600 text-sm mt-2">
    📤 خروج از انبار برش و ورود به سالن دوخت
    <button
      @click="submitScan"
      class="mt-2 bg-green-600 text-white px-4 py-2 rounded"
    >
      ✅ ثبت نهایی آمار
    </button>
  </div>

  <div v-else-if="parsedQR.section === 'نهایی‌کار'" class="text-purple-600 text-sm mt-2">
    ✅ خروج از سالن دوخت و ورود به انبار نهایی
    <button
      @click="submitScan"
      class="mt-2 bg-green-600 text-white px-4 py-2 rounded"
    >
      ✅ ثبت نهایی آمار
    </button>
  </div>
</div>


    <!-- انتخاب تاریخ -->
    <div class="my-4 text-center">
      <label class="mr-2 font-medium">نمایش آمار روز:</label>
      <input type="date" v-model="selectedDate" class="border p-1 rounded" />
    </div>

    <!-- کد اسکن‌شده -->
    <div v-if="scannedText" class="mt-6 text-center bg-gray-100 p-4 rounded shadow">
      <p class="font-medium text-lg">📦 کد خوانده‌شده:</p>
      <p class="mt-2 text-blue-600 break-words">{{ scannedText }}</p>
    </div>

    <!-- دکمه دانلود PDF -->
    <button
      @click="downloadPDF"
      class="bg-purple-600 hover:bg-purple-700 transition text-white px-6 py-2 rounded shadow mb-4"
    >
      📥 دانلود PDF گزارش
    </button>

    <!-- فیلتر -->
    <input
      v-model="filterText"
      placeholder="🔍 جستجو در کد، بخش یا کاربر..."
      class="border rounded p-2 w-full mb-4"
    />

    <p class="text-right mb-2 text-sm text-gray-600">🔢 مجموع کل قطعات: {{ totalCount }}</p>

    <!-- جدول آمار -->
    <table class="w-full text-sm text-right border border-gray-300 shadow-sm rounded overflow-hidden">
      <thead class="bg-gray-200">
        <tr>
          <th class="border px-2 py-2">بخش</th>
          <th class="border px-2 py-2">قسمت</th>
          <th class="border px-2 py-2">کد</th>
          <th class="border px-2 py-2">تعداد</th>
          <th class="border px-2 py-2">کاربر</th>
          <th class="border px-2 py-2">تاریخ</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in filteredRecordsByDate"
          :key="item.id"
          class="border odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition"
        >
          <td class="border px-2 py-2">{{ item.section }}</td>
          <td class="border px-2 py-2">{{ item.part || '---' }}</td>
          <td class="border px-2 py-2">{{ item.code }}</td>
          <td class="border px-2 py-2">{{ item.count }}</td>
          <td class="border px-2 py-2">{{ getWorkerName(item.workerId) }}</td>
          <td class="border px-2 py-2">
            {{ item.createdAt ? item.createdAt.toLocaleString('fa-IR') : '-' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import { Html5Qrcode } from "html5-qrcode"

export default {
  data() {
    return {
      scannedText: '',
      cameraError: false,
      parsedQR: null,
      selectedWorker: '',
      workersList: [],
      records: [],
      selectedDate: new Date().toISOString().substr(0, 10),
      filterText: ''
    }
  },

  mounted() {
    this.fetchWorkersList()
    this.fetchRecords()
  
  
  this.workersList.push(
  { uid: 'cutout-worker', name: '👷‍♂️ خروج از انبار برش' },
  { uid: 'final-worker', name: '🏁 نهایی‌کار' }
)

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      this.cameraError = true
      return
    }

    const html5QrCode = new Html5Qrcode("qr-reader")
    const config = { fps: 10, qrbox: 250 }

    html5QrCode.start(
      { facingMode: "environment" },
      config,
      decodedText => {
        this.scannedText = decodedText
        this.parseQRAndSave(decodedText)

        setTimeout(() => {
          html5QrCode.stop().catch(err => console.warn("❌ توقف اسکن:", err))
        }, 500)
      }
    ).catch(err => {
      this.cameraError = true
      console.error("خطا در راه‌اندازی دوربین:", err)
    })
  },

  computed: {
    filteredRecords() {
      const keyword = this.filterText.toLowerCase()
      return this.records.filter(item =>
        (item.section || '').toLowerCase().includes(keyword) ||
        (item.part || '').toLowerCase().includes(keyword) ||
        (item.code || '').toLowerCase().includes(keyword) ||
        (item.workerId || '').toLowerCase().includes(keyword)
      )
    },
    filteredRecordsByDate() {
      return this.filteredRecords.filter(item =>
        item.createdAt?.toISOString().substr(0, 10) === this.selectedDate
      )
    },
    totalCount() {
      return this.filteredRecords.reduce((sum, item) => sum + (item.count || 0), 0)
    }
  },
filteredWorkers() {
  const section = this.parsedQR?.section
  if (section === 'سالن دوخت') {
    return this.workersList.filter(w => w.uid !== 'cutout-worker' && w.uid !== 'final-worker')
  }
  if (section === 'خروج از برش') {
    return this.workersList.filter(w => w.uid === 'cutout-worker')
  }
  if (section === 'نهایی‌کار') {
    return this.workersList.filter(w => w.uid === 'final-worker')
  }
  return []
}
,
  methods: {
getWorkerName(uid) {
  const worker = this.workersList.find(w => w.uid === uid)
  return worker ? worker.name : 'نامشخص'
}

,
    async fetchWorkersList() {
      try {
        const res = await fetch(`https://app.paryamezon.ir/api/get-workers.php?t=${Date.now()}`)
        const json = await res.json()
        if (json.success && Array.isArray(json.workers)) {
          this.workersList = json.workers
        }
      } catch (err) {
        console.error('❌ خطا در واکشی کارگرها:', err)
      }
    },

async fetchRecords() {
  try {
    const res = await fetch(`https://app.paryamezon.ir/api/get-scans.php?t=${Date.now()}`)
    const json = await res.json()
    if (json.success) {
      this.records = json.records.map(r => {
        if (r.createdAt && typeof r.createdAt === 'number') {
          r.createdAt = new Date(r.createdAt * 1000)
        }
        return r
      })
    }
  } catch (err) {
    console.error('❌ خطا در واکشی رکوردها:', err)
  }
}
,

async parseQRAndSave(text) {
  try {
    let data = {}
    if (text.includes('برش') || text.includes('دوخت')) {
      const m = text.match(/قسمت: (.+?) - کد: (.+?) - تعداد: (\d+)/)
      if (m) {
        data = {
          section: text.includes('برش') ? 'خروج از برش' : text.includes('دوخت') ? 'سالن دوخت' : '',
          part: m[1],
          code: m[2],
          count: +m[3]
        }
      }
    } else if (text.includes('نهایی‌کار')) {
      const m = text.match(/کد کار: (.+?) - تعداد: (\d+)/)
      if (m) {
        data = {
          section: 'نهایی‌کار',
          code: m[1],
          count: +m[2]
        }
      }
    }

    if (Object.keys(data).length > 0) {
      this.parsedQR = data
    } else {
      alert('⚠️ فرمت QR نامعتبر است.')
    }
  } catch (err) {
    console.error('❌ خطا در پردازش QR:', err)
    alert('خطا در پردازش QR')
  }
}
,

async submitScan() {
  if (!this.parsedQR) return

  const timestamp = Math.floor(Date.now() / 1000)
  const section = this.parsedQR.section
  const part = this.parsedQR.part || null

  let workerId = this.selectedWorker
  if (section === 'خروج از برش') workerId = 'cutout-worker'
  if (section === 'نهایی‌کار') workerId = 'final-worker'

  if (section === 'سالن دوخت' && !workerId) {
    alert('لطفاً یک کارگر انتخاب کنید')
    return
  }

  const record = {
    workerId,
    section,
    part,
    code: this.parsedQR.code,
    count: this.parsedQR.count,
    createdAt: timestamp
  }

  try {
    // ثبت در qr_stats
    const resMain = await fetch('https://app.paryamezon.ir/api/submit-scan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(record)
    })

    const jsonMain = await resMain.json()
    if (!jsonMain.success) throw new Error(jsonMain.message || 'خطا در ثبت آمار اصلی')

    // ثبت در مسیر اختصاصی
    if (section === 'نهایی‌کار') {
      const resFinal = await fetch('https://app.paryamezon.ir/api/submit-sewing-to-final.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          code: record.code,
          count: record.count,
          workerId,
          createdAt: timestamp,
          part
        })
      })

      const jsonFinal = await resFinal.json()
      if (!jsonFinal.success) throw new Error(jsonFinal.message || 'خطا در ثبت نهایی‌کار')
    }

    alert('✅ آمار با موفقیت ثبت شد')
    this.scannedText = ''
    this.selectedWorker = ''
    this.parsedQR = null
    await this.fetchRecords()

  } catch (err) {
    console.error('❌ خطا در ثبت آمار:', err)
    alert('❌ خطا در ارتباط با سرور یا ثبت آمار')
  }
}


,

    async downloadPDF() {
      try {
        const doc = new jsPDF()
        doc.text('📊 گزارش آماری اسکن QRCode', 14, 15)
        doc.text(`🗓 تاریخ گزارش: ${this.selectedDate}`, 14, 22)

        const rows = this.filteredRecordsByDate.map(item => [
          item.createdAt ? new Date(item.createdAt).toLocaleString('fa-IR') : '-',
          item.section || '-', item.part || '-', item.code || '-',
          item.count || 0, item.workerId || '-'
        ])

        autoTable(doc, {
          head: [['تاریخ', 'بخش', 'قسمت', 'کد', 'تعداد', 'کاربر']],
          body: rows,
          startY: 28
        })

        const pdfBlob = doc.output('blob')
        const formData = new FormData()
        formData.append('pdf', pdfBlob, `qr-report-${this.selectedDate}.pdf`)

        const uploadRes = await fetch('https://app.paryamezon.ir/api/save-pdf-report.php', {
          method: 'POST',
          body: formData
        })

        const result = await uploadRes.json()
        if (result.success) {
          console.log('✅ گزارش در هاست ذخیره شد:', result.path)
        }

        doc.save(`qr-report-${this.selectedDate}.pdf`)
      } catch (err) {
        console.error('❌ خطا در ساخت PDF:', err)
        alert('❌ خطا در ساخت PDF')
      }
    }
  }
}
</script>


