<template>
  <AppLayout>
    <div class="p-6">
      <h1 class="text-xl font-bold mb-4">🖼️ گالری محصولات</h1>
<div class="my-6 bg-white p-4 rounded shadow">
  <h2 class="text-lg font-semibold mb-4">📊 توزیع محصولات بر اساس دسته‌بندی</h2>
  <Pie :data="pieChartData" :options="pieChartOptions" />
</div>
<p class="text-sm text-gray-600 mb-2">
  🔍 تعداد نتایج فیلترشده: {{ filteredCount }}
</p>

<div class="mb-4 flex items-center gap-4">
  <label class="font-medium">👁 نوع نمایش:</label>
  <select v-model="viewMode" class="border p-1 rounded">
    <option value="grid">🟩 نمای گرید</option>
    <option value="list">📄 نمای لیست</option>
  </select>
</div>

<div class="mb-4">
  <label class="block mb-1 font-medium">🧮 مرتب‌سازی:</label>
  <select v-model="sortOption" class="border p-2 rounded w-full">
    <option value="newest">جدیدترین</option>
    <option value="oldest">قدیمی‌ترین</option>
    <option value="title">مرتب‌سازی بر اساس نام</option>
  </select>
</div>
<div v-if="selectedCategories.length" class="text-sm text-gray-600 mb-4">
  <p class="font-semibold mb-1">📌 دسته‌های فعال:</p>
  <ul class="list-disc pr-4">
    <li v-for="cat in selectedCategories" :key="cat">
      {{ cat }} ({{ countInCategory(cat) }})
    </li>
  </ul>
</div>

<div class="mb-4">
  <label class="block mb-1 font-medium">🔽 مرتب‌سازی بر اساس:</label>
  <select v-model="sortOption" class="border p-2 rounded w-full">
    <option value="newest">جدیدترین</option>
    <option value="oldest">قدیمی‌ترین</option>
    <option value="title">عنوان (الف-ی)</option>
  </select>
</div>

<div class="mt-10 bg-white shadow rounded p-4">
  <h2 class="text-lg font-bold mb-4">🕒 تاریخچه آپلود تصاویر</h2>
  <ul class="text-sm space-y-2">
    <li v-for="item in images" :key="item.id" class="border-b pb-1">
      📌 {{ item.title }} | 📁 {{ item.category || '—' }} | 🕒 {{ formatDate(item.createdAt) }}
    </li>
  </ul>
</div>

<div id="chart-section" class="bg-white p-4 rounded shadow mt-4">
  <Pie :data="pieChartData" :options="pieChartOptions" />
</div>

      <form @submit.prevent="uploadImage" class="bg-white p-4 rounded shadow mb-6 space-y-4">
        <div>
  <label class="block mb-1 font-medium">👷‍♀️ نام خیاط:</label>
  <input v-model="newImage.worker" class="border p-2 rounded w-full" placeholder="مثلاً نرگس" />
</div>

<div>
  <label class="block mb-1 font-medium">🏷️ دسته‌بندی:</label>
  <input v-model="newImage.category" class="border p-2 rounded w-full" placeholder="مثلاً شلوار" />
</div>

        <div>
          <label class="block mb-1 font-medium">📌 نام محصول:</label>
          <input v-model="title" class="border p-2 rounded w-full" required />
        </div>
        <div class="bg-white shadow rounded p-4">
  <p class="text-gray-500 text-sm mb-1">📦 تعداد محصولات ثبت‌شده</p>
  <p class="text-xl font-bold">{{ productCount }}</p>
</div>
<div class="bg-white shadow rounded p-4 mt-4">
  <p class="text-gray-500 text-sm mb-1">🧮 حجم کلی تصاویر آپلودشده</p>
  <p class="text-xl font-bold">{{ totalSizeMB }} مگابایت</p>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
  <h2 class="text-lg font-bold mb-2">📊 نمودار میله‌ای دسته‌بندی‌ها</h2>
  <Bar :data="categoryChartData" :options="categoryChartOptions" />
</div>

<div id="bar-chart-section" class="bg-white rounded shadow p-4 mb-6">
  <h2 class="text-lg font-bold mb-2">📊 نمودار میله‌ای دسته‌بندی‌ها</h2>
  <Bar :data="categoryChartData" :options="categoryChartOptions" />
</div>

<div class="text-sm text-gray-600 mt-4">
  <h3 class="font-semibold mb-1">📈 درصد سهم هر دسته:</h3>
  <ul class="list-disc pr-4">
    <li v-for="(p, i) in categoryPercents" :key="i">{{ p }}</li>
  </ul>
</div>

<p
  class="text-xs font-bold mt-1"
  :class="{
    'text-red-600': item.category === 'مانتو',
    'text-blue-600': item.category === 'پالتو',
    'text-green-600': item.category === 'شلوار',
    'text-yellow-600': item.category === 'شومیز',
    'text-purple-600': item.category === 'کت',
  }"
>
  📁 دسته: {{ item.category || '—' }}
</p>


<div class="bg-white shadow rounded p-4">
  <p class="text-gray-500 text-sm mb-1">📁 دسته‌بندی‌های موجود</p>
  <p class="text-xl font-bold">{{ categoryCount }}</p>
</div>

<div class="bg-white rounded shadow p-4 mb-6">
  <h2 class="text-lg font-semibold mb-2">📊 نمودار دسته‌بندی محصولات</h2>
  <Pie :data="chartData" />
</div>
<div class="my-8 bg-white shadow rounded p-4">
  <h2 class="text-lg font-bold mb-4">📊 نمودار محصولات بر اساس دسته‌بندی</h2>
  <Pie :data="pieChartData" />
</div>

<div v-if="selectedCategories.length" class="mt-4 text-sm text-gray-700">
  <span class="font-semibold">دسته‌های انتخاب‌شده:</span>
  <span class="ml-2" v-for="(cat, i) in selectedCategories" :key="i">
    {{ cat }} <span v-if="i !== selectedCategories.length - 1">|</span>
  </span>
</div>

<div class="flex flex-wrap gap-4">
  <label v-for="cat in ['مانتو', 'پالتو', 'شلوار', 'شومیز', 'کت']" :key="cat" class="flex items-center gap-2">
    <input
      type="checkbox"
      :value="cat"
      v-model="selectedCategories"
      class="form-checkbox text-blue-600"
    />
    <span>{{ cat }}</span>
  </label>
</div>

<div class="mb-4">
  <label class="block mb-1 font-medium">📁 فیلتر بر اساس دسته‌بندی:</label>
  <select v-model="selectedCategories" multiple class="border p-2 rounded w-full" size="5">
    <option>مانتو</option>
    <option>پالتو</option>
    <option>شلوار</option>
    <option>شومیز</option>
    <option>کت</option>
  </select>
</div>

<div class="mt-8 bg-white p-4 shadow rounded">
  <h2 class="text-lg font-bold mb-4">🥧 سهم دسته‌بندی‌ها</h2>
  <Pie :data="pieChartData" :options="pieChartOptions" />
</div>
<div class="bg-white rounded shadow p-4 mb-6">
  <h2 class="text-lg font-bold mb-2">📊 نمودار دسته‌بندی محصولات</h2>
  <Pie :data="pieChartData" :options="pieChartOptions" />
</div>


        <div>
          <label class="block mb-1 font-medium">📷 انتخاب تصویر:</label>
          <input type="file" multiple @change="onFileChange" accept="image/*" class="border p-2 rounded w-full" required />
        </div>


        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
          ⬆️ آپلود
        </button>
      </form>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-6">
  <div v-for="item in filteredImages" :key="item.id" class="border p-2 rounded shadow">
    <img
  v-lazy="item.url"
  :alt="item.title"
  class="w-full h-48 object-cover mb-2 rounded"
/>
    <p class="font-semibold">{{ item.title }}</p>
    <p class="text-xs text-gray-500 mt-1">📅 زمان بارگذاری: {{ formatDate(item.createdAt) }}</p>
    <p class="text-xs text-gray-600">👷‍♀️ خیاط: {{ item.worker || '—' }}</p>
    <p class="text-xs text-gray-600">🏷️ دسته‌بندی: {{ item.category || '—' }}</p>
  </div>
</div>

      <div class="mb-4">
  <label class="block mb-1 font-medium">🔍 جستجو در محصولات:</label>
  <input
    v-model="searchQuery"
    type="text"
    placeholder="مثلاً مانتو"
    class="border p-2 rounded w-full"
  />
</div>

<div class="mb-4">
  <label class="block mb-1 font-medium">📁 فیلتر بر اساس دسته‌بندی:</label>
  <select v-model="selectedCategories" multiple class="border p-2 rounded w-full">
    <option value="مانتو">مانتو</option>
    <option value="پالتو">پالتو</option>
    <option value="شلوار">شلوار</option>
    <option value="شومیز">شومیز</option>
    <option value="کت">کت</option>
  </select>
  <p class="text-xs text-gray-500 mt-1">برای انتخاب چند دسته، از Ctrl یا Shift استفاده کنید.</p>
</div>



<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

        <button @click="exportBarChartAsPDF" class="mt-4 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
          📄 دریافت PDF نمودار میله‌ای
        </button>

        <button @click="resetFilters" class="mt-2 text-sm text-blue-600 hover:underline">
          ♻️ بازنشانی فیلترها و مرتب‌سازی
        </button>

        <button @click="exportFilteredImagesAsPDF" class="mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
          📄 دریافت PDF تصاویر فیلترشده
        </button>

<template v-if="editingId === item.id">
  <input v-model="editingTitle" class="border p-1 rounded w-full text-sm mb-2" />
  
  <select v-model="editingCategory" class="border p-1 rounded w-full text-sm">
    <option value="">بدون دسته‌بندی</option>
    <option value="مانتو">مانتو</option>
    <option value="پالتو">پالتو</option>
    <option value="شلوار">شلوار</option>
    <option value="شومیز">شومیز</option>
    <option value="کت">کت</option>
  </select>

  <div class="mt-2 flex gap-2">
    <button @click="saveTitle(item)" class="text-green-600 hover:underline text-xs">✅ ذخیره</button>
    <button @click="cancelEdit" class="text-gray-500 hover:underline text-xs">✖️ لغو</button>
  </div>
</template>

<div v-else>
  <p class="font-semibold">{{ item.title }}</p>
  <button
    @click="startEdit(item)"
    class="ml-2 text-blue-600 hover:underline text-xs"
  >✏️</button>
  <p class="text-xs text-gray-500 mt-1">📅 زمان بارگذاری: {{ formatDate(item.createdAt) }}</p>
  <p class="text-xs text-gray-600">📁 دسته: {{ item.category || '—' }}</p>
</div>

    </div>

    <button
      @click="deleteImage(item)"
      class="mt-2 text-sm text-red-600 hover:underline block mx-auto"
    >
      🗑 حذف تصویر
    </button>
  </div>
  </AppLayout>
</template>

<script setup>
import { Chart as ChartJS, Title, Tooltip, Legend, ArcElement, BarElement, CategoryScale, LinearScale } from 'chart.js'
import { Pie, Bar } from 'vue-chartjs'
import { ref, computed } from 'vue'
import { db, storage } from '@/firebase'
import { collection, addDoc, getDocs, deleteDoc, doc, updateDoc, serverTimestamp } from 'firebase/firestore'
import { ref as storageRef, uploadBytes, getDownloadURL, deleteObject } from 'firebase/storage'
import html2canvas from 'html2canvas'
import jsPDF from 'jspdf'


ChartJS.register(BarElement, CategoryScale, LinearScale)
ChartJS.register(Title, Tooltip, Legend, ArcElement)
const productCount = ref(0)
const categoryCount = ref(0)
const title = ref('')
const files = ref([])
const images = ref([])
const searchQuery = ref('')
const editingId = ref(null)
const editingTitle = ref('')
const category = ref('')
const categoryPercents = ref([])
const totalSizeMB = ref(0)
const sortOption = ref('newest')
const selectedCategories = ref([])
const editingCategory = ref(null)


const categoryCounts = {}
for (const img of images.value) {
  const cat = img.category || 'نامشخص'
  categoryCounts[cat] = (categoryCounts[cat] || 0) + 1
}

pieChartData.value.labels = Object.keys(categoryCounts)
pieChartData.value.datasets[0].data = Object.values(categoryCounts)

const pieChartData = ref({
  labels: [],
  datasets: [
    {
      label: 'محصولات',
      backgroundColor: ['#f87171', '#60a5fa', '#34d399', '#fbbf24', '#a78bfa'],
      data: []
    }
  ]
})
// eslint-disable-next-line no-unused-vars
const barChartData = ref({
  labels: [],
  datasets: [{
    label: 'تعداد محصولات در هر دسته',
    backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa'],
    data: []
  }]
})
// eslint-disable-next-line no-unused-vars
const barChartOptions = ref({
  responsive: true,
  plugins: {
    legend: { display: false },
    title: {
      display: true,
      text: 'نمودار ستونی تعداد محصولات'
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
})

const chartData = ref({
  labels: [],
  datasets: [
    {
      label: 'توزیع دسته‌بندی‌ها',
      backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
      data: []
    }
  ]
})

const exportBarChartAsPDF = async () => {
  const chartEl = document.getElementById('bar-chart-section')
  if (!chartEl) return

  const canvas = await html2canvas(chartEl)
  const imgData = canvas.toDataURL('image/png')
  const pdf = new jsPDF()

  const imgProps = pdf.getImageProperties(imgData)
  const pdfWidth = pdf.internal.pageSize.getWidth()
  const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width

  pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight)
  pdf.save('bar-chart.pdf')
}
// eslint-disable-next-line no-unused-vars
const exportChartAsPDF = async () => {
  const chartEl = document.getElementById('chart-section')
  if (!chartEl) return

  const canvas = await html2canvas(chartEl)
  const imgData = canvas.toDataURL('image/png')
  const pdf = new jsPDF({
    orientation: 'portrait',
    unit: 'mm',
    format: 'a4'
  })

  const imgProps = pdf.getImageProperties(imgData)
  const pdfWidth = pdf.internal.pageSize.getWidth()
  const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width

  pdf.text(`تاریخ گزارش: ${new Date().toLocaleString('fa-IR')}`, 10, 10)
  pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight)
  pdf.save('product-chart.pdf')
}

const viewMode = ref('grid') // مقدار اولیه گرید

const exportFilteredImagesAsPDF = async () => {
  const pdf = new jsPDF()
  let y = 10

  for (const img of filteredImages.value) {
    const imgEl = new Image()
    imgEl.crossOrigin = 'anonymous'
    imgEl.src = img.url

    await new Promise(resolve => {
      imgEl.onload = () => {
        const ratio = imgEl.width / imgEl.height
        const width = 60
        const height = width / ratio

        if (y + height > 280) {
          pdf.addPage()
          y = 10
        }

        pdf.text(img.title || 'بدون عنوان', 10, y)
        pdf.addImage(imgEl, 'JPEG', 10, y + 5, width, height)
        y += height + 20

        resolve()
      }
    })
  }

  pdf.save('filtered-images.pdf')
}

const pieChartOptions = ref({
  responsive: true,
  plugins: {
    legend: { position: 'right' }
  }
})

const categoryChartData = ref({
  labels: [],
  datasets: [{
    data: [],
    backgroundColor: ['#60a5fa', '#f87171', '#34d399', '#fbbf24', '#a78bfa']
  }]
})

const categoryChartOptions = ref({
  responsive: true,
  plugins: {
    legend: { position: 'bottom' }
  }
})

const startEdit = (item) => {
  editingId.value = item.id
  editingTitle.value = item.title
  editingCategory.value = item.category || null
}

const cancelEdit = () => {
  editingId.value = null
  editingTitle.value = ''
}


const saveTitle = async (item) => {
  const docRef = doc(db, 'products', item.id)
  await updateDoc(docRef, {
    title: editingTitle.value,
    category: editingCategory.value || null
  })

  // آپدیت دستی روی آیتم لوکالی
  item.title = editingTitle.value
  item.category = editingCategory.value
  cancelEdit()
}


const onFileChange = (e) => {
  files.value = Array.from(e.target.files)
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategories.value = []
  sortOption.value = 'newest'
}

const filteredImages = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  const cats = selectedCategories.value

  let result = images.value.filter(img => {
    const matchesTitle = img.title.toLowerCase().includes(q)
    const matchesCategory = cats.length === 0 || cats.includes(img.category)
    return matchesTitle && matchesCategory
  })

  // مرتب‌سازی بر اساس گزینه انتخاب‌شده
  if (sortOption.value === 'newest') {
    result = result.sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0))
  } else if (sortOption.value === 'oldest') {
    result = result.sort((a, b) => (a.createdAt?.seconds || 0) - (b.createdAt?.seconds || 0))
  } else if (sortOption.value === 'title') {
    result = result.sort((a, b) => a.title.localeCompare(b.title))
  }

  return result
})


const formatDate = (ts) => {
  if (!ts || !ts.seconds) return '—'
  const date = new Date(ts.seconds * 1000)
  return date.toLocaleDateString('fa-IR') + ' - ' + date.toLocaleTimeString('fa-IR')
}

const filteredCount = computed(() => filteredImages.value.length)


const countInCategory = (cat) => {
  return images.value.filter(img => img.category === cat).length
}

const deleteImage = async (image) => {
  const confirmDelete = confirm(`آیا از حذف "${image.title}" مطمئن هستی؟`)
  if (!confirmDelete) return

  try {
    const imagePath = decodeURIComponent(image.imageUrl.split('/o/')[1].split('?')[0])

    const fileRef = storageRef(storage, imagePath)

    await deleteObject(fileRef)
    await deleteDoc(doc(db, 'productImages', image.id))

    images.value = images.value.filter(img => img.id !== image.id)

    alert('✅ تصویر با موفقیت حذف شد.')
  } catch (err) {
    console.error(err)
    alert('❌ خطا در حذف تصویر.')
  }
}

// eslint-disable-next-line no-unused-vars
const getStoragePathFromURL = (url) => {
  const base = 'https://firebasestorage.googleapis.com/v0/b/'
  const pathStart = url.indexOf(base)
  if (pathStart === -1) return null

  const path = decodeURIComponent(
    url
      .replace(base, '')
      .split('?')[0]
      .split('/')
      .slice(1)
      .join('/')
  )
  return path
}

const newImage = ref({
  worker: '',
  category: ''
})

const uploadImage = async () => {
  if (!newImage.value.category || !newImage.value.worker) {
    alert('❗ لطفاً نام خیاط و دسته‌بندی را وارد کن.')
    return
  }

  if (!files.value.length || !title.value) return

  const isDuplicate = images.value.some(
    img => img.title.trim().toLowerCase() === title.value.trim().toLowerCase()
  )
  if (isDuplicate) {
    alert('⚠️ عنوان تکراری است. لطفاً یک نام متفاوت وارد کن.')
    return
  }

  for (const file of files.value) {
    const imgRef = storageRef(storage, `products/${Date.now()}-${file.name}`)
    await uploadBytes(imgRef, file)
    const imageUrl = await getDownloadURL(imgRef)

    const data = {
      title: title.value,
      url: imageUrl,
      category: newImage.value.category,
      worker: newImage.value.worker,
      createdAt: serverTimestamp()
    }

    // ⬅️ ذخیره در کالکشن اصلی
    await addDoc(collection(db, 'products'), data)

    // ⬅️ ذخیره در کالکشن اضافی productImages
    await addDoc(collection(db, 'productImages'), {
      imageUrl,
      title: data.title,
      worker: data.worker,
      category: data.category,
      createdAt: data.createdAt
    })
  }

  title.value = ''
  files.value = []
  category.value = ''
  newImage.value.category = ''
  newImage.value.worker = ''
  alert('✅ تصاویر با موفقیت آپلود شدند.')

  const totalBytes = files.value.reduce((sum, file) => sum + file.size, 0)
  totalSizeMB.value = (totalBytes / 1024 / 1024).toFixed(2)

  fetchImages()
}




const fetchImages = async () => {
  const snap = await getDocs(collection(db, 'products'))
  const products = snap.docs
    .map(doc => ({ id: doc.id, ...doc.data() }))
    .sort((a, b) => (b.createdAt?.seconds || 0) - (a.createdAt?.seconds || 0)) // جدیدترین اول

  images.value = products
  productCount.value = products.length
  categoryCount.value = new Set(products.map(p => p.category)).size

  const categoryTotals = {}
products.forEach(p => {
  const cat = p.category || 'نامشخص'
  categoryTotals[cat] = (categoryTotals[cat] || 0) + 1
})

pieChartData.value.labels = Object.keys(categoryTotals)
pieChartData.value.datasets[0].data = Object.values(categoryTotals)

  // نمودار دایره‌ای: تعداد محصولات در هر دسته
  const counts = {}
  products.forEach(p => {
    const cat = p.category || 'نامشخص'
    counts[cat] = (counts[cat] || 0) + 1
  })
  chartData.value.labels = Object.keys(counts)
  chartData.value.datasets[0].data = Object.values(counts)

  pieChartData.value.labels = Object.keys(counts)
  pieChartData.value.datasets[0].data = Object.values(counts)

  categoryChartData.value.labels = Object.keys(counts)
  categoryChartData.value.datasets[0].data = Object.values(counts)

  const total = products.length
const percentages = Object.entries(counts).map(([cat, count]) => {
  const percent = ((count / total) * 100).toFixed(1)
  return `${cat}: ${percent}%`
})
categoryPercents.value = percentages

}

</script>
