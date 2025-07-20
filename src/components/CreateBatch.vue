<!-- eslint-disable vue/multi-word-component-names -->
 <!-- eslint-disable no-unused-vars -->
<template>
  <AppLayout>
    <!-- فرم ثبت دسته فقط برای سرپرست -->
    <div v-if="isSupervisor" class="p-4 bg-white rounded shadow">
      <h2 class="text-xl font-bold mb-4">
        {{ editMode ? '✏️ ویرایش دسته' : '➕ ثبت دسته جدید' }}
      </h2>

      <form @submit.prevent="handleSubmit" class="space-y-3">
  <p v-if="successMessage" class="text-green-600 font-bold mt-4">
    ✅ {{ successMessage }}
  </p>

<label class="block mb-1">نام کارگر:</label>
<select v-model="workerName" class="border p-2 rounded w-full">
  <option value="" disabled selected>انتخاب کنید...</option>
  <option v-for="worker in workers" :key="worker">{{ worker }}</option>
</select>

<select v-model="form.workerName" class="border p-2 rounded w-full">
  <option value="">انتخاب کارگر</option>
  <option v-for="name in workersList" :key="name" :value="name">{{ name }}</option>
</select>

  <div>
    <label class="block">نام قطعه:</label>
    <input v-model="partName" class="input" required />
  </div>

  <div>
    <label class="block">تعداد:</label>
    <input v-model.number="quantity" type="number" class="input" required />
  </div>

  <!-- 🔹 نوع پارچه -->
  <div>
    <label class="block">نوع پارچه:</label>
    <input v-model="fabricType" class="input" placeholder="مثلاً کرپ، کتان..." />
  </div>

  <!-- 🔹 متر مصرف‌شده -->
  <div>
    <label class="block">مقدار مصرف (متر):</label>
    <input v-model.number="metersUsed" type="number" class="input" placeholder="مثلاً 12.5" step="0.1" />
  </div>

  <button type="submit" class="button">
    {{ editMode ? 'ذخیره تغییرات' : 'ثبت' }}
  </button>
</form>

    </div>

    <!-- اگر کاربر سرپرست نبود، پیام نمایشی -->
    <div v-else class="text-red-500 p-4 bg-red-100 rounded">
      فقط سرپرست مجاز به ثبت یا ویرایش دسته تولیدی است.
    </div>
  </AppLayout>
</template>

<script>
import { db } from '../firebase'
import { collection, addDoc, updateDoc, doc, getDocs } from 'firebase/firestore'
import AppLayout from '@/components/AppLayout.vue'
import jsPDF from 'jspdf'
import { vazirmatnFontBase64 } from '@/utils/vazirmatn'
import { getStorage, ref as storageRef, uploadBytes } from 'firebase/storage'

const SEVERE_THRESHOLD = 30

export default {
  components: { AppLayout },
  name: 'CreateBatch',
  props: {
    batch: Object
  },
  data() {
    return {
      workerName: '',
      partName: '',
      quantity: 1,
      fabricType: '',
      metersUsed: null,
      editingId: null,
      workersList: [],
      editMode: false,
       workers: [],
      successMessage: ''
    }
  },
  async mounted() {
  const snapshot = await getDocs(collection(db, 'workers'))
  this.workersList = snapshot.docs.map(doc => doc.data().name)
},
async created() {
  const snap = await getDocs(collection(db, 'workers'))
  this.workers = snap.docs.map(doc => doc.data().name)
},
  computed: {
    isSupervisor() {
      return localStorage.getItem('userRole') === 'supervisor'
    }
  },
  watch: {
    batch: {
      handler(newVal) {
        if (newVal) {
          this.workerName = newVal.workerName
          this.partName = newVal.partName
          this.quantity = newVal.quantity
          this.fabricType = newVal.storageUsage?.fabricType || ''
          this.metersUsed = newVal.storageUsage?.metersUsed || null
          this.editingId = newVal.id
          this.editMode = true
        }
      },
      immediate: true
    }
  },
methods: {
  getMonthValue(date) {
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    return `${y}-${m}`
  },

  async handleSubmit() {
    if (!this.isSupervisor) {
      alert('شما مجاز به ثبت دسته نیستید!')
      return
    }

    try {
      const now = new Date()
      const month = this.getMonthValue(now)
      const userRole = localStorage.getItem('userRole') || 'unknown'

      const batchData = {
        workerName: this.workerName,
        partName: this.partName,
        quantity: this.quantity,
        timestamp: now,
        month,
        storageUsage: {
          fabricType: this.fabricType,
          metersUsed: parseFloat(this.metersUsed) || 0
        },
        createdAt: new Date(),
        createdBy: userRole
      }

      if (this.editMode && this.editingId) {
        const batchRef = doc(db, 'batches', this.editingId)
        await updateDoc(batchRef, batchData)
      } else {
        await addDoc(collection(db, 'batches'), batchData)

        const dropPercent = this.estimateDropPercent(this.quantity)
        if (dropPercent >= SEVERE_THRESHOLD) {
          await addDoc(collection(db, 'performanceWarnings'), {
            workerName: this.workerName,
            dropPercent,
            timestamp: now,
            month
          })
        }

        // ساخت PDF و آپلود در Firebase Storage
        const docPdf = new jsPDF()
        docPdf.addFileToVFS('Vazirmatn.ttf', vazirmatnFontBase64)
        docPdf.addFont('Vazirmatn.ttf', 'Vazirmatn', 'normal')
        docPdf.setFont('Vazirmatn')
        docPdf.setFontSize(14)

        docPdf.text('📄 ثبت دسته جدید', 10, 20)
        docPdf.setFontSize(12)
        docPdf.text(`👷‍♀️ خیاط: ${this.workerName}`, 10, 35)
        docPdf.text(`📦 قطعه: ${this.partName}`, 10, 45)
        docPdf.text(`🔢 تعداد: ${this.quantity}`, 10, 55)
        docPdf.text(`🧵 نوع پارچه: ${this.fabricType}`, 10, 65)
        docPdf.text(`📏 متراژ: ${parseFloat(this.metersUsed || 0)} متر`, 10, 75)

        const pdfBlob = docPdf.output('blob')
        const filename = `pdf-archive/batch-${Date.now()}.pdf`
        const storage = getStorage()
        const fileRef = storageRef(storage, filename)
        await uploadBytes(fileRef, pdfBlob)
      }

      this.successMessage = this.editMode
        ? 'ویرایش با موفقیت انجام شد.'
        : 'دسته جدید ثبت شد.'

      setTimeout(() => {
        this.successMessage = ''
      }, 3000)

      this.resetForm()
    } catch (error) {
      console.error('خطا در ثبت/ویرایش دسته:', error)
      alert('⚠️ خطایی در ثبت یا ویرایش رخ داد.')
    }
  },

  estimateDropPercent(quantity) {
    if (quantity >= 100) return 0
    if (quantity >= 80) return 20
    if (quantity >= 60) return SEVERE_THRESHOLD
    return 50
  },

  resetForm() {
    this.workerName = ''
    this.partName = ''
    this.quantity = 1
    this.fabricType = ''
    this.metersUsed = null
    this.editingId = null
    this.editMode = false
  }
}
}
</script>




