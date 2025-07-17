<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">👷‍♀️ مدیریت کارگرها</h1>

    <form @submit.prevent="addWorker" class="mb-6 flex gap-4 flex-wrap items-end">
      <div>
        <label class="block mb-1">نام کارگر:</label>
        <input v-model="newWorker" class="border p-2 rounded" placeholder="مثلاً نرگس حسینی" />
      </div>
      <button type="submit" class="btn-primary">➕ افزودن</button>
    </form>

    <ul class="space-y-2">
      <li
        v-for="worker in workers"
        :key="worker.id"
        class="flex items-center justify-between bg-gray-50 px-4 py-2 rounded shadow-sm"
      >
        <span>{{ worker.name }}</span>
        <button @click="deleteWorker(worker.id)" class="btn-danger text-sm">🗑 حذف</button>
      </li>
    </ul>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { collection, getDocs, addDoc, deleteDoc, doc } from 'firebase/firestore'
import { db } from '@/firebase'
import { useRouter } from 'vue-router'

export default {
  name: 'WorkersManagement',
  setup() {
    const workers = ref([])
    const newWorker = ref('')
    const router = useRouter()

    const fetchWorkers = async () => {
      const snap = await getDocs(collection(db, 'workers'))
      workers.value = snap.docs.map(doc => ({ id: doc.id, ...doc.data() }))
    }

    const addWorker = async () => {
      if (!newWorker.value.trim()) return alert('نام کارگر را وارد کنید')
      await addDoc(collection(db, 'workers'), {
        name: newWorker.value.trim(),
        active: true
      })
      newWorker.value = ''
      await fetchWorkers()
    }

    const deleteWorker = async (id) => {
      if (!confirm('از حذف این کارگر مطمئنی؟')) return
      await deleteDoc(doc(db, 'workers', id))
      await fetchWorkers()
    }

    onMounted(async () => {
      const role = localStorage.getItem('userRole')
      if (role !== 'admin') {
        alert('⛔ فقط مدیر اجازه دسترسی دارد.')
        router.push('/dashboard')
        return
      }
      await fetchWorkers()
    })

    return { workers, newWorker, addWorker, deleteWorker }
  }
}
</script>
