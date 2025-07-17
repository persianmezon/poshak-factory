<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-2">📦 لیست دسته‌های تولیدی</h2>
    <ul v-if="batches.length" class="space-y-2">
      <li v-for="batch in batches" :key="batch.id" class="p-2 border rounded bg-white shadow">
        <strong>{{ batch.workerName }}</strong> - {{ batch.partName }} - {{ batch.quantity }} عدد
      </li>
    </ul>
    <p v-else>دسته‌ای ثبت نشده است.</p>
  </div>
</template>

<script>
import { collection, onSnapshot } from 'firebase/firestore'
import { db } from '../firebase'

export default {
  name: 'BatchList',
  data() {
    return {
      batches: []
    }
  },
  created() {
    const q = collection(db, 'batches')
    onSnapshot(q, (snapshot) => {
      this.batches = snapshot.docs.map(doc => ({
        id: doc.id,
        ...doc.data()
      }))
    })
  }
}
</script>
