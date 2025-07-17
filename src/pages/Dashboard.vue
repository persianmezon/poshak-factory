<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div>
    <!-- Header جدید -->
     

    <!-- ساختار اصلی داشبورد -->
    <div class="flex min-h-screen bg-gray-100 text-gray-800">

      <!-- Main Content -->
      <div class="flex-1 p-6">
        <!-- Header داخل داشبورد -->
<header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-3">
  <!-- نقش کاربر -->
  <div class="flex items-center gap-3">
    <span class="text-gray-500 text-sm">👤 نقش شما:</span>
    <span class="text-sm font-medium text-gray-700">{{ userRole }}</span>
  </div>

  <!-- تاریخ انتخابی با نمایش شمسی -->
<div class="flex items-center gap-2">
  <input
    type="date"
    v-model="selectedDate"
    class="border border-gray-300 rounded px-3 py-1 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
  />
  <span class="text-gray-600 text-sm">({{ selectedDateJalali }})</span>
</div>
</header>

         


<!-- کارت‌های آماری + نمودار دایره‌ای کنار هم -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 items-start">
  <!-- کارت‌ها: دو ردیف، هر ردیف ۳ تا -->
  <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 col-span-2">
    <DashboardCard icon="💼" title="برش" value="64" change="-5%" />
    <DashboardCard icon="👤" title="خدمات اتو" value="32" change="+12%" />
    <DashboardCard icon="🏢" title="زیگزاگ" value="22" change="+5%" />
    <DashboardCard icon="⏱️" title="تکمیل" value="21" change="-25%" />
    <DashboardCard icon="⏱️" title="آستری" value="11" change="+12%" />
    <DashboardCard icon="✉️" title="لایی" value="87" change="+11%" />
  </div>

  <!-- نمودار دایره‌ای -->
  <div class="bg-white p-6 rounded shadow h-full">
    <EfficiencyChart :workers="workers" />
  </div>
</div>


        <!-- فیلتر ورودی -->
        <input
          v-model="search"
          type="text"
          placeholder="جستجو نام کارگر..."
          class="border p-2 rounded mb-4 w-full md:w-1/3"
        />

        <!-- جدول کارگرها -->
        <table class="w-full text-right">
          <thead>
            <tr class="text-gray-500 border-b">
              <th class="py-2">کارگر</th>
              <th @click="sortBy('done')" class="cursor-pointer">✅ انجام‌شده</th>
              <th @click="sortBy('remaining')" class="cursor-pointer">⌛ مانده</th>
              <th>کل</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="worker in filteredWorkers"
              :key="worker.name"
              class="border-b hover:bg-gray-50"
            >
              <td class="py-2 flex items-center gap-2">
                <img :src="worker.avatar" class="w-8 h-8 rounded-full" />
                <span>{{ worker.name }}</span>
              </td>
              <td>{{ worker.done }}</td>
              <td>{{ worker.remaining }}</td>
              <td>{{ worker.done + worker.remaining }}</td>
            </tr>
          </tbody>
        </table>

        <!-- نمودار عملکرد -->
        <div class="bg-white p-6 rounded shadow mt-8">
          <h2 class="text-xl font-bold mb-4">آمار کلی</h2>
          <WorkerPerformanceChart />
        </div>

      
      </div>
    </div>
  </div>
</template>


<script>

import DashboardCard from '@/components/DashboardCard.vue'
import WorkerPerformanceChart from '@/components/charts/WorkerPerformanceChart.vue'
import EfficiencyChart from '@/components/charts/EfficiencyChart.vue'
import dayjs from 'dayjs'
import jalaliday from 'jalaliday'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.extend(jalaliday)


export default {
  components: {
    DashboardCard,
    WorkerPerformanceChart,
    EfficiencyChart
  },
  data() {
    return {
      userRole: localStorage.getItem('userRole') || 'ناشناس',
      search: '',
      sortKey: '',
      sortAsc: true,
      selectedDate: new Date().toISOString().slice(0, 10),
      workers: [
        {
          name: 'رضا رضایی',
          avatar: 'https://i.pravatar.cc/100?img=1',
          done: 20,
          remaining: 5
        },
        {
          name: 'سارا احمدی',
          avatar: 'https://i.pravatar.cc/100?img=2',
          done: 15,
          remaining: 3
        },
        {
          name: 'علی کریمی',
          avatar: 'https://i.pravatar.cc/100?img=3',
          done: 30,
          remaining: 0
        }
      ]
    }
  },
  computed: {
selectedDateJalali() {
  if (!this.selectedDate) return ''
  return dayjs
    .tz(this.selectedDate, 'Asia/Tehran')
    .calendar('jalali')
    .locale('fa')
    .format('YYYY/MM/DD')
}
,
    filteredWorkers() {
      let result = this.workers.filter(worker =>
        worker.name.includes(this.search)
      )
      if (this.sortKey) {
        result.sort((a, b) => {
          const valA = a[this.sortKey]
          const valB = b[this.sortKey]
          return this.sortAsc ? valA - valB : valB - valA
        })
      }
      return result
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('userRole')
      this.$router.push('/login')
    },
    sortBy(key) {
      if (this.sortKey === key) {
        this.sortAsc = !this.sortAsc
      } else {
        this.sortKey = key
        this.sortAsc = true
      }
    }
  }
}

</script>

<style scoped>
</style>
