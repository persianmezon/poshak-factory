<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">📦 مدیریت انبار (QR ثبت‌شده‌ها)</h1>

    <!-- فرم افزودن یا ویرایش کالا -->
    <form @submit.prevent="handleSubmit" class="bg-white shadow p-4 rounded mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
          <label class="block mb-1 font-medium">قسمت</label>
          <input v-model="form.part" class="w-full border p-2 rounded" placeholder="دلخواه" />
        </div>
        <div>
          <label class="block mb-1 font-medium">کد</label>
          <input v-model="form.code" class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block mb-1 font-medium">تعداد</label>
          <input v-model.number="form.count" type="number" class="w-full border p-2 rounded" />
        </div>
      </div>
      <button type="submit" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded">
        {{ form.id ? '✏️ ویرایش کالا' : '➕ افزودن به انبار' }}
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

    <!-- مجموع -->
    <p class="text-right mb-2 text-sm text-gray-700">🔢 مجموع قطعات: {{ totalCount }}</p>


    <!-- کارت‌های آماری -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">🔢 مجموع کالاها</p>
    <p class="text-xl font-bold text-blue-600">{{ totalItems }}</p>
  </div>
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">📋 تعداد ردیف‌ها</p>
    <p class="text-xl font-bold text-green-600">{{ totalRecords }}</p>
  </div>
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">✂️ برش</p>
    <p class="text-xl font-bold text-purple-600">{{ statsBySection['برش'] }}</p>
  </div>
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">🧵 دوخت</p>
    <p class="text-xl font-bold text-pink-600">{{ statsBySection['دوخت'] }}</p>
  </div>
  <div class="bg-white shadow rounded p-4 text-center">
    <p class="text-sm text-gray-600">🎯 نهایی‌کار</p>
    <p class="text-xl font-bold text-yellow-600">{{ statsBySection['نهایی‌کار'] }}</p>
  </div>
</div>


<button
  @click="exportToExcel"
  class="bg-green-700 text-white px-4 py-2 rounded shadow mb-4 hover:bg-green-800"
>
  📥 خروجی اکسل
</button>

<p class="text-sm text-gray-600">
  📅 تاریخ انتخاب‌شده (میلادی): {{ startDate || '---' }} 
  | شمسی: {{ selectedDateJalali }}
</p>



    <!-- جدول -->
<table class="w-full text-sm text-right border border-gray-300 shadow-sm rounded overflow-hidden">
<thead class="bg-gray-200">
  <tr>
    <th class="border border-gray-300 px-2 py-2">بخش</th>
    <th class="border border-gray-300 px-2 py-2">قسمت</th>
    <th class="border border-gray-300 px-2 py-2">کد</th>
    <th class="border border-gray-300 px-2 py-2">تعداد</th>
    <th class="border border-gray-300 px-2 py-2">کارگر</th>
    <th class="border border-gray-300 px-2 py-2">تاریخ</th>
    <th class="border border-gray-300 px-2 py-2">عملیات</th>
  </tr>
</thead>
<tbody>
  <tr
    v-for="item in filteredItems"
    :key="item.id"
    class="border border-gray-200 odd:bg-white even:bg-gray-50 hover:bg-gray-100 transition"
  >
    <td class="border border-gray-300 px-2 py-2">{{ item.section }}</td>
    <td class="border border-gray-300 px-2 py-2">{{ item.part || '-' }}</td>
    <td class="border border-gray-300 px-2 py-2">{{ item.code }}</td>
    <td class="border border-gray-300 px-2 py-2">{{ item.count }}</td>
    <td class="border border-gray-300 px-2 py-2">{{ getWorkerName(item.workerId) }}</td>
    <td class="border border-gray-300 px-2 py-2">
      {{ formatDate(item.createdAt) }}
    </td>
<td class="border border-gray-300 px-2 py-2">
  <!-- آیکن‌ها مخفی شدند -->
</td>

  </tr>
</tbody>
</table>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import * as XLSX from 'xlsx'
import { saveAs } from 'file-saver'
import moment from 'moment-jalaali'
moment.loadPersian({ usePersianDigits: true })

export default {
  name: "WarehousePage",
  setup() {
    const items = ref([])
    const startDate = ref('')
    const endDate = ref('')

    const workers = ref([])

    const form = ref({
      id: null,
      section: '',
      part: '',
      code: '',
      count: null
    })

// eslint-disable-next-line no-unused-vars
const getWorkerName = (uid) => {
  if (!uid) return '---'
  const worker = workers.value.find(w => w.uid === uid)
  return worker ? worker.name : '---'
}



const selectedDateJalali = computed(() => {
  const from = startDate.value
  return from ? moment(from).format('jYYYY/jMM/jDD') : ''
})

const fetchItems = async () => {
  try {
    const res = await fetch('/api/get-scans.php?t=' + Date.now()) // جلوگیری از کش شدن پاسخ
    const json = await res.json()
    if (json.success) {
      items.value = json.records.map(r => {
        if (r.createdAt && typeof r.createdAt === 'number') {
          r.createdAt = new Date(r.createdAt * 1000) // تبدیل timestamp به Date
        }
        return r
      })
    }
  } catch (err) {
    console.error('❌ خطا در دریافت داده‌ها:', err)
  }
}



    const totalItems = computed(() => filteredItems.value.reduce((sum, item) => sum + (item.count || 0), 0))
const totalRecords = computed(() => filteredItems.value.length)

const statsBySection = computed(() => {
  const stats = { برش: 0, دوخت: 0, 'نهایی‌کار': 0 }
  filteredItems.value.forEach(item => {
    if (item.section && stats[item.section] !== undefined) {
      stats[item.section] += item.count || 0
    }
  })
  return stats
})

const exportToExcel = () => {
  const data = filteredItems.value.map(item => {
    let dateStr = '-'

    if (item.createdAt?.seconds) {
      // حالت Firebase timestamp
      dateStr = new Date(item.createdAt.seconds * 1000).toLocaleDateString('fa-IR')
    } else if (item.createdAt instanceof Date) {
      // حالت Date معمولی
      dateStr = item.createdAt.toLocaleDateString('fa-IR')
    }

    return {
      بخش: item.section || '-',
      قسمت: item.part || '---',
      کد: item.code || '-',
      تعداد: item.count || 0,
      تاریخ: dateStr
    }
  })

  const worksheet = XLSX.utils.json_to_sheet(data)
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Warehouse')

  const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' })
  const blob = new Blob([excelBuffer], { type: 'application/octet-stream' })

  saveAs(blob, `warehouse-export-${new Date().toISOString().slice(0, 10)}.xlsx`)
}



    const handleSubmit = async () => {
      try {
        if (form.value.id) {
          // ویرایش
          const res = await fetch('/api/update-scan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(form.value)
          })
          const json = await res.json()
          if (json.success) {
            alert('✅ با موفقیت ویرایش شد')
            form.value = { id: null, section: '', part: '', code: '', count: null }
            fetchItems()
          }
        } else {
          alert('❌ امکان افزودن مستقیم در این بخش وجود ندارد. (فقط ویرایش رکوردهای ثبت‌شده QR)')
        }
      } catch (err) {
        console.error('❌ خطا در ذخیره:', err)
      }
    }

    const editItem = (item) => {
      form.value = {
        id: item.id,
        section: item.section || '',
        part: item.part || '',
        code: item.code || '',
        count: item.count || null
      }
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
const deleteRecord = async (id) => {
  const confirmed = confirm('آیا مطمئن هستید که می‌خواهید این رکورد را حذف کنید؟')
  if (!confirmed) return

  try {
    const res = await fetch('/api/delete-scan.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    const json = await res.json()
    if (json.success) {
      alert('✅ با موفقیت حذف شد')
      // به‌روزرسانی لیست
      items.value = items.value.filter(item => item.id !== id)
    } else {
      alert('❌ خطا در حذف: ' + json.message)
    }
  } catch (err) {
    console.error('خطا در حذف:', err)
    alert('⛔ خطا در ارتباط با سرور')
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  if (date.seconds) return new Date(date.seconds * 1000).toLocaleDateString('fa-IR')
  if (date instanceof Date) return date.toLocaleDateString('fa-IR')
  try {
    return new Date(date).toLocaleDateString('fa-IR')
  } catch {
    return '-'
  }
}


    const filteredItems = computed(() => {
      return items.value.filter(item => {
        if (!item.createdAt) return false
        const itemDate = new Date(item.createdAt)
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

const fetchWorkers = async () => {
  try {
    const res = await fetch('/api/get-workers.php?t=' + Date.now())
    const json = await res.json()
    if (json.success) {
      workers.value = json.workers
    }
  } catch (err) {
    console.error('❌ خطا در دریافت کارگرها:', err)
  }
}

onMounted(() => {
  fetchItems()
  fetchWorkers()
})

    return {
      form,
      startDate,
      endDate,
      items,
      filteredItems,
      totalCount,
      handleSubmit,
      editItem,
      totalItems,
      totalRecords,
      exportToExcel,
      formatDate,
      statsBySection,
      getWorkerName,
      workers,
      deleteRecord,
      selectedDateJalali
    }
  }
}
</script>



