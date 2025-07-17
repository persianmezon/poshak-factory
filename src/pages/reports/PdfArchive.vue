<template>
  <div class="p-6">
    <h1 class="text-xl font-bold mb-4">📁 آرشیو گزارش‌های PDF</h1>

    <input
      v-model="searchQuery"
      placeholder="🔍 جستجو در تاریخ یا نام فایل"
      class="border px-3 py-2 rounded mb-4 w-full"
    />

    <ul class="space-y-2">
      <li
        v-for="file in filteredPdfUrls"
        :key="file.url"
        class="flex items-center justify-between bg-gray-50 px-4 py-2 rounded shadow-sm"
      >
        <a
          :href="file.url"
          target="_blank"
          class="text-blue-600 hover:underline text-sm font-medium"
        >
          {{ formatFilename(file.name) }}
        </a>
        <button
          @click="deleteFile(file.url)"
          class="text-red-600 text-sm hover:underline"
        >
          🗑 حذف
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import {
  getStorage,
  ref as storageRef,
  listAll,
  getDownloadURL,
  deleteObject
} from 'firebase/storage'

// لیست فایل‌ها و جستجو
const pdfUrls = ref([])
const searchQuery = ref('')

// نمایش فیلتر شده با جستجو
const filteredPdfUrls = computed(() =>
  pdfUrls.value.filter(file =>
    formatFilename(file.name).toLowerCase().includes(searchQuery.value.toLowerCase())
  )
)

// فرمت‌سازی اسم فایل
const formatFilename = (name) => {
  try {
    const parts = name.replace('.pdf', '').split('-')
    const date = new Date(Number(parts[1]))
    return `📄 ${date.toLocaleDateString('fa-IR')} - گزارش`
  } catch {
    return name
  }
}

// حذف فایل
const deleteFile = async (url) => {
  const storage = getStorage()
  const path = decodeURIComponent(url.split('/o/')[1].split('?')[0])
  const fileRef = storageRef(storage, path)

  try {
    await deleteObject(fileRef)
    pdfUrls.value = pdfUrls.value.filter(file => file.url !== url)
    alert('✅ فایل حذف شد')
  } catch (err) {
    console.error(err)
    alert('❌ خطا در حذف فایل')
  }
}

// واکشی فایل‌ها
onMounted(async () => {
  const storage = getStorage()
  const folderRef = storageRef(storage, 'pdf-archive')

  try {
    const res = await listAll(folderRef)
    const files = await Promise.all(
      res.items.map(async (item) => {
        const url = await getDownloadURL(item)
        return {
          name: item.name,
          url
        }
      })
    )
    pdfUrls.value = files
  } catch (err) {
    console.error('خطا در واکشی فایل‌ها:', err)
  }
})
</script>

