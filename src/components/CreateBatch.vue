<!-- eslint-disable vue/multi-word-component-names -->
<!-- eslint-disable no-unused-vars -->
<template>
  <AppLayout>
    <div v-if="isSupervisor" class="p-4 bg-white rounded shadow">
      <h2 class="text-xl font-bold mb-4">
        {{ editMode ? '✏️ ویرایش دسته' : '➕ ثبت دسته جدید' }}
      </h2>

      <form @submit.prevent="handleSubmit" class="space-y-3">
        <p v-if="successMessage" class="text-green-600 font-bold mt-4">
          ✅ {{ successMessage }}
        </p>

        <!-- نام کارگر -->
        <div>
          <label class="block mb-1">نام کارگر:</label>
          <select v-model="workerName" class="border p-2 rounded w-full" required>
            <option value="" disabled selected>انتخاب کنید...</option>
            <option v-for="worker in workersList" :key="worker" :value="worker">{{ worker }}</option>
          </select>
        </div>

        <!-- نام قطعه -->
        <div>
          <label class="block">نام قطعه:</label>
          <input v-model="partName" class="input" required />
        </div>

        <!-- تعداد -->
        <div>
          <label class="block">تعداد:</label>
          <input v-model.number="quantity" type="number" class="input" required />
        </div>

        <!-- نوع پارچه -->
        <div>
          <label class="block">نوع پارچه:</label>
          <input v-model="fabricType" class="input" placeholder="مثلاً کرپ، کتان..." />
        </div>

        <!-- مقدار مصرف -->
        <div>
          <label class="block">مقدار مصرف (متر):</label>
          <input v-model.number="metersUsed" type="number" class="input" placeholder="مثلاً 12.5" step="0.1" />
        </div>

        <!-- دکمه ارسال -->
        <button type="submit" class="button">
          {{ editMode ? 'ذخیره تغییرات' : 'ثبت' }}
        </button>
      </form>
    </div>

    <!-- اگر سرپرست نبود -->
    <div v-else class="text-red-500 p-4 bg-red-100 rounded">
      فقط سرپرست مجاز به ثبت یا ویرایش دسته تولیدی است.
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/components/AppLayout.vue'

export default {
  name: 'CreateBatch',
  components: { AppLayout },
  props: { batch: Object },

  data() {
    return {
      workerName: '',
      partName: '',
      quantity: 1,
      fabricType: '',
      metersUsed: null,
      editingId: null,
      editMode: false,
      workersList: [],
      successMessage: ''
    }
  },

  computed: {
    isSupervisor() {
      return localStorage.getItem('userRole') === 'supervisor'
    }
  },

mounted() {
  this.fetchWorkers()

  const savedBatch = localStorage.getItem('editBatch')
  if (savedBatch) {
    const parsed = JSON.parse(savedBatch)
    this.workerName = parsed.workerName
    this.partName = parsed.partName
    this.quantity = parsed.quantity
    this.fabricType = parsed.fabricType || ''
    this.metersUsed = parsed.metersUsed || null
    this.editingId = parsed.id
    this.editMode = true
    localStorage.removeItem('editBatch')
  }
}
,

  watch: {
    batch: {
      handler(newVal) {
        if (newVal) {
          this.workerName = newVal.workerName
          this.partName = newVal.partName
          this.quantity = newVal.quantity
          this.fabricType = newVal.fabricType || ''
          this.metersUsed = newVal.metersUsed || null
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

    async fetchWorkers() {
      try {
        const response = await fetch('https://app.paryamezon.ir/api/get-workers.php')
        const result = await response.json()
        if (result.success) {
          this.workersList = result.workers
        } else {
          console.error('❌ دریافت کارگرها ناموفق:', result.message)
        }
      } catch (err) {
        console.error('❌ خطا در دریافت کارگرها:', err)
      }
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
      id: this.editingId || Date.now(), // آیدی اگر در حالت ویرایش هست از قبل گرفته میشه
      workerName: this.workerName,
      partName: this.partName,
      quantity: this.quantity,
      timestamp: now.toISOString(),
      month,
      fabricType: this.fabricType,
      metersUsed: parseFloat(this.metersUsed) || 0,
      createdBy: userRole
    }

    const endpoint = this.editMode
      ? 'https://app.paryamezon.ir/api/update-batch.php'
      : 'https://app.paryamezon.ir/api/save-batch.php'

    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(batchData)
    })

    const result = await res.json()

    if (result.success) {
      this.successMessage = this.editMode
        ? '✅ دسته با موفقیت ویرایش شد.'
        : '✅ دسته جدید با موفقیت ذخیره شد.'

      setTimeout(() => { this.successMessage = '' }, 3000)
      this.resetForm()
    } else {
      alert('❌ خطا در ذخیره: ' + result.message)
    }
  } catch (err) {
    console.error('❌ خطا در اتصال به PHP:', err)
    alert('⚠️ خطا در ارتباط با سرور. لطفاً بعداً تلاش کنید.')
  }
}
,

    resetForm() {
      this.workerName = ''
      this.partName = ''
      this.quantity = 1
      this.fabricType = ''
      this.metersUsed = null
      this.editMode = false
      this.editingId = null
    }
  }
}
</script>




