<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">🗑 تاریخچه حذف دسته‌ها</h1>
    <table class="min-w-full bg-white rounded shadow">
      <thead>
        <tr class="bg-gray-200 text-right">
          <th class="p-2">🆔 Batch ID</th>
          <th class="p-2">⏰ زمان حذف</th>
          <th class="p-2">👤 حذف‌شده توسط</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in deleted" :key="item.id" class="border-t">
          <td class="p-2">{{ item.batchId }}</td>
          <td class="p-2">{{ formatDate(item.deletedAt) }}</td>
          <td class="p-2">{{ item.deletedBy }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { collection, getDocs } from 'firebase/firestore'
import { db } from '@/firebase'

export default {
  name: 'DeletedBatches',
  data() {
    return { deleted: [] }
  },
  async mounted() {
    const snap = await getDocs(collection(db, 'deletedBatches'))
    this.deleted = snap.docs.map(doc => doc.data())
  },
  methods: {
    formatDate(ts) {
      const d = new Date(ts.seconds * 1000)
      return d.toLocaleDateString('fa-IR') + ' - ' + d.toLocaleTimeString('fa-IR')
    }
  }
}
</script>
