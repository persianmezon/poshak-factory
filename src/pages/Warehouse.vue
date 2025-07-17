<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">📦 مدیریت انبار</h1>

    <!-- فرم افزودن کالا -->
    <form @submit.prevent="addItem" class="bg-white shadow p-4 rounded mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block mb-1 font-medium">بخش</label>
          <select v-model="form.section" class="w-full border p-2 rounded">
            <option disabled value="">انتخاب بخش</option>
            <option>برش</option>
            <option>دوخت</option>
            <option>نهایی‌کار</option>
          </select>
        </div>
        <div>
          <label class="block mb-1 font-medium">کد کالا</label>
          <input v-model="form.code" class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block mb-1 font-medium">تعداد</label>
          <input v-model.number="form.count" type="number" class="w-full border p-2 rounded" />
        </div>
      </div>
      <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
        ➕ افزودن به انبار
      </button>
    </form>

    <!-- فیلتر تاریخ -->
    <div class="flex gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium mb-1">از تاریخ:</label>
        <input type="date" v-model="startDate" class="border p-2 rounded" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">تا تاریخ:</label>
        <input type="date" v-model="endDate" class="border p-2 rounded" />
      </div>
    </div>

    <!-- جمع کل -->
    <p class="text-right mb-2 text-sm text-gray-700">🔢 مجموع موجودی: {{ totalCount }}</p>

    <!-- جدول نمایش کالاها -->
    <table class="w-full table-auto border text-sm">
      <thead>
        <tr class="bg-gray-200 text-right">
          <th class="px-2 py-1">بخش</th>
          <th class="px-2 py-1">کد</th>
          <th class="px-2 py-1">تعداد</th>
          <th class="px-2 py-1">تاریخ</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in filteredItems" :key="item.id">
          <td class="px-2 py-1">{{ item.section }}</td>
          <td class="px-2 py-1">{{ item.code }}</td>
          <td class="px-2 py-1">{{ item.count }}</td>
          <td class="px-2 py-1">{{ new Date(item.createdAt?.seconds * 1000).toLocaleDateString('fa-IR') }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { db } from '@/firebase'
import { collection, addDoc, onSnapshot, serverTimestamp } from 'firebase/firestore'

export default {
  name: "WarehousePage",
  setup() {
    const form = ref({ section: '', code: '', count: null })
    const items = ref([])
    const startDate = ref('')
    const endDate = ref('')

    const addItem = async () => {
      try {
        await addDoc(collection(db, 'warehouse'), {
          ...form.value,
          createdAt: serverTimestamp()
        })
        alert('✅ با موفقیت به انبار اضافه شد!')
        form.value = { section: '', code: '', count: null }
      } catch (err) {
        console.error(err)
        alert('❌ خطا در افزودن کالا')
      }
    }

    onMounted(() => {
      const q = collection(db, 'warehouse')
      onSnapshot(q, (snapshot) => {
        items.value = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }))
      })
    })

    const filteredItems = computed(() => {
      return items.value.filter((item) => {
        if (!item.createdAt) return false
        const itemDate = new Date(item.createdAt.seconds * 1000)
        const from = startDate.value ? new Date(startDate.value) : null
        const to = endDate.value ? new Date(endDate.value) : null

        if (from && itemDate < from) return false
        if (to && itemDate > to) return false

        return true
      })
    })

    const totalCount = computed(() => {
      return filteredItems.value.reduce((sum, item) => sum + (item.count || 0), 0)
    })

    return {
      form,
      items,
      startDate,
      endDate,
      addItem,
      filteredItems,
      totalCount
    }
  }
}
</script>


