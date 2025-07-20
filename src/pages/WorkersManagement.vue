<!-- eslint-disable -->
<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-6">👷 مدیریت کارگرها</h1>

    <!-- فرم افزودن کارگر -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mb-6">
      <input
        v-model="newWorkerName"
        type="text"
        placeholder="نام کارگر جدید..."
        class="flex-1 border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
      />
      <button
        @click="addWorker"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition"
      >
        ➕ افزودن
      </button>
    </div>

    <!-- لیست کارگرها -->
    <div class="overflow-x-auto shadow border rounded">
      <table class="min-w-full text-sm text-right">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="px-4 py-2 font-semibold text-gray-700">👤 نام</th>
            <th class="px-4 py-2 font-semibold text-gray-700">🛠️ عملیات</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="worker in workers"
            :key="worker.id"
            class="border-b hover:bg-gray-50"
          >
            <td class="px-4 py-2">{{ worker.name }}</td>
            <td class="px-4 py-2">
              <button
                @click="deleteWorker(worker.id)"
                class="text-red-600 hover:underline"
              >
                حذف
              </button>
            </td>
          </tr>
          <tr v-if="workers.length === 0">
            <td colspan="2" class="text-center py-4 text-gray-400">هیچ کارگری ثبت نشده است</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { collection, addDoc, getDocs, deleteDoc, doc } from 'firebase/firestore'
import { db } from '@/firebase'

export default {
  name: 'WorkersManagement',
  setup() {
    const newWorkerName = ref('')
    const workers = ref([])

    const fetchWorkers = async () => {
      const snapshot = await getDocs(collection(db, 'workers'))
      workers.value = snapshot.docs.map(doc => ({
        id: doc.id,
        ...doc.data()
      }))
    }

    const addWorker = async () => {
      const name = newWorkerName.value.trim()
      if (!name) return alert('نام کارگر را وارد کنید')
      await addDoc(collection(db, 'workers'), { name })
      newWorkerName.value = ''
      fetchWorkers()
    }

    const deleteWorker = async (id) => {
      if (!confirm('آیا از حذف این کارگر مطمئن هستید؟')) return
      await deleteDoc(doc(db, 'workers', id))
      fetchWorkers()
    }

    onMounted(fetchWorkers)

    return {
      newWorkerName,
      workers,
      addWorker,
      deleteWorker
    }
  }
}
</script>



