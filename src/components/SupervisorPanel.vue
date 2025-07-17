<template><AppLayout>
  <div class="p-4 max-w-md mx-auto">
    <h2 class="text-xl font-bold mb-4">🎯 پنل سرپرست</h2>

    <qrcode-stream @decode="onDecode" class="mb-4" />

    <div v-if="batch" class="mt-4 bg-gray-100 p-3 rounded">
      <p><strong>نام دسته:</strong> {{ batch.name }}</p>
      <p><strong>تعداد:</strong> {{ batch.quantity }}</p>
      <p><strong>مرحله:</strong> {{ batch.step }}</p>

      <label class="block mt-2">انتخاب کارگر:</label>
      <select v-model="selectedWorker" class="input mt-1">
        <option disabled value="">انتخاب کنید</option>
        <option v-for="worker in workers" :key="worker">{{ worker }}</option>
      </select>

      <button @click="assignWorker" class="btn mt-4">ثبت 👷‍♂️</button>
    </div>
  </div>
  </AppLayout>
</template>

<script>
import { db } from '../firebase'
import { collection, query, where, getDocs, updateDoc, doc } from 'firebase/firestore'
import { QrcodeStream } from 'vue-qrcode-reader'
import AppLayout from '@/components/AppLayout.vue'

export default {
  components: { AppLayout, QrcodeStream },
  data() {
    return {
      scannedCode: '',
      batch: null,
      selectedWorker: '',
      workers: ['علی', 'زهرا', 'مجتبی', 'فاطمه']
    }
  },
  methods: {
    async onDecode(code) {
      this.scannedCode = code
      const q = query(collection(db, 'batches'), where('qr', '==', code))
      const snapshot = await getDocs(q)
      if (!snapshot.empty) {
        this.batch = { ...snapshot.docs[0].data(), id: snapshot.docs[0].id }
      } else {
        this.batch = null
        alert('کدی پیدا نشد ❌')
      }
    },
    async assignWorker() {
      if (!this.batch || !this.selectedWorker) return
      await updateDoc(doc(db, 'batches', this.batch.id), {
        assignedTo: this.selectedWorker
      })
      alert('کارگر با موفقیت ثبت شد ✅')
      this.scannedCode = ''
      this.selectedWorker = ''
      this.batch = null
    }
  }
}
</script>

<style scoped>
.input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
}
.btn {
  background-color: #10b981;
  color: white;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
}
</style>
<button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
  تست Tailwind
</button>
