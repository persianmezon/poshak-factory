<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="p-6 max-w-xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold flex items-center gap-2 justify-center">
      ✂️ ثبت برش در انبار
    </h1>

    <div class="bg-white shadow rounded-xl p-6 space-y-4">
      <div>
        <label class="block mb-1 text-sm font-medium">🧵 نوع پارچه:</label>
        <select v-model="fabricType" class="w-full border rounded px-3 py-2">
          <option disabled value="">لطفاً انتخاب کنید</option>
          <option>پارچه اصلی</option>
          <option>آستر</option>
        </select>
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">📦 کد مانتو:</label>
        <input v-model="productCode" placeholder="مثلاً 564" class="w-full border rounded px-3 py-2" />
      </div>

      <div>
        <label class="block mb-1 text-sm font-medium">🔢 تعداد قطعه:</label>
        <input v-model.number="count" type="number" min="1" class="w-full border rounded px-3 py-2" />
      </div>

      <button
        @click="submit"
        :disabled="!fabricType || !productCode || !count"
        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition"
      >
        ➕ ثبت در انبار
      </button>
    </div>

    <p v-if="success" class="text-green-600 font-medium">✅ با موفقیت ثبت شد!</p>
    <p v-if="error" class="text-red-600 font-medium">❌ خطا در ثبت اطلاعات!</p>
  </div>
</template>

<script>
export default {
  data() {
    return {
      fabricType: '',
      productCode: '',
      count: null,
      success: false,
      error: false
    }
  },
  methods: {
    async submit() {
      try {
        const payload = {
          section: 'برش',
          part: this.fabricType,
          code: this.productCode,
          count: this.count,
          createdAt: Math.floor(Date.now() / 1000)
        }

        const res = await fetch('/api/add-cut-to-inventory.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })

        const json = await res.json()
        if (json.success) {
          this.success = true
          this.error = false
          this.fabricType = ''
          this.productCode = ''
          this.count = null
        } else {
          this.success = false
          this.error = true
        }
      } catch (e) {
        console.error('❌ Error:', e)
        this.success = false
        this.error = true
      }
    }
  }
}
</script>
