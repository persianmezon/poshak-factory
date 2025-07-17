<!-- eslint-disable -->
<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">اسکن QRCode</h1>

    <qrcode-stream
      @decode="onDecode"
      @init="onInit"
      class="w-full max-w-md mx-auto"
    />
    <p v-if="cameraError" class="text-red-500 mt-4 text-center">
      دوربین در دسترس نیست. لطفاً با دستگاهی که دوربین دارد وارد شوید.
    </p>

    <!-- انتخاب تاریخ -->
<div class="my-4 text-center">
  <label class="mr-2 font-medium">نمایش آمار روز:</label>
  <input type="date" v-model="selectedDate" class="border p-1 rounded" />
</div>


    <div v-if="scannedText" class="mt-6 text-center bg-gray-100 p-4 rounded shadow">
      <p class="font-medium text-lg">📦 کد خوانده‌شده:</p>
      <p class="mt-2 text-blue-600 break-words">{{ scannedText }}</p>
    </div>

<div class="flex items-center gap-4 mb-4">
  <div>
    <label class="block text-sm font-medium mb-1">از تاریخ:</label>
    <input type="date" v-model="startDate" class="border p-2 rounded" />
  </div>
  <div>
    <label class="block text-sm font-medium mb-1">تا تاریخ:</label>
    <input type="date" v-model="endDate" class="border p-2 rounded" />
  </div>
</div>

    <!-- دکمه دانلود PDF -->
    <button @click="downloadPDF" class="bg-purple-600 text-white px-4 py-2 rounded mb-4">
      📥 دانلود PDF گزارش
    </button>

    <!-- فیلتر -->
    <input
      v-model="filterText"
      placeholder="🔍 جستجو در کد، بخش یا کاربر..."
      class="border rounded p-2 w-full mb-4"
    />

    <!-- مجموع -->
    <p class="text-right mb-2 text-sm text-gray-600">🔢 مجموع کل قطعات: {{ totalCount }}</p>

    <!-- جدول آمار فیلترشده -->
    <table class="w-full table-auto border text-sm">
      <thead>
        <tr class="bg-gray-200">
          <th class="px-2 py-1">بخش</th>
          <th class="px-2 py-1">قسمت</th>
          <th class="px-2 py-1">کد</th>
          <th class="px-2 py-1">تعداد</th>
          <th class="px-2 py-1">کاربر</th>
          <th class="px-2 py-1">تاریخ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in filteredRecords" :key="item.id">
          <td class="px-2 py-1">{{ item.section }}</td>
          <td class="px-2 py-1">{{ item.part || '---' }}</td>
          <td class="px-2 py-1">{{ item.code }}</td>
          <td class="px-2 py-1">{{ item.count }}</td>
          <td class="px-2 py-1">{{ item.workerId }}</td>
          <td class="px-2 py-1">{{ new Date(item.createdAt?.seconds * 1000).toLocaleString('fa-IR') }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { QrcodeStream } from 'vue-qrcode-reader'
import jsPDF from 'jspdf'
import 'jspdf-autotable'

export default {
  components: { QrcodeStream },

  data() {
    return {
      scannedText: '',
      cameraError: false,
      startDate: '',
      endDate: '',
      stats: {
        totalScans: 0,
        workersCount: 0,
        lastScanTime: '---'
      },
      scanHistory: [],
      records: [],
      qrScans: [],
      selectedDate: new Date().toISOString().substr(0, 10),
      filterText: ''
    }
  },

  mounted() {
    this.listenToQrStats()
  },
computed: {
  filteredScans() {
    if (!this.filterText) return this.qrScans
    const keyword = this.filterText.toLowerCase()
    return this.qrScans.filter(item =>
      (item.section || '').toLowerCase().includes(keyword) ||
      (item.part || '').toLowerCase().includes(keyword) ||
      (item.code || '').toLowerCase().includes(keyword) ||
      (item.workerId || '').toLowerCase().includes(keyword)
    )
  },
  totalCount() {
    return this.filteredScans.reduce((sum, item) => sum + (item.count || 0), 0)
  },
computed: {
  filteredRecords() {
    return this.records.filter(item => {
      const createdAt = new Date(item.createdAt?.seconds * 1000)

      const fromOk = !this.startDate || createdAt >= new Date(this.startDate)
      const toOk = !this.endDate || createdAt <= new Date(this.endDate + 'T23:59:59')

      const textMatch =
        item.section?.includes(this.filterText) ||
        item.part?.includes(this.filterText) ||
        item.code?.includes(this.filterText) ||
        item.workerId?.includes(this.filterText)

      return fromOk && toOk && (!this.filterText || textMatch)
    })
  },

  totalCount() {
    return this.filteredRecords.reduce((sum, item) => sum + (item.count || 0), 0)
  }
}
,
  filteredRecordsByDate() {
    return this.records.filter(r => {
      const recordDate = new Date(r.createdAt.seconds * 1000).toISOString().substr(0, 10)
      return recordDate === this.selectedDate
    })
  }
}
,
  methods: {
    onDecode(result) {
      this.scannedText = result
      this.parseQRAndSave(result)
    },

    onInit(promise) {
      promise.catch(error => {
        this.cameraError = true
        console.error('دسترسی به دوربین ممکن نیست:', error)
      })
    },

    listenToQrStats() {
      import('@/firebase').then(({ firestore }) => {
        firestore()
          .collection('qr_stats')
          .orderBy('createdAt', 'desc')
          .onSnapshot(snapshot => {
            this.qrScans = snapshot.docs.map(doc => ({
              id: doc.id,
              ...doc.data()
            }))

            this.records = snapshot.docs.map(doc => ({
              id: doc.id,
              ...doc.data()
            }))
          })
      })
    },
    async saveReportToFirestore() {
  try {
    const db = await import('@/firebase')
    const now = new Date()
    const report = {
      createdAt: now,
      createdBy: localStorage.getItem('uid') || 'ناشناس',
      dateRange: {
        from: this.startDate || null,
        to: this.endDate || null
      },
      records: this.filteredRecords,
      total: this.totalCount
    }

    await db.firestore().collection('qr_reports').add(report)
    console.log('📦 گزارش در آرشیو ذخیره شد.')
  } catch (err) {
    console.error('❌ خطا در ذخیره گزارش:', err)
  }
}
,

    async parseQRAndSave(text) {
      let data = {}

      try {
        if (text.includes('برش') || text.includes('دوخت')) {
          const matches = text.match(/قسمت: (.+?) - کد: (.+?) - تعداد: (\d+)/)
          if (matches) {
            data = {
              section: text.includes('برش') ? 'برش' : 'دوخت',
              part: matches[1],
              code: matches[2],
              count: Number(matches[3])
            }
          }
        } else if (text.includes('نهایی‌کار')) {
          const matches = text.match(/کد کار: (.+?) - تعداد: (\d+)/)
          if (matches) {
            data = {
              section: 'نهایی‌کار',
              code: matches[1],
              count: Number(matches[2])
            }
          }
        }

        if (Object.keys(data).length > 0) {
          await this.saveToFirestore(data)
        } else {
          alert('فرمت QR نامعتبر است')
        }

      } catch (err) {
        console.error('خطا در پردازش QR:', err)
        alert('خطا در پردازش QR')
      }
    },

    async saveToFirestore(data) {
      const userUID = localStorage.getItem('uid') || 'ناشناس'
      const now = new Date()

      const record = {
        workerId: userUID,
        section: data.section,
        part: data.part || null,
        code: data.code,
        count: data.count,
        createdAt: now
      }

      try {
        const db = await import('@/firebase')
        await db.firestore().collection('qr_stats').add(record)
        alert('✅ آمار با موفقیت ثبت شد!')

        this.scanHistory.unshift({
          time: now.toLocaleTimeString('fa-IR'),
          code: data.code,
          count: data.count,
          worker: userUID
        })

        this.stats.totalScans++
        this.stats.lastScanTime = now.toLocaleTimeString('fa-IR')

        const uniqueWorkers = new Set(this.scanHistory.map(item => item.worker))
        this.stats.workersCount = uniqueWorkers.size

      } catch (err) {
        console.error('خطا در ذخیره آمار:', err)
        alert('❌ خطا در ثبت آمار')
      }
    },

    async downloadPDF() {
      await this.saveReportToFirestore()
      const doc = new jsPDF()

      const tableColumn = ['تاریخ', 'بخش', 'قسمت', 'کد', 'تعداد', 'کاربر']
      const tableRows = this.filteredScans.map(item => [
        new Date(item.createdAt.seconds * 1000).toLocaleString('fa-IR'),
        item.section || '-',
        item.part || '-',
        item.code,
        item.count,
        item.workerId
      ])

      doc.setFont('helvetica')
      doc.setFontSize(10)
      doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        styles: { font: 'helvetica', fontSize: 9 },
        margin: { top: 20 }
      })

      doc.save('qr-scans-report.pdf')
    }
  }
}
</script>


